<x-dashboard-layout>
    <x-slot name="title">Profile & Settings</x-slot>
    <x-slot name="sidebar">
        @include('student.partials.sidebar')
    </x-slot>

    @php
        $student = $student ?? auth()->user()->student;
        $user = $user ?? auth()->user();
        $isParentManaged = $student && ($student->parent_user_id || ($student->parents && $student->parents->isNotEmpty()));
        $parentUser = $parent ?? ($student?->parent ?? $student?->parents?->first());
        $isSystemEmail = $user && (str_contains($user->email, '@olympianscholars.my') || str_contains($user->email, '@student.olympia.edu'));
        $avatarUrl = $user->avatar ? asset('storage/' . $user->avatar) : null;
        $activeTab = $activeTab ?? request('tab', ($errors->has('current_password') || $errors->has('password') || session('password_updated')) ? 'security' : (session('settings_updated') ? 'settings' : 'profile'));
    @endphp

    <div x-data="{ 
        tab: '{{ $activeTab }}',
        avatarPreview: '{{ $avatarUrl }}',
        removeAvatar: false,
        previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                this.removeAvatar = false;
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.avatarPreview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },
        clearAvatar() {
            this.avatarPreview = null;
            this.removeAvatar = true;
            this.$refs.avatarInput.value = '';
        }
    }" class="max-w-4xl mx-auto space-y-6">

        {{-- ── PAGE HEADER ── --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Profile & Settings</h1>
                <p class="text-sm text-gray-500 mt-1">Manage your photo, personal information, school details, and preferences</p>
            </div>

        </div>

        {{-- ── FLASH ALERTS ── --}}
        @if(session('profile_updated'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-sm font-medium flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('profile_updated') }}</span>
            </div>
        @endif

        @if(session('settings_updated'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-sm font-medium flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('settings_updated') }}</span>
            </div>
        @endif

        @if(session('password_updated'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-sm font-medium flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('password_updated') }}</span>
            </div>
        @endif

        {{-- ── SIMPLE TABS ── --}}
        <div class="flex gap-2 border-b border-gray-200 pb-3">
            <button @click="tab = 'profile'" 
                :class="tab === 'profile' ? 'bg-indigo-600 text-white font-semibold shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200'"
                class="px-5 py-2.5 rounded-xl text-sm transition-all">
                Student Details
            </button>
            <button @click="tab = 'settings'" 
                :class="tab === 'settings' ? 'bg-indigo-600 text-white font-semibold shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200'"
                class="px-5 py-2.5 rounded-xl text-sm transition-all">
                Preferences
            </button>
            <button @click="tab = 'security'" 
                :class="tab === 'security' ? 'bg-indigo-600 text-white font-semibold shadow-sm' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200'"
                class="px-5 py-2.5 rounded-xl text-sm transition-all">
                Password
            </button>
        </div>

        {{-- ── TAB 1: STUDENT DETAILS & PROFILE ── --}}
        <div x-show="tab === 'profile'" class="space-y-6">
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-6">
                
                @if($errors->has('name') || $errors->has('email') || $errors->has('phone') || $errors->has('school_name') || $errors->has('avatar'))
                    <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($isSystemEmail)
                    <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800">
                        <span class="font-bold">Note:</span> You are currently using a system-generated email address. You can update it below with your active personal email.
                    </div>
                @endif

                <form method="POST" action="{{ route('student.profile.update') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PATCH')

                    <input type="hidden" name="remove_avatar" :value="removeAvatar ? '1' : '0'">

                    {{-- 1. Profile Picture Section --}}
                    <div class="pb-6 border-b border-gray-100">
                        <h3 class="text-sm font-bold text-gray-900 mb-3">Profile Picture</h3>
                        
                        <div class="flex items-center gap-5">
                            {{-- Avatar Display / Preview --}}
                            <div class="relative shrink-0">
                                <template x-if="avatarPreview">
                                    <img :src="avatarPreview" alt="Profile Picture" class="w-20 h-20 rounded-2xl object-cover border-2 border-indigo-100 shadow-sm">
                                </template>
                                <template x-if="!avatarPreview">
                                    <div class="w-20 h-20 rounded-2xl bg-indigo-600 text-white font-bold text-2xl flex items-center justify-center shadow-sm">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                </template>
                            </div>

                            {{-- Avatar Actions --}}
                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <input type="file" name="avatar" accept="image/jpeg,image/png,image/jpg,image/webp" 
                                        x-ref="avatarInput" @change="previewImage($event)" class="hidden" id="student_avatar_input">
                                    
                                    <label for="student_avatar_input" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-semibold rounded-xl cursor-pointer transition-colors inline-flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span>Change Photo</span>
                                    </label>

                                    <template x-if="avatarPreview">
                                        <button type="button" @click="clearAvatar()" class="px-3 py-2 text-xs font-medium text-red-600 hover:bg-red-50 rounded-xl transition-colors">
                                            Remove
                                        </button>
                                    </template>
                                </div>
                                <p class="text-xs text-gray-400">Allowed formats: JPG, PNG, WebP (Max 2MB)</p>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Personal Information Fields --}}
                    <div class="space-y-4">
                        <h3 class="text-sm font-bold text-gray-900">Personal Information</h3>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address <span class="text-red-500">*</span></label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="e.g. 0123456789"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">School / Institution Name</label>
                            <input type="text" name="school_name" value="{{ old('school_name', $student->school_name ?? '') }}" placeholder="e.g. SMK Taman Melawati, SK Convent"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>

                    {{-- 3. Academic Details (Read-Only / Program cannot be changed by student) --}}
                    <div class="pt-4 border-t border-gray-100 space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-bold text-gray-900">Academic Program & Enrolment Details</h3>
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-gray-500 bg-gray-100 px-2.5 py-1 rounded-lg">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                Locked
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {{-- Locked Education Program / Level --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Education Level / Program
                                </label>
                                <div class="relative">
                                    <input type="text" value="{{ $student->level ?? 'Not specified' }}" readonly disabled
                                        class="w-full px-4 py-2.5 rounded-xl bg-gray-100 border border-gray-200 text-gray-600 text-sm font-medium cursor-not-allowed select-none">
                                    <div class="absolute right-3.5 top-3 text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    </div>
                                </div>
                                <p class="text-[11px] text-gray-400 mt-1">To change your education level, contact your parent or portal administrator.</p>
                            </div>

                            {{-- School Name --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">School Name</label>
                                <input type="text" value="{{ $student->school_name ?? 'Not specified' }}" readonly disabled
                                    class="w-full px-4 py-2.5 rounded-xl bg-gray-100 border border-gray-200 text-gray-700 text-sm cursor-not-allowed">
                            </div>
                        </div>

                        {{-- Enrolled Courses List --}}
                        @if(isset($enrolledSubjects) && count($enrolledSubjects) > 0)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Enrolled Subjects</label>
                                <div class="flex flex-wrap gap-2 p-3 bg-gray-50 rounded-xl border border-gray-100">
                                    @foreach($enrolledSubjects as $subj)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-white border border-gray-200 text-xs font-semibold text-gray-800">
                                            {{ $subj }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Parent / Guardian Account Info --}}
                        <div class="pt-4 border-t border-gray-100 space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-base font-bold text-gray-900">Parent / Guardian Information</h2>
                                    <p class="text-xs text-gray-500">Details of the parent or guardian linked to your student account</p>
                                </div>
                                @if($parent)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        Linked
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-700 text-xs font-bold">
                                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                        Not Linked
                                    </span>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- Parent Name --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Parent / Guardian Name</label>
                                    <input type="text" value="{{ $parent->name ?? 'No parent linked' }}" readonly disabled
                                        class="w-full px-4 py-2.5 rounded-xl bg-gray-100 border border-gray-200 text-gray-800 text-sm font-medium cursor-not-allowed">
                                </div>

                                {{-- Relationship --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Relationship</label>
                                    @php
                                        $pivotRel = $student->parents()->where('parent_user_id', $parent?->id)->first()?->pivot?->relationship ?? ($parent ? 'Guardian' : '—');
                                    @endphp
                                    <input type="text" value="{{ ucfirst($pivotRel) }}" readonly disabled
                                        class="w-full px-4 py-2.5 rounded-xl bg-gray-100 border border-gray-200 text-gray-800 text-sm font-medium cursor-not-allowed">
                                </div>

                                {{-- Parent Email --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Parent Email</label>
                                    <input type="text" value="{{ $parent->email ?? '—' }}" readonly disabled
                                        class="w-full px-4 py-2.5 rounded-xl bg-gray-100 border border-gray-200 text-gray-800 text-sm cursor-not-allowed">
                                </div>

                                {{-- Parent Phone --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Parent Contact Number</label>
                                    <input type="text" value="{{ $parent->phone ?? '—' }}" readonly disabled
                                        class="w-full px-4 py-2.5 rounded-xl bg-gray-100 border border-gray-200 text-gray-800 text-sm cursor-not-allowed">
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
                            Save Changes
                        </button>
                        <span class="text-xs text-gray-400">Registered: {{ $user->created_at ? $user->created_at->format('d M Y') : '—' }}</span>
                    </div>
                </form>
            </div>

            {{-- ── LINK PARENT ACCOUNT (standalone — cannot be nested inside another form) ── --}}
            @if(!$parent)
                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-sm font-bold text-gray-900">Link Parent Account</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Enter your parent's MyKad / IC number to link their account to yours.</p>
                        </div>
                    </div>

                    @if($errors->has('parent_ic'))
                        <p class="text-xs text-red-600 mb-3">{{ $errors->first('parent_ic') }}</p>
                    @endif

                    <form method="POST" action="{{ route('student.profile.link-parent') }}" class="flex flex-col sm:flex-row gap-2">
                        @csrf
                        <input type="text" name="parent_ic" value="{{ old('parent_ic') }}" placeholder="Parent's IC number (e.g. 901231-14-5678)"
                            class="flex-1 px-3 py-2 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <select name="relationship"
                            class="pl-3.5 pr-8 py-2 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 min-w-[130px]">
                            <option value="Guardian">Guardian</option>
                            <option value="Father">Father</option>
                            <option value="Mother">Mother</option>
                            <option value="Other">Other</option>
                        </select>
                        <button type="submit"
                            class="px-4 py-2 bg-gray-900 hover:bg-black text-white text-xs font-medium rounded-xl transition-colors whitespace-nowrap">
                            Link Account
                        </button>
                    </form>
                </div>
            @endif
        </div>

        {{-- ── TAB 2: PREFERENCES ── --}}
        <div x-show="tab === 'settings'" x-cloak class="space-y-6">
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-6">
                <form method="POST" action="{{ route('student.settings.preferences') }}" class="space-y-6">
                    @csrf
                    @method('PATCH')

                    {{-- Notifications --}}
                    <div>
                        <h2 class="text-base font-bold text-gray-900 mb-1">Notifications</h2>
                        <p class="text-xs text-gray-500 mb-4">Choose which study notifications you want to receive</p>

                        <div class="space-y-3">
                            <label class="flex items-center justify-between p-3.5 bg-gray-50 rounded-xl cursor-pointer">
                                <div>
                                    <p class="text-sm font-medium text-gray-800">Live Class Reminders</p>
                                    <p class="text-xs text-gray-500">Notify 15 minutes before scheduled class starts</p>
                                </div>
                                <input type="checkbox" checked class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                            </label>

                            <label class="flex items-center justify-between p-3.5 bg-gray-50 rounded-xl cursor-pointer">
                                <div>
                                    <p class="text-sm font-medium text-gray-800">Assignment Deadlines</p>
                                    <p class="text-xs text-gray-500">Remind me 24 hours before homework is due</p>
                                </div>
                                <input type="checkbox" checked class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                            </label>

                            <label class="flex items-center justify-between p-3.5 bg-gray-50 rounded-xl cursor-pointer">
                                <div>
                                    <p class="text-sm font-medium text-gray-800">Teacher Messages</p>
                                    <p class="text-xs text-gray-500">Get notified when a teacher sends guidance or feedback</p>
                                </div>
                                <input type="checkbox" checked class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                            </label>
                        </div>
                    </div>

                    {{-- Sound & Effects --}}
                    <div class="pt-4 border-t border-gray-100">
                        <h2 class="text-base font-bold text-gray-900 mb-1">Learning Sound & Effects</h2>
                        <p class="text-xs text-gray-500 mb-4">Customize audio effects during quizzes and exercises</p>

                        <div class="space-y-3">
                            <label class="flex items-center justify-between p-3.5 bg-gray-50 rounded-xl cursor-pointer">
                                <div>
                                    <p class="text-sm font-medium text-gray-800">Quiz Sound Effects</p>
                                    <p class="text-xs text-gray-500">Play audio chime on correct answer</p>
                                </div>
                                <input type="checkbox" checked class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                            </label>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
                            Save Preferences
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── TAB 3: PASSWORD / SECURITY ── --}}
        <div x-show="tab === 'security'" x-cloak class="space-y-6">
            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-6">
                <div>
                    <h2 class="text-base font-bold text-gray-900 mb-1">Change Password</h2>
                    <p class="text-xs text-gray-500">Update your login password to keep your account secure</p>
                </div>

                @if($errors->has('current_password') || $errors->has('password'))
                    <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @if($errors->has('current_password'))
                                <li>{{ $errors->first('current_password') }}</li>
                            @endif
                            @if($errors->has('password'))
                                <li>{{ $errors->first('password') }}</li>
                            @endif
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('student.profile.password') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    @if(!$isParentManaged)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                            <div class="relative" x-data="{ show: false }">
                                <input :type="show ? 'text' : 'password'" name="current_password" required placeholder="Enter current password"
                                    class="w-full px-4 py-2.5 pr-11 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <button type="button" @click="show = !show"
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none transition-colors"
                                    :title="show ? 'Hide password' : 'Show password'"
                                    aria-label="Toggle current password visibility">
                                    <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg x-show="show" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800">
                            Your account was created by your parent. You can set a new password below directly.
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <div class="relative" x-data="{ show: false }">
                                <input :type="show ? 'text' : 'password'" name="password" required placeholder="Min. 8 characters"
                                    class="w-full px-4 py-2.5 pr-11 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <button type="button" @click="show = !show"
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none transition-colors"
                                    :title="show ? 'Hide password' : 'Show password'"
                                    aria-label="Toggle new password visibility">
                                    <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg x-show="show" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                            <div class="relative" x-data="{ show: false }">
                                <input :type="show ? 'text' : 'password'" name="password_confirmation" required placeholder="Re-enter new password"
                                    class="w-full px-4 py-2.5 pr-11 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <button type="button" @click="show = !show"
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none transition-colors"
                                    :title="show ? 'Hide password' : 'Show password'"
                                    aria-label="Toggle confirm new password visibility">
                                    <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg x-show="show" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500">Password must be at least 8 characters long and include uppercase, lowercase, and numbers.</p>

                    <div class="pt-3">
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-dashboard-layout>