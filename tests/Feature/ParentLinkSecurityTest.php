<?php

namespace Tests\Feature;

use App\Mail\ParentLinkRequestMail;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Regression coverage for the Phase 0 fix to ParentDashboardController::connectChild()
 * and ParentRegistrationController::store(). Previously, correctly guessing a
 * student's IC number or email immediately granted active guardianship — including
 * silently taking over a student who already had a parent on file, with zero
 * notice to anyone. See app/Services/ParentLinkService.php.
 */
class ParentLinkSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function makeStudent(string $ic, ?int $parentUserId = null): Student
    {
        $studentUser = User::factory()->create([
            'name' => 'Child '.$ic,
            'email' => 'child'.$ic.'@student.test',
            'role' => 'student',
            'password' => Hash::make('password'),
        ]);

        return Student::factory()->create([
            'user_id' => $studentUser->id,
            'parent_user_id' => $parentUserId,
            'ic_number_hash' => Student::hashIc($ic),
            'level' => 'SPM',
        ]);
    }

    private function makeParent(string $email): User
    {
        return User::factory()->create([
            'name' => 'Parent '.$email,
            'email' => $email,
            'role' => 'parent',
            'password' => Hash::make('password'),
        ]);
    }

    public function test_first_claim_on_an_unguarded_student_activates_immediately(): void
    {
        $parent = $this->makeParent('newparent@test.com');
        $student = $this->makeStudent('010203040506'); // no existing guardian

        $response = $this->actingAs($parent)->post(route('parent.children.connect'), [
            'identifier' => '010203-04-0506',
        ]);

        $response->assertRedirect(route('parent.dashboard', ['child_id' => $student->id]));

        $this->assertDatabaseHas('parent_student', [
            'parent_user_id' => $parent->id,
            'student_id' => $student->id,
            'status' => 'active',
        ]);
        $this->assertSame($parent->id, $student->fresh()->parent_user_id);
    }

    public function test_claiming_an_already_guardianed_student_does_not_grant_access(): void
    {
        Mail::fake();

        $existingGuardian = $this->makeParent('existing@test.com');
        $student = $this->makeStudent('020304050607', $existingGuardian->id);

        DB::table('parent_student')->insert([
            'parent_user_id' => $existingGuardian->id,
            'student_id' => $student->id,
            'relationship' => 'Father',
            'is_primary' => true,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $attacker = $this->makeParent('attacker@test.com');

        $response = $this->actingAs($attacker)->post(route('parent.children.connect'), [
            'identifier' => '020304-05-0607',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // The attacker's pivot row exists but is NOT active.
        $this->assertDatabaseHas('parent_student', [
            'parent_user_id' => $attacker->id,
            'student_id' => $student->id,
            'status' => 'pending',
        ]);

        // parent_user_id must remain the existing guardian's — not silently hijacked.
        $this->assertSame($existingGuardian->id, $student->fresh()->parent_user_id);

        // getAuthorizedChild() must reject the attacker for this student.
        $dashboard = $this->actingAs($attacker)
            ->get(route('parent.children.progress', $student->id));
        $dashboard->assertStatus(403);

        // The existing guardian was notified — not the attacker.
        Mail::assertSent(ParentLinkRequestMail::class, function ($mail) use ($existingGuardian, $attacker, $student) {
            return $mail->hasTo($existingGuardian->email)
                && $mail->requester->id === $attacker->id
                && $mail->student->id === $student->id;
        });
        Mail::assertNotSent(ParentLinkRequestMail::class, function ($mail) use ($attacker) {
            return $mail->hasTo($attacker->email);
        });
    }

    public function test_approving_a_pending_link_grants_access(): void
    {
        $existingGuardian = $this->makeParent('existing2@test.com');
        $student = $this->makeStudent('030405060708', $existingGuardian->id);

        $pivotId = DB::table('parent_student')->insertGetId([
            'parent_user_id' => $existingGuardian->id,
            'student_id' => $student->id,
            'relationship' => 'Father',
            'is_primary' => true,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $newParent = $this->makeParent('newguardian@test.com');
        $pendingPivotId = DB::table('parent_student')->insertGetId([
            'parent_user_id' => $newParent->id,
            'student_id' => $student->id,
            'relationship' => 'Mother',
            'is_primary' => false,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $approveUrl = URL::temporarySignedRoute(
            'parent-link-requests.approve',
            now()->addDays(7),
            ['pivot' => $pendingPivotId]
        );

        $response = $this->get($approveUrl);
        $response->assertOk();

        $this->assertDatabaseHas('parent_student', [
            'id' => $pendingPivotId,
            'status' => 'active',
        ]);

        // New parent now has real access.
        $dashboard = $this->actingAs($newParent)
            ->get(route('parent.children.progress', $student->id));
        $dashboard->assertStatus(200);
    }

    public function test_denying_a_pending_link_keeps_access_blocked(): void
    {
        $existingGuardian = $this->makeParent('existing3@test.com');
        $student = $this->makeStudent('040506070809', $existingGuardian->id);

        $newParent = $this->makeParent('deniedparent@test.com');
        $pendingPivotId = DB::table('parent_student')->insertGetId([
            'parent_user_id' => $newParent->id,
            'student_id' => $student->id,
            'relationship' => 'Mother',
            'is_primary' => false,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $denyUrl = URL::temporarySignedRoute(
            'parent-link-requests.deny',
            now()->addDays(7),
            ['pivot' => $pendingPivotId]
        );

        $response = $this->get($denyUrl);
        $response->assertOk();

        $this->assertDatabaseHas('parent_student', [
            'id' => $pendingPivotId,
            'status' => 'denied',
        ]);

        $dashboard = $this->actingAs($newParent)
            ->get(route('parent.children.progress', $student->id));
        $dashboard->assertStatus(403);
    }

    public function test_approve_link_requires_a_valid_signature(): void
    {
        $existingGuardian = $this->makeParent('existing4@test.com');
        $student = $this->makeStudent('050607080910', $existingGuardian->id);

        $newParent = $this->makeParent('unsigned@test.com');
        $pendingPivotId = DB::table('parent_student')->insertGetId([
            'parent_user_id' => $newParent->id,
            'student_id' => $student->id,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Hitting the route without a valid signature must not approve it.
        $response = $this->get('/parent-link-requests/'.$pendingPivotId.'/approve');
        $response->assertForbidden();

        $this->assertDatabaseHas('parent_student', [
            'id' => $pendingPivotId,
            'status' => 'pending',
        ]);
    }

    public function test_registration_time_linking_to_a_guardianed_student_does_not_grant_instant_access(): void
    {
        Mail::fake();

        $existingGuardian = $this->makeParent('existing5@test.com');
        $student = $this->makeStudent('060708091011', $existingGuardian->id);

        DB::table('parent_student')->insert([
            'parent_user_id' => $existingGuardian->id,
            'student_id' => $student->id,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post(route('register.parent.submit'), [
            'nationality_type' => 'local',
            'parent_name' => 'Attacker Parent',
            'email' => 'attackerreg@test.com',
            'phone' => '0123456789',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'relationship' => 'Guardian',
            'child_has_account' => 'yes',
            'parent_ic_number' => '999999-99-9999',
            'child_ic_number' => '060708-09-1011',
            'address_line_1' => 'Test Address',
            'postcode' => '12345',
            'city' => 'Test City',
            'state' => 'Selangor',
        ]);

        $response->assertRedirect(route('register.parent.success'));

        $attackerParent = User::where('email', 'attackerreg@test.com')->first();
        $this->assertNotNull($attackerParent);

        $this->assertDatabaseHas('parent_student', [
            'parent_user_id' => $attackerParent->id,
            'student_id' => $student->id,
            'status' => 'pending',
        ]);
        $this->assertSame($existingGuardian->id, $student->fresh()->parent_user_id);

        Mail::assertSent(ParentLinkRequestMail::class, function ($mail) use ($existingGuardian) {
            return $mail->hasTo($existingGuardian->email);
        });
    }
}
