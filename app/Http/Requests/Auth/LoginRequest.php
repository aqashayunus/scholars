<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $loginInput = trim($this->input('email'));
        $email = $loginInput;

        // 1. If input is 'admin', find the admin account
        if (strtolower($loginInput) === 'admin') {
            $adminUser = \App\Models\User::where('role', 'admin')
                ->orWhere('email', 'like', 'admin@%')
                ->first();
            if ($adminUser) {
                $email = $adminUser->email;
            }
        }
        // 2. If input looks like a phone number, find user by phone
        elseif (!filter_var($loginInput, FILTER_VALIDATE_EMAIL) && preg_match('/^[0-9\+\-\s\(\)]{8,20}$/', $loginInput)) {
            $user = \App\Models\User::where('phone', $loginInput)->first();
            if ($user) {
                $email = $user->email;
            }
        }
        // 3. If input is a username / name without email format
        elseif (!filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
            $user = \App\Models\User::where('name', $loginInput)
                ->orWhere('email', $loginInput)
                ->first();
            if ($user) {
                $email = $user->email;
            }
        }

        if (! Auth::attempt(['email' => $email, 'password' => $this->input('password')], $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // Check if the authenticated account is inactive or deactivated
        $user = Auth::user();
        if ($user && method_exists($user, 'isAccountInactive') && $user->isAccountInactive()) {
            Auth::logout();
            $this->session()->invalidate();
            $this->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Akaun anda telah dinyahaktifkan atau tidak aktif. Sila hubungi pihak pentadbir untuk maklumat lanjut.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
