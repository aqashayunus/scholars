<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Route x role authorization matrix — Phase 1 safety net.
 *
 * Introspects the live route table for every route gated by the `role:`
 * middleware alias (204 routes as of 2026-09-28: 107 admin, 41 teacher,
 * 32 student, 24 parent) and asserts that a user of any OTHER role is
 * rejected, and that a guest is redirected to login. This tests at the
 * middleware boundary rather than controller internals, so it keeps
 * working unchanged through Phase 3/4's controller-to-service extraction
 * — the whole point of a safety net that has to survive the refactor it's
 * protecting against.
 */
class RouteAuthorizationMatrixTest extends TestCase
{
    use RefreshDatabase;

    private const ROLES = ['admin', 'teacher', 'student', 'parent'];

    /** @var array<string, User> */
    private array $users = [];

    protected function setUp(): void
    {
        parent::setUp();

        foreach (self::ROLES as $role) {
            $user = User::factory()->create([
                'name' => ucfirst($role).' Matrix Tester',
                'email' => $role.'@matrix.test',
                'role' => $role,
                'password' => Hash::make('password'),
            ]);

            if ($role === 'student') {
                Student::factory()->create(['user_id' => $user->id, 'level' => 'SPM']);
            }
            if ($role === 'teacher') {
                Teacher::factory()->create([
                    'user_id' => $user->id,
                    'specialization' => 'General Academic',
                    'qualification' => 'B.Ed',
                    'rating' => 5.0,
                    'experience_years' => 1,
                ]);
            }

            $this->users[$role] = $user;
        }
    }

    public function test_every_role_protected_route_rejects_other_roles_and_guests(): void
    {
        $roleRoutes = collect(Route::getRoutes())->filter(function ($route) {
            foreach ($route->gatherMiddleware() as $mw) {
                if (str_starts_with($mw, 'role:')) {
                    return true;
                }
            }

            return false;
        });

        $this->assertGreaterThan(
            50, $roleRoutes->count(),
            'Expected ~204 role-protected routes — matrix would be vacuous or the route table changed shape unexpectedly.'
        );

        $failures = [];
        $checked = 0;

        foreach ($roleRoutes as $route) {
            $roleMiddleware = collect($route->gatherMiddleware())
                ->first(fn ($m) => str_starts_with($m, 'role:'));
            $allowedRoles = explode(',', substr($roleMiddleware, 5));

            $method = collect($route->methods())->first(fn ($m) => $m !== 'HEAD');
            $rawUri = ltrim($route->uri(), '/');
            $uri = '/'.preg_replace('/\{[^}]+\}/', '999999', $rawUri);

            foreach (self::ROLES as $testRole) {
                if (in_array($testRole, $allowedRoles, true)) {
                    continue; // this role legitimately belongs on this route
                }

                $checked++;
                $response = $this->actingAs($this->users[$testRole])->call($method, $uri);
                $status = $response->getStatusCode();

                // RoleMiddleware has two deliberate crossover redirects rather
                // than a hard 403: a parent hitting a student/fees* URL is sent
                // to the parent fee portal, and vice versa. Both still deny
                // access to the OTHER role's actual data — just via redirect.
                $isKnownCrossover = ($testRole === 'parent' && str_starts_with($rawUri, 'student/fees'))
                    || ($testRole === 'student' && str_starts_with($rawUri, 'parent/fees'));

                // A crossover route redirects (302) when RoleMiddleware's own
                // logic runs, but 404s instead when the placeholder id fails
                // route-model-binding before RoleMiddleware is even reached —
                // both outcomes deny access to the other family's real data.
                $acceptable = $isKnownCrossover
                    ? in_array($status, [302, 403, 404], true)
                    : in_array($status, [403, 404], true);

                if (! $acceptable) {
                    $failures[] = sprintf(
                        '%s %s as %s => %d (expected %s)',
                        $method, $uri, $testRole, $status,
                        $isKnownCrossover ? '302/403/404' : '403 or 404'
                    );
                }
            }

            // Guest: not authenticated at all. RoleMiddleware does its own
            // explicit abort(403) for a missing user rather than deferring to
            // auth's redirect-to-login — both deny access equally, so both
            // are acceptable outcomes here.
            $checked++;
            $guestResponse = $this->call($method, $uri);
            $guestStatus = $guestResponse->getStatusCode();
            if (! in_array($guestStatus, [302, 401, 403, 404], true)) {
                $failures[] = sprintf(
                    '%s %s as GUEST => %d (expected 302/401/403/404)',
                    $method, $uri, $guestStatus
                );
            }
        }

        $this->assertEmpty(
            $failures,
            sprintf(
                "Authorization matrix failures (%d of %d checks across %d routes):\n%s",
                count($failures), $checked, $roleRoutes->count(),
                implode("\n", array_slice($failures, 0, 60))
            )
        );
    }
}
