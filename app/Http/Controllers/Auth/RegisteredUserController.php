<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\WelcomeMail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:student,parent'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        // Create matching Student profile if registering as student
        if ($user->role === 'student') {
            \App\Models\Student::firstOrCreate([
                'user_id' => $user->id
            ], [
                'level' => 'SPM',
                'total_points' => 0,
            ]);
        }

        // Create and link Child account if registering as parent and child info is provided
        if ($user->role === 'parent' && $request->filled('child_name') && $request->filled('child_email')) {
            $childUser = User::create([
                'name'     => $request->child_name,
                'email'    => $request->child_email,
                'password' => Hash::make($request->child_password ?: 'password123'),
                'role'     => 'student',
            ]);

            $childStudent = \App\Models\Student::create([
                'user_id'        => $childUser->id,
                'parent_user_id' => $user->id,
                'level'          => $request->child_level ?: 'SPM',
                'total_points'   => 100,
                'streak_days'    => 1,
            ]);

            $courses = \App\Models\Course::where('status', 'published')->take(3)->get();
            if ($courses->isEmpty()) {
                $courses = \App\Models\Course::take(3)->get();
            }
            foreach ($courses as $course) {
                \App\Models\Enrollment::firstOrCreate([
                    'student_id' => $childStudent->id,
                    'course_id'  => $course->id,
                ], [
                    'status'            => 'active',
                    'enrolled_at'       => now(),
                    'progress_pct'      => 0,
                    'completed_lessons' => 0,
                ]);
            }
        }

        // Send Welcome Email (logged to storage/logs/laravel.log in dev)
        try {
            Mail::to($user->email)->send(new WelcomeMail($user));
        } catch (\Exception $e) {
            // Log warning or handle gracefully so user registration doesn't fail on mail configurations
            report($e);
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route($user->getDashboardRoute(), absolute: false));
    }
}
