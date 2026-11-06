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

        // NOTE: this generic endpoint is not linked from any UI — the real
        // registration flows are /register/student, /register/parent, and
        // /register/teacher (Auth/StudentRegistrationController,
        // ParentRegistrationController, TeacherApplicationController), which
        // validate their inputs properly and handle child-linking through
        // ParentLinkService. This endpoint previously also accepted unvalidated
        // child_name/child_email and defaulted the child's password to the
        // hardcoded literal 'password123' when none was supplied — removed
        // rather than fixed, since it duplicated (and diverged from) the real
        // parent registration flow's logic.
        $user = new User([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        $user->role = $request->role;
        $user->save();

        // Create matching Student profile if registering as student
        if ($user->role === 'student') {
            \App\Models\Student::firstOrCreate([
                'user_id' => $user->id
            ], [
                'level' => 'SPM',
                'total_points' => 0,
            ]);
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
