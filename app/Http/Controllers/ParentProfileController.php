<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Parent\ChildAccessService;
use App\Services\RenewalService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Parent portal > Profile & Settings: parent details, password, and Manage
 * Subscription for each linked child (2026-11-28; the parent portal had no
 * profile page before).
 */
class ParentProfileController extends Controller
{
    public const STATES = [
        'Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan', 'Pahang', 'Perak', 'Perlis', 'Pulau Pinang',
        'Sabah', 'Sarawak', 'Selangor', 'Terengganu', 'W.P. Kuala Lumpur', 'W.P. Labuan', 'W.P. Putrajaya',
    ];

    public function __construct(
        private ChildAccessService $children,
        private RenewalService $renewals,
    ) {}

    public function show(Request $request): View
    {
        $user = $this->user($request);
        $children = $this->children->getParentChildren($user);
        $child = $children->isEmpty() ? null : $this->children->getAuthorizedChild($children, $request->query('child'));
        $tab = $request->query('tab');
        if (! is_string($tab)) {
            $errors = $request->session()->get('errors');
            $bag = $errors instanceof ViewErrorBag ? $errors->getBag('default') : null;
            $passwordError = $bag && ($bag->has('current_password') || $bag->has('password'));
            $tab = (session('password_updated') || $passwordError) ? 'security' : 'profile';
        }

        return view('parent.profile', [
            'user' => $user,
            'children' => $children,
            'child' => $child,
            'subscription' => $child ? $this->renewals->panel($child) : null,
            'activeTab' => in_array($tab, ['profile', 'security', 'subscription'], true) ? $tab : 'profile',
            'states' => self::STATES,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $this->user($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'min:8', 'max:50', 'regex:/^[0-9\s\-\+\(\)]+$/'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'postcode' => ['nullable', 'string', 'max:10'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
        ], [
            'email.unique' => 'This email address is already used by another account.',
            'phone.regex' => 'The phone number contains invalid characters. Use digits, +, or -.',
        ]);

        $user->fill($data)->save();

        return redirect()->route('parent.profile', ['tab' => 'profile'])->with('profile_updated', 'Profile updated successfully!');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $this->user($request);
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ], [
            'current_password.current_password' => 'The current password you entered is incorrect.',
            'password.confirmed' => 'New passwords do not match.',
        ]);

        $user->password = Hash::make($data['password']);
        $user->save();

        return redirect()->route('parent.profile', ['tab' => 'security'])->with('password_updated', 'Password changed successfully!');
    }

    private function user(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        return $user;
    }
}
