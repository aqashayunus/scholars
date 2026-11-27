<x-dashboard-layout>
    <x-slot name="title">Profile & Settings</x-slot>
    <x-slot name="sidebar">
        @include('parent.partials.sidebar')
    </x-slot>

    @php
        $input = 'w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 outline-none';
        $tabBtn = "px-5 py-2.5 rounded-xl text-sm transition-all";
        $on = "bg-indigo-600 text-white font-semibold shadow-sm";
        $off = "bg-white text-gray-600 hover:bg-gray-50 border border-gray-200";
    @endphp

    <div x-data="{ tab: @js($activeTab) }" class="max-w-4xl mx-auto space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Profile & Settings</h1>
            <p class="text-sm text-gray-500 mt-1">Manage your details, password and your children's subscriptions</p>
        </div>

        @foreach(['profile_updated', 'password_updated'] as $flash)
            @if(session($flash))
                <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-sm font-medium flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session($flash) }}</span>
                </div>
            @endif
        @endforeach

        <div class="flex flex-wrap gap-2 border-b border-gray-200 pb-3">
            <button type="button" @click="tab = 'profile'" :class="tab === 'profile' ? @js($on) : @js($off)" class="{{ $tabBtn }}">Parent Details</button>
            <button type="button" @click="tab = 'security'" :class="tab === 'security' ? @js($on) : @js($off)" class="{{ $tabBtn }}">Password</button>
            <button type="button" @click="tab = 'subscription'" :class="tab === 'subscription' ? @js($on) : @js($off)" class="{{ $tabBtn }}">Subscription</button>
        </div>

        {{-- ── PARENT DETAILS ── --}}
        <div x-show="tab === 'profile'" class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-6">
            @if($errors->hasAny(['name', 'email', 'phone', 'address_line_1', 'address_line_2', 'postcode', 'city', 'state']))
                <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach(['name', 'email', 'phone', 'address_line_1', 'address_line_2', 'postcode', 'city', 'state'] as $f)
                            @foreach($errors->get($f) as $err)<li>{{ $err }}</li>@endforeach
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('parent.profile.update') }}" class="space-y-5">
                @csrf
                @method('PATCH')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label for="p_name" class="block text-sm font-semibold text-gray-800 mb-1.5">Full name <span class="text-red-500">*</span></label>
                        <input id="p_name" name="name" required value="{{ old('name', $user->name) }}" class="{{ $input }}">
                    </div>
                    <div>
                        <label for="p_email" class="block text-sm font-semibold text-gray-800 mb-1.5">Email <span class="text-red-500">*</span></label>
                        <input id="p_email" type="email" name="email" required value="{{ old('email', $user->email) }}" class="{{ $input }}">
                    </div>
                    <div>
                        <label for="p_phone" class="block text-sm font-semibold text-gray-800 mb-1.5">Phone</label>
                        <input id="p_phone" name="phone" value="{{ old('phone', $user->phone) }}" class="{{ $input }}">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="p_addr1" class="block text-sm font-semibold text-gray-800 mb-1.5">Address</label>
                        <input id="p_addr1" name="address_line_1" value="{{ old('address_line_1', $user->address_line_1) }}" placeholder="Address line 1" class="{{ $input }}">
                        <input name="address_line_2" aria-label="Address line 2" value="{{ old('address_line_2', $user->address_line_2) }}" placeholder="Address line 2 (optional)" class="{{ $input }} mt-2">
                    </div>
                    <div>
                        <label for="p_postcode" class="block text-sm font-semibold text-gray-800 mb-1.5">Postcode</label>
                        <input id="p_postcode" name="postcode" value="{{ old('postcode', $user->postcode) }}" class="{{ $input }}">
                    </div>
                    <div>
                        <label for="p_city" class="block text-sm font-semibold text-gray-800 mb-1.5">City</label>
                        <input id="p_city" name="city" value="{{ old('city', $user->city) }}" class="{{ $input }}">
                    </div>
                    <div>
                        <label for="p_state" class="block text-sm font-semibold text-gray-800 mb-1.5">State</label>
                        <select id="p_state" name="state" class="{{ $input }}">
                            <option value="">Choose…</option>
                            @php $currentState = old('state', $user->state); @endphp
                            @foreach($states as $st)
                                <option value="{{ $st }}" @selected($currentState === $st)>{{ $st }}</option>
                            @endforeach
                            @if($currentState && ! in_array($currentState, $states, true))
                                <option value="{{ $currentState }}" selected>{{ $currentState }}</option>
                            @endif
                        </select>
                    </div>
                </div>

                @if($children->isNotEmpty())
                    <div class="rounded-xl bg-gray-50 px-4 py-3 text-sm">
                        <p class="font-semibold text-gray-800 mb-1">Linked children</p>
                        <ul class="text-gray-600 space-y-0.5">
                            @foreach($children as $c)<li>{{ $c->user->name ?? 'Student #'.$c->id }} · {{ $c->level ?: '—' }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <div class="flex justify-end">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold">Save details</button>
                </div>
            </form>
        </div>

        {{-- ── PASSWORD ── --}}
        <div x-show="tab === 'security'" x-cloak class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-4">
            <div>
                <h3 class="text-base font-bold text-gray-900">Change password</h3>
                <p class="text-sm text-gray-500">At least 8 characters, with upper- and lower-case letters and a number.</p>
            </div>
            <form method="POST" action="{{ route('parent.profile.password') }}" class="space-y-4 max-w-md">
                @csrf
                @method('PATCH')
                <div>
                    <label for="p_current" class="block text-sm font-semibold text-gray-800 mb-1.5">Current password</label>
                    <input id="p_current" type="password" name="current_password" required autocomplete="current-password" class="{{ $input }}">
                    @error('current_password')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="p_new" class="block text-sm font-semibold text-gray-800 mb-1.5">New password</label>
                    <input id="p_new" type="password" name="password" required autocomplete="new-password" class="{{ $input }}">
                    @error('password')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="p_confirm" class="block text-sm font-semibold text-gray-800 mb-1.5">Confirm new password</label>
                    <input id="p_confirm" type="password" name="password_confirmation" required autocomplete="new-password" class="{{ $input }}">
                </div>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold">Change password</button>
            </form>
        </div>

        {{-- ── MANAGE SUBSCRIPTION ── --}}
        <div x-show="tab === 'subscription'" x-cloak class="space-y-4">
            @if($children->isEmpty())
                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm text-sm text-gray-500">
                    No child is linked to your account yet. Add or connect your child from the Dashboard.
                </div>
            @else
                @if($children->count() > 1)
                    <div class="flex flex-wrap gap-2" role="tablist" aria-label="Choose a child">
                        @foreach($children as $c)
                            <a href="{{ route('parent.profile', ['tab' => 'subscription', 'child' => $c->id]) }}"
                               class="px-4 py-2 rounded-xl text-sm font-semibold border {{ $child && $child->id === $c->id ? 'bg-indigo-50 border-indigo-300 text-indigo-700' : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                                {{ $c->user->name ?? 'Student #'.$c->id }}
                            </a>
                        @endforeach
                    </div>
                @endif
                @if($subscription)
                    @include('subscription.panel', $subscription)
                @endif
            @endif
        </div>
    </div>
</x-dashboard-layout>
