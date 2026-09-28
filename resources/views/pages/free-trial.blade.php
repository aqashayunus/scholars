<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Registration | Olympian Scholars</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface min-h-screen flex items-center justify-center py-12">
    <div class="w-full max-w-xl px-4" x-data="freeTrialManager">

        {{-- Logo & Header (Identical to Login Page) --}}
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                <div class="w-12 h-12 bg-gradient-brand rounded-2xl flex items-center justify-center">
                    <span class="text-white font-heading font-bold text-base">OS</span>
                </div>
                <div class="text-left">
                    <span class="font-heading font-bold text-primary text-xl block leading-none">Olympian</span>
                    <span class="font-heading font-bold text-accent text-sm block">Scholars</span>
                </div>
            </a>
            <h1 class="font-heading font-bold text-2xl text-charcoal mt-6 mb-1">Create an Account</h1>
            <p class="text-muted text-sm">Register for Olympian Scholars classes</p>
        </div>

        {{-- Safe JSON Data Container --}}
        <script id="trial-init-data" type="application/json">
            {!! json_encode([
                'defaultRole' => old('register_as', 'student'),
                'defaultProgramme' => old('programme', $selectedProgramme ?? 'SPM'),
                'defaultSubjects' => old('subjects', $selectedSubject ? [$selectedSubject] : ['Additional Mathematics']),
                'defaultLevel' => old('level', 'Form 5'),
                'defaultPromo' => old('promo_code', $promoCode ?? ''),
                'studentName' => old('student_name', ''),
                'parentName' => old('parent_name', ''),
                'educatorName' => old('educator_name', ''),
                'schoolName' => old('school_name', ''),
                'email' => old('email', ''),
                'phone' => old('phone', ''),
            ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
        </script>

        {{-- Registration Card --}}
        <div class="card p-6 sm:p-8">

            {{-- Step Progress Indicator Bar --}}
            <div class="mb-6">
                <div class="flex items-center justify-between text-xs font-bold text-gray-500 mb-2">
                    <span :class="step >= 1 ? 'text-primary' : 'text-gray-400'">1. Basic Info</span>
                    <span :class="step >= 2 ? 'text-primary' : 'text-gray-400'">2. Subject & Level</span>
                    <span :class="step >= 3 ? 'text-primary' : 'text-gray-400'">3. Schedule & Promo</span>
                </div>
                <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
                    <div class="bg-primary h-full transition-all duration-300 rounded-full"
                         :style="'width: ' + (step === 1 ? '33.33%' : (step === 2 ? '66.66%' : '100%'))">
                    </div>
                </div>
                <div class="text-right mt-1.5">
                    <span class="text-[11px] font-semibold text-gray-400" x-text="`Step ${step} of 3`"></span>
                </div>
            </div>

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm">
                    <p class="font-bold mb-1">Please correct the following errors:</p>
                    <ul class="list-disc list-inside space-y-0.5 text-xs">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Step Validation Alert --}}
            <template x-if="stepError">
                <div class="mb-5 p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-xs font-normal flex items-center gap-2">
                    <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span x-text="stepError"></span>
                </div>
            </template>

            {{-- Registration Form --}}
            <form method="POST" action="{{ route('free-trial.submit') }}" id="free-trial-form" class="space-y-5">
                @csrf

                {{-- ══════════════════════════════════════════════════════
                     STEP 1: BASIC INFORMATION
                     ══════════════════════════════════════════════════════ --}}
                <div x-show="step === 1" class="space-y-5">

                    {{-- Role Selector (Identical to Login Page design) --}}
                    <div>
                        <p class="text-xs font-semibold text-muted uppercase tracking-wide mb-2.5">I am registering as:</p>
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" @click="registerAs = 'student'; errors.studentName = ''; errors.parentName = ''; errors.educatorName = ''"
                                    :class="registerAs === 'student' ? 'bg-primary text-white shadow-md' : 'bg-surface text-charcoal hover:bg-primary-50'"
                                    class="flex flex-col items-center gap-1.5 px-2 py-3 rounded-2xl text-xs font-semibold transition-all duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                </svg>
                                Student
                            </button>
                            <button type="button" @click="registerAs = 'parent'; errors.studentName = ''; errors.parentName = ''; errors.educatorName = ''"
                                    :class="registerAs === 'parent' ? 'bg-primary text-white shadow-md' : 'bg-surface text-charcoal hover:bg-primary-50'"
                                    class="flex flex-col items-center gap-1.5 px-2 py-3 rounded-2xl text-xs font-semibold transition-all duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Parent
                            </button>
                            <button type="button" @click="registerAs = 'educator'; errors.studentName = ''; errors.parentName = ''; errors.educatorName = ''"
                                    :class="registerAs === 'educator' ? 'bg-primary text-white shadow-md' : 'bg-surface text-charcoal hover:bg-primary-50'"
                                    class="flex flex-col items-center gap-1.5 px-2 py-3 rounded-2xl text-xs font-semibold transition-all duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                Educator
                            </button>
                        </div>
                        <input type="hidden" name="register_as" :value="registerAs">
                    </div>

                    {{-- Dynamic Inputs Based on Role --}}
                    {{-- If Student --}}
                    <div x-show="registerAs === 'student'" class="space-y-4">
                        <div>
                            <label class="form-label">Student Name (Name as per NRIC/Passport) *</label>
                            <input type="text" 
                                   name="student_name" 
                                   x-model="studentName" 
                                   @input="validateField('studentName')"
                                   @blur="validateField('studentName')"
                                   placeholder="Full name of student" 
                                   class="form-input"
                                   :class="errors.studentName ? 'border-red-400 focus:ring-red-400/20 focus:border-red-400' : ''">
                            <p x-show="errors.studentName" x-text="errors.studentName" class="text-red-500 text-xs mt-1 font-medium"></p>
                        </div>
                        <div>
                            <label class="form-label">School Name (Optional)</label>
                            <input type="text" name="school_name" x-model="schoolName" placeholder="e.g. SMK Taman Melawati" class="form-input">
                        </div>
                    </div>

                    {{-- If Parent --}}
                    <div x-show="registerAs === 'parent'" class="space-y-4">
                        <div>
                            <label class="form-label">Parent / Guardian Name *</label>
                            <input type="text" 
                                   name="parent_name" 
                                   x-model="parentName" 
                                   @input="validateField('parentName')"
                                   @blur="validateField('parentName')"
                                   placeholder="Full name of parent or guardian" 
                                   class="form-input"
                                   :class="errors.parentName ? 'border-red-400 focus:ring-red-400/20 focus:border-red-400' : ''">
                            <p x-show="errors.parentName" x-text="errors.parentName" class="text-red-500 text-xs mt-1 font-medium"></p>
                        </div>

                        <div>
                            <label class="form-label">Child's Full Name (Name as per MyKid/IC) *</label>
                            <input type="text" 
                                   name="student_name" 
                                   x-model="studentName" 
                                   @input="validateField('studentName')"
                                   @blur="validateField('studentName')"
                                   placeholder="Full name of child (e.g. Muhammad Adam)" 
                                   class="form-input"
                                   :class="errors.studentName ? 'border-red-400 focus:ring-red-400/20 focus:border-red-400' : ''">
                            <p x-show="errors.studentName" x-text="errors.studentName" class="text-red-500 text-xs mt-1 font-normal"></p>
                            
                            {{-- Inform parent that student login will be created --}}
                            <p x-show="!errors.studentName && !hasExistingCode && studentName.trim().length >= 2" class="text-gray-500 text-[11px] mt-1.5 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>A unique Student ID will be generated so your child can log in to the Student Portal.</span>
                            </p>
                        </div>

                        {{-- Link Code Option for Existing Student Account --}}
                        <div class="pt-1">
                            <label class="text-[11px] text-gray-600 font-medium flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" x-model="hasExistingCode" class="rounded border-gray-300 text-primary focus:ring-primary w-3.5 h-3.5">
                                <span>My child already has a Student Link Code (OS-XXXXXX)</span>
                            </label>

                            <div x-show="hasExistingCode" class="mt-2.5 p-3.5 bg-blue-50/60 border border-blue-200/80 rounded-2xl space-y-2">
                                <label class="text-xs font-bold text-blue-900 block">Enter Child's Student Link Code (OS-XXXXXX)</label>
                                <div class="relative">
                                    <input type="text" 
                                           name="child_link_code" 
                                           x-model="childLinkCode" 
                                           @input="checkLinkCode()" 
                                           placeholder="e.g. OS-ZTELDD" 
                                           class="form-input bg-white font-mono text-xs uppercase"
                                           maxlength="12">
                                    <div x-show="isCheckingCode" class="absolute right-3.5 top-3.5 text-gray-400">
                                        <svg class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                    </div>
                                </div>
                                <p x-show="linkCodeChecked && linkCodeFound" class="text-emerald-600 text-xs font-normal flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span x-text="'Found student: ' + matchedStudentName + ' (will be connected to your account).'"></span>
                                </p>
                                <p x-show="linkCodeChecked && !linkCodeFound && childLinkCode.trim().length >= 4" class="text-red-500 text-xs font-normal flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>No student found with this Link Code. Please verify with your child.</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- If Educator --}}
                    <div x-show="registerAs === 'educator'" class="space-y-4">
                        <div>
                            <label class="form-label">Educator / Teacher Name *</label>
                            <input type="text" 
                                   name="educator_name" 
                                   x-model="educatorName" 
                                   @input="validateField('educatorName')"
                                   @blur="validateField('educatorName')"
                                   placeholder="Full name" 
                                   class="form-input"
                                   :class="errors.educatorName ? 'border-red-400 focus:ring-red-400/20 focus:border-red-400' : ''">
                            <p x-show="errors.educatorName" x-text="errors.educatorName" class="text-red-500 text-xs mt-1 font-medium"></p>
                        </div>
                        <div>
                            <label class="form-label">School / Institution Name *</label>
                            <input type="text" name="school_name" x-model="schoolName" placeholder="Institution name" class="form-input">
                        </div>
                    </div>

                    {{-- Contact Details --}}
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Email Address *</label>
                            <input type="email" 
                                   name="email" 
                                   x-model="email" 
                                   @input="validateField('email')"
                                   @blur="validateField('email'); checkAvailability('email')"
                                   placeholder="email@example.com" 
                                   class="form-input"
                                   :class="errors.email ? 'border-red-400 focus:ring-red-400/20 focus:border-red-400' : ''">
                            <div x-show="errors.email" class="text-red-500 text-xs mt-1 font-medium">
                                <span x-text="errors.email"></span>
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Phone Number *</label>
                            <input type="tel" 
                                   name="phone" 
                                   x-model="phone" 
                                   @input="validateField('phone')"
                                   @blur="validateField('phone'); checkAvailability('phone')"
                                   placeholder="+60 12-345 6789" 
                                   class="form-input"
                                   :class="errors.phone ? 'border-red-400 focus:ring-red-400/20 focus:border-red-400' : ''">
                            <div x-show="errors.phone" class="text-red-500 text-xs mt-1 font-medium">
                                <span x-text="errors.phone"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Password Fields --}}
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Password *</label>
                            <div class="relative">
                                <input :type="showPassword ? 'text' : 'password'"
                                       name="password"
                                       x-model="password"
                                       @input="validateField('password'); validateField('confirmPassword')"
                                       placeholder="Min. 8 characters"
                                       autocomplete="new-password"
                                       class="form-input pr-10"
                                       :class="errors.password ? 'border-red-400 focus:ring-red-400/20 focus:border-red-400' : ''">
                                <button type="button" @click="showPassword = !showPassword"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg x-show="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                </button>
                            </div>
                            {{-- Strength Meter --}}
                            <div x-show="password.length > 0" class="mt-2 space-y-1.5">
                                <div class="flex gap-1">
                                    <div class="h-1.5 flex-1 rounded-full transition-all duration-300"
                                         :class="passwordStrength >= 1 ? (passwordStrength <= 1 ? 'bg-red-500' : (passwordStrength <= 3 ? 'bg-amber-400' : 'bg-emerald-500')) : 'bg-gray-200'"></div>
                                    <div class="h-1.5 flex-1 rounded-full transition-all duration-300"
                                         :class="passwordStrength >= 2 ? (passwordStrength <= 2 ? 'bg-amber-400' : (passwordStrength <= 3 ? 'bg-amber-400' : 'bg-emerald-500')) : 'bg-gray-200'"></div>
                                    <div class="h-1.5 flex-1 rounded-full transition-all duration-300"
                                         :class="passwordStrength >= 3 ? (passwordStrength <= 3 ? 'bg-amber-400' : 'bg-emerald-500') : 'bg-gray-200'"></div>
                                    <div class="h-1.5 flex-1 rounded-full transition-all duration-300"
                                         :class="passwordStrength >= 4 ? 'bg-emerald-500' : 'bg-gray-200'"></div>
                                </div>
                                <p class="text-[11px] font-semibold"
                                   :class="passwordStrength <= 1 ? 'text-red-500' : (passwordStrength <= 3 ? 'text-amber-600' : 'text-emerald-600')"
                                   x-text="passwordStrengthLabel"></p>
                                <ul class="text-[10px] text-gray-500 space-y-0.5 font-medium">
                                    <li :class="password.length >= 8 ? 'text-emerald-600' : 'text-gray-400'">✓ At least 8 characters</li>
                                    <li :class="/[A-Z]/.test(password) ? 'text-emerald-600' : 'text-gray-400'">✓ One uppercase letter</li>
                                    <li :class="/[0-9]/.test(password) ? 'text-emerald-600' : 'text-gray-400'">✓ One number</li>
                                    <li :class="/[^A-Za-z0-9]/.test(password) ? 'text-emerald-600' : 'text-gray-400'">✓ One special character (!@#$...)</li>
                                </ul>
                            </div>
                            <p x-show="errors.password" x-text="errors.password" class="text-red-500 text-xs mt-1 font-medium"></p>
                        </div>

                        <div>
                            <label class="form-label">Confirm Password *</label>
                            <div class="relative">
                                <input :type="showPassword ? 'text' : 'password'"
                                       name="password_confirmation"
                                       x-model="confirmPassword"
                                       @input="validateField('confirmPassword')"
                                       placeholder="Re-enter password"
                                       autocomplete="new-password"
                                       class="form-input pr-10"
                                       :class="errors.confirmPassword ? 'border-red-400 focus:ring-red-400/20 focus:border-red-400' : (confirmPassword && !errors.confirmPassword ? 'border-emerald-400' : '')">
                            </div>
                            <p x-show="errors.confirmPassword" x-text="errors.confirmPassword" class="text-red-500 text-xs mt-1 font-medium"></p>
                            <p x-show="confirmPassword.length > 0 && !errors.confirmPassword" class="text-emerald-600 text-xs mt-1 font-medium">✓ Passwords match</p>
                        </div>
                    </div>

                    {{-- Navigation Button for Step 1 --}}
                    <div class="pt-3">
                        <button type="button" @click="goToStep(2)" :disabled="isChecking" class="btn-primary w-full justify-center py-3.5">
                            <span x-text="isChecking ? 'Checking...' : (registerAs === 'parent' ? 'Continue to Child\'s Subjects' : (registerAs === 'educator' ? 'Continue to Teaching Subjects' : 'Continue to Subject Selection'))"></span>
                            <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════
                     STEP 2: SUBJECT & PROGRAMME (SIMPLE CHECKBOXES)
                     ══════════════════════════════════════════════════════ --}}
                <div x-show="step === 2" class="space-y-5">

                    {{-- Role-specific Context Notification Banner --}}
                    <div x-show="registerAs === 'parent' && childData.found && childData.subjects.length > 0" class="p-3.5 bg-blue-50 border border-blue-200/80 rounded-2xl text-xs text-blue-900 flex items-start gap-2.5 shadow-xs">
                        <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <span class="font-bold block text-blue-800">Child's existing subjects auto-loaded</span>
                            <span class="text-blue-700 text-[11px]"><strong x-text="childData.name"></strong> is currently registered for the pre-selected subjects below. You can select more subjects to enroll them in additional classes.</span>
                        </div>
                    </div>

                    <div x-show="registerAs === 'parent' && (!childData.found || childData.subjects.length === 0)" class="p-3.5 bg-gray-50 border border-gray-200/80 rounded-2xl text-xs text-gray-700 flex items-center gap-2.5">
                        <svg class="w-4 h-4 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Select the subjects your child needs tutoring or trial lessons in. Your child will be automatically enrolled in these courses.</span>
                    </div>

                    <div x-show="registerAs === 'educator'" class="p-3.5 bg-indigo-50/70 border border-indigo-200/80 rounded-2xl text-xs text-indigo-900 flex items-center gap-2.5">
                        <svg class="w-4 h-4 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                        <span>Select the subject(s) you teach or are interested in exploring on the platform.</span>
                    </div>
                    
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Programme *</label>
                            <select name="programme" x-model="programme" @change="onProgrammeChange()" class="form-input" required>
                                <option value="SPM">SPM (KSSM Form 4 & 5)</option>
                                <option value="IGCSE">IGCSE (Cambridge)</option>
                                <option value="Lower Secondary">Lower Secondary (Form 1 - 3)</option>
                                <option value="Primary">Primary (Standard 1 - 6)</option>
                                <option value="Kindergarten">Kindergarten / Preschool</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Academic Level *</label>
                            <select name="level" x-model="level" class="form-input">
                                <template x-for="lvl in availableLevels" :key="lvl">
                                    <option :value="lvl" x-text="lvl"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    {{-- Simple Checkbox Multi-Subject Picker --}}
                    <div class="space-y-2.5 pt-1">
                        <div class="flex items-center justify-between">
                            <label class="form-label mb-0" x-text="registerAs === 'parent' ? 'Choose Subject(s) for Your Child *' : (registerAs === 'educator' ? 'Choose Subject(s) You Teach / Specialize In *' : 'Choose Subject(s) You Want to Learn *')"></label>
                            <span class="text-xs font-bold text-primary" x-text="`${selectedSubjects.length} selected`"></span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-64 overflow-y-auto no-scrollbar p-1">
                            <template x-for="subj in currentSubjectsList" :key="subj.name">
                                <label class="flex items-center gap-2.5 p-3 rounded-2xl border cursor-pointer transition-all text-xs font-semibold"
                                       :class="selectedSubjects.includes(subj.name) ? 'border-primary bg-primary-50 text-primary' : 'border-gray-200 bg-white text-gray-800 hover:bg-gray-50'">
                                    <input type="checkbox" 
                                           name="subjects[]" 
                                           :value="subj.name" 
                                           x-model="selectedSubjects" 
                                           @change="validateField('subjects')"
                                           class="rounded border-gray-300 text-primary focus:ring-primary h-4 w-4 shrink-0">
                                    <span class="truncate" x-text="subj.name"></span>
                                </label>
                            </template>
                        </div>

                        <p x-show="errors.subjects" x-text="errors.subjects" class="text-red-500 text-xs font-medium"></p>

                        {{-- Selected list preview tags --}}
                        <div x-show="selectedSubjects.length > 0" class="pt-1 flex flex-wrap gap-1.5 items-center">
                            <span class="text-[11px] font-semibold text-gray-500">Selected:</span>
                            <template x-for="s in selectedSubjects" :key="s">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-primary-100 text-primary-800 text-[11px] font-bold">
                                    <span x-text="s"></span>
                                    <button type="button" @click="selectedSubjects = selectedSubjects.filter(item => item !== s); validateField('subjects')" class="text-primary-600 hover:text-primary-900 font-bold ml-1">×</button>
                                </span>
                            </template>
                        </div>
                    </div>

                    {{-- Navigation Buttons for Step 2 --}}
                    <div class="pt-3 grid grid-cols-2 gap-3">
                        <button type="button" @click="step = 1; stepError = ''" class="btn-secondary w-full justify-center py-3.5">
                            ← Back
                        </button>
                        <button type="button" @click="goToStep(3)" class="btn-primary w-full justify-center py-3.5">
                            Next Step →
                        </button>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════
                     STEP 3: SCHEDULE, PROMO CODE & SUBMIT
                     ══════════════════════════════════════════════════════ --}}
                <div x-show="step === 3" class="space-y-4">
                    
                    <div>
                        <label class="form-label">Preferred Session Timing</label>
                        <select name="schedule" class="form-input">
                            <option value="Weekday Evenings (8:00 PM - 9:30 PM)">Weekday Evenings (8:00 PM - 9:30 PM)</option>
                            <option value="Saturday Morning (10:00 AM - 11:30 AM)">Saturday Morning (10:00 AM - 11:30 AM)</option>
                            <option value="Saturday Afternoon (2:30 PM - 4:00 PM)">Saturday Afternoon (2:30 PM - 4:00 PM)</option>
                            <option value="Sunday Morning (10:00 AM - 11:30 AM)">Sunday Morning (10:00 AM - 11:30 AM)</option>
                            <option value="Next Available Session">Next Available Session</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Promo / Referral Code (Optional)</label>
                        <input type="text" name="promo_code" value="{{ old('promo_code', $promoCode ?? '') }}" placeholder="Enter promo code if any" class="form-input uppercase">
                    </div>

                    <div>
                        <label class="form-label">Additional Notes / Remarks (Optional)</label>
                        <textarea name="notes" rows="2" placeholder="Any specific questions or topics..." class="form-input resize-none">{{ old('notes') }}</textarea>
                    </div>

                    {{-- Navigation Buttons for Step 3 --}}
                    <div class="pt-3 grid grid-cols-2 gap-3">
                        <button type="button" @click="step = 2; stepError = ''" class="btn-secondary w-full justify-center py-3.5">
                            ← Back
                        </button>
                        <button type="submit" class="btn-primary w-full justify-center py-3.5">
                            Complete Registration
                        </button>
                    </div>
                </div>
            </form>

            {{-- Footer link to Login --}}
            <p class="text-center text-sm text-muted mt-5 pt-4 border-t border-gray-100">
                Already have an account?
                <a href="{{ route('login') }}" class="text-primary font-semibold hover:underline">Sign In</a>
            </p>
        </div>

        {{-- Bottom navigation --}}
        <div class="text-center text-xs text-muted mt-5">
            <a href="{{ route('home') }}" class="hover:text-primary transition-colors">← Back to Homepage</a>
        </div>

    </div>

    {{-- Component Definition Script --}}
    <script>
    (function() {
        function freeTrialComponent() {
            var rawEl = document.getElementById('trial-init-data');
            var cfg = {};
            if (rawEl) {
                try {
                    cfg = JSON.parse(rawEl.textContent || '{}');
                } catch (e) {
                    console.error('Error parsing registration init data', e);
                }
            }

            var initSubjects = ['Additional Mathematics'];
            if (Array.isArray(cfg.defaultSubjects) && cfg.defaultSubjects.length > 0) {
                initSubjects = cfg.defaultSubjects;
            } else if (typeof cfg.defaultSubjects === 'string' && cfg.defaultSubjects) {
                initSubjects = [cfg.defaultSubjects];
            }

            return {
                step: 1,
                stepError: '',
                isChecking: false,
                isSearchingChild: false,
                registerAs: cfg.defaultRole || 'student',
                studentName: cfg.studentName || '',
                parentName: cfg.parentName || '',
                educatorName: cfg.educatorName || '',
                schoolName: cfg.schoolName || '',
                email: cfg.email || '',
                phone: cfg.phone || '',
                password: '',
                confirmPassword: '',
                showPassword: false,
                programme: cfg.defaultProgramme || 'SPM',
                level: cfg.defaultLevel || 'Form 5',
                selectedSubjects: initSubjects,
                promoCode: cfg.defaultPromo || '',
                // Child status & subjects for parent registration
                hasExistingCode: false,
                childLinkCode: '',
                isCheckingCode: false,
                linkCodeChecked: false,
                linkCodeFound: false,
                matchedStudentName: '',
                childData: {
                    checked: false,
                    found: false,
                    name: '',
                    subjects: [],
                    level: ''
                },

                // Field errors map
                errors: {
                    studentName: '',
                    parentName: '',
                    educatorName: '',
                    email: '',
                    phone: '',
                    password: '',
                    confirmPassword: '',
                    subjects: ''
                },

                get passwordStrength() {
                    var p = this.password || '';
                    var score = 0;
                    if (p.length >= 8) score++;
                    if (/[A-Z]/.test(p)) score++;
                    if (/[0-9]/.test(p)) score++;
                    if (/[^A-Za-z0-9]/.test(p)) score++;
                    return score;
                },

                get passwordStrengthLabel() {
                    var s = this.passwordStrength;
                    if (s === 0) return '';
                    if (s === 1) return 'Weak';
                    if (s === 2) return 'Fair';
                    if (s === 3) return 'Good';
                    return 'Strong';
                },

                // Clean subjects list without descriptions
                databaseSubjects: {
                    'SPM': [
                        { name: 'Additional Mathematics' },
                        { name: 'Mathematics' },
                        { name: 'Physics' },
                        { name: 'Chemistry' },
                        { name: 'Biology' },
                        { name: 'Science' },
                        { name: 'Bahasa Melayu' },
                        { name: 'English' },
                        { name: 'Sejarah' },
                        { name: 'Prinsip Perakaunan' },
                        { name: 'Perniagaan' },
                        { name: 'Ekonomi' },
                        { name: 'Sains Komputer' },
                        { name: 'Pendidikan Islam & Moral' }
                    ],
                    'IGCSE': [
                        { name: 'IGCSE Mathematics' },
                        { name: 'IGCSE Additional Mathematics' },
                        { name: 'IGCSE Physics' },
                        { name: 'IGCSE Chemistry' },
                        { name: 'IGCSE Biology' },
                        { name: 'IGCSE English' },
                        { name: 'IGCSE Business Studies' },
                        { name: 'IGCSE Accounting' },
                        { name: 'IGCSE Economics' },
                        { name: 'IGCSE Computer Science' }
                    ],
                    'Lower Secondary': [
                        { name: 'Bahasa Melayu' },
                        { name: 'English' },
                        { name: 'Mathematics' },
                        { name: 'Science' },
                        { name: 'Sejarah' },
                        { name: 'Geografi' },
                        { name: 'Asas Sains Komputer (ASK)' },
                        { name: 'Reka Bentuk & Teknologi (RBT)' },
                        { name: 'Pendidikan Islam & Moral' }
                    ],
                    'Primary': [
                        { name: 'Bahasa Melayu' },
                        { name: 'English' },
                        { name: 'Mathematics' },
                        { name: 'Science' },
                        { name: 'Sejarah' },
                        { name: 'Pendidikan Islam & Moral' },
                        { name: 'Reka Bentuk & Teknologi (RBT)' }
                    ],
                    'Kindergarten': [
                        { name: 'Literasi Awal (Early Literacy & Phonics)' },
                        { name: 'Numerasi Awal (Early Numeracy & Counting)' },
                        { name: 'Bahasa Melayu Awal' },
                        { name: 'English for Preschool' },
                        { name: 'Penerokaan Sains Awal' }
                    ]
                },

                get currentSubjectsList() {
                    return this.databaseSubjects[this.programme] || this.databaseSubjects['SPM'];
                },

                get availableLevels() {
                    if (this.programme === 'SPM') {
                        return ['Form 5', 'Form 4'];
                    }
                    if (this.programme === 'IGCSE') {
                        return ['Year 11', 'Year 10', 'Year 9', 'Year 8', 'Year 7'];
                    }
                    if (this.programme === 'Lower Secondary') {
                        return ['Form 3', 'Form 2', 'Form 1'];
                    }
                    if (this.programme === 'Primary') {
                        return ['Standard 6', 'Standard 5', 'Standard 4', 'Standard 3', 'Standard 2', 'Standard 1'];
                    }
                    if (this.programme === 'Kindergarten') {
                        return ['Age 6 (Tadika)', 'Age 5 (Tadika)', 'Age 4 (Prasekolah)'];
                    }
                    return ['Form 5'];
                },

                onProgrammeChange() {
                    var levels = this.availableLevels;
                    if (!levels.includes(this.level)) {
                        this.level = levels[0];
                    }
                    var availableNames = this.currentSubjectsList.map(function(s) { return s.name; });
                    this.selectedSubjects = this.selectedSubjects.filter(function(name) {
                        return availableNames.indexOf(name) !== -1;
                    });
                    if (this.selectedSubjects.length === 0 && this.currentSubjectsList.length > 0) {
                        this.selectedSubjects = [this.currentSubjectsList[0].name];
                    }
                },

                checkLinkCode() {
                    var self = this;
                    var code = (self.childLinkCode || '').trim();
                    if (code.length < 4) {
                        self.linkCodeChecked = false;
                        self.linkCodeFound = false;
                        return;
                    }

                    var tokenEl = document.querySelector('meta[name="csrf-token"]');
                    var token = tokenEl ? tokenEl.getAttribute('content') : '';

                    self.isCheckingCode = true;
                    fetch('{{ route('registration.check-availability') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            register_as: 'parent',
                            child_link_code: code
                        })
                    })
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        self.isCheckingCode = false;
                        self.linkCodeChecked = true;
                        self.linkCodeFound = data.link_code_found;
                        if (data.link_code_found) {
                            self.matchedStudentName = data.link_student_name || '';
                            self.studentName = data.link_student_name || self.studentName;
                            if (data.link_level) self.level = data.link_level;
                            if (data.link_subjects && data.link_subjects.length > 0) {
                                data.link_subjects.forEach(function(sub) {
                                    if (self.selectedSubjects.indexOf(sub) === -1) {
                                        self.selectedSubjects.push(sub);
                                    }
                                });
                            }
                        }
                    })
                    .catch(function() {
                        self.isCheckingCode = false;
                    });
                },

                checkAvailability(type) {
                    var self = this;
                    var tokenEl = document.querySelector('meta[name="csrf-token"]');
                    var token = tokenEl ? tokenEl.getAttribute('content') : '';

                    var payload = {
                        register_as: self.registerAs
                    };

                    if (type === 'email' || !type) {
                        var em = (self.email || '').trim();
                        if (em && em.indexOf('@') !== -1) {
                            payload.email = em;
                        }
                    }
                    if (type === 'phone' || !type) {
                        var ph = (self.phone || '').trim();
                        if (ph && ph.replace(/[^0-9]/g, '').length >= 8) {
                            payload.phone = ph;
                        }
                    }

                    if (!payload.email && !payload.phone) {
                        return Promise.resolve({});
                    }

                    return fetch('{{ route('registration.check-availability') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        if (type === 'email' || !type) {
                            if (data.email_exists) {
                                self.errors.email = 'This email is already registered.';
                            } else if (self.errors.email && self.errors.email.indexOf('already registered') !== -1) {
                                self.errors.email = '';
                            }
                        }
                        if (type === 'phone' || !type) {
                            if (data.phone_exists) {
                                self.errors.phone = 'This phone number is already registered in our system.';
                            } else if (self.errors.phone && self.errors.phone.indexOf('already registered') !== -1) {
                                self.errors.phone = '';
                            }
                        }
                        return data;
                    })
                    .catch(function(err) {
                        console.error('Availability check error:', err);
                        return {};
                    });
                },

                validateField(fieldName) {
                    if (fieldName === 'studentName') {
                        if (this.registerAs === 'student' || this.registerAs === 'parent') {
                            if (!this.studentName || !this.studentName.trim()) {
                                this.errors.studentName = this.registerAs === 'parent' 
                                    ? 'Please enter the child / student full name.' 
                                    : 'Please enter the student full name.';
                            } else if (this.studentName.trim().length < 2) {
                                this.errors.studentName = 'Name must be at least 2 characters.';
                            } else {
                                this.errors.studentName = '';
                            }
                        } else {
                            this.errors.studentName = '';
                        }
                    }

                    if (fieldName === 'parentName') {
                        if (this.registerAs === 'parent') {
                            if (!this.parentName || !this.parentName.trim()) {
                                this.errors.parentName = 'Please enter the parent / guardian full name.';
                            } else if (this.parentName.trim().length < 2) {
                                this.errors.parentName = 'Parent name must be at least 2 characters.';
                            } else {
                                this.errors.parentName = '';
                            }
                        } else {
                            this.errors.parentName = '';
                        }
                    }

                    if (fieldName === 'educatorName') {
                        if (this.registerAs === 'educator') {
                            if (!this.educatorName || !this.educatorName.trim()) {
                                this.errors.educatorName = 'Please enter the educator / teacher full name.';
                            } else if (this.educatorName.trim().length < 2) {
                                this.errors.educatorName = 'Educator name must be at least 2 characters.';
                            } else {
                                this.errors.educatorName = '';
                            }
                        } else {
                            this.errors.educatorName = '';
                        }
                    }

                    if (fieldName === 'email') {
                        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!this.email || !this.email.trim()) {
                            this.errors.email = 'Please enter an email address.';
                        } else if (!emailRegex.test(this.email.trim())) {
                            this.errors.email = 'Please enter a valid email format (e.g. name@example.com).';
                        } else if (this.errors.email && this.errors.email.indexOf('already registered') === -1) {
                            this.errors.email = '';
                        }
                    }

                    if (fieldName === 'phone') {
                        var cleanDigits = (this.phone || '').replace(/[^0-9]/g, '');
                        var phoneRegex = /^[0-9\s\-\+\(\)]+$/;
                        if (!this.phone || !this.phone.trim()) {
                            this.errors.phone = 'Please enter a phone number.';
                        } else if (!phoneRegex.test(this.phone.trim()) || cleanDigits.length < 8) {
                            this.errors.phone = 'Please enter a valid phone number with at least 8 digits (e.g. +60 12-345 6789).';
                        } else if (this.errors.phone && this.errors.phone.indexOf('already registered') === -1) {
                            this.errors.phone = '';
                        }
                    }

                    if (fieldName === 'password') {
                        var p = this.password || '';
                        if (!p) {
                            this.errors.password = 'Please create a password.';
                        } else if (p.length < 8) {
                            this.errors.password = 'Password must be at least 8 characters.';
                        } else if (!/[A-Z]/.test(p)) {
                            this.errors.password = 'Password must contain at least one uppercase letter.';
                        } else if (!/[0-9]/.test(p)) {
                            this.errors.password = 'Password must contain at least one number.';
                        } else if (!/[^A-Za-z0-9]/.test(p)) {
                            this.errors.password = 'Password must contain at least one special character (!@#$%...).';
                        } else {
                            this.errors.password = '';
                        }
                    }

                    if (fieldName === 'confirmPassword') {
                        if (!this.confirmPassword) {
                            this.errors.confirmPassword = 'Please confirm your password.';
                        } else if (this.confirmPassword !== this.password) {
                            this.errors.confirmPassword = 'Passwords do not match.';
                        } else {
                            this.errors.confirmPassword = '';
                        }
                    }

                    if (fieldName === 'subjects') {
                        if (!this.selectedSubjects || this.selectedSubjects.length === 0) {
                            this.errors.subjects = 'Please select at least one subject.';
                        } else {
                            this.errors.subjects = '';
                        }
                    }
                },

                goToStep(targetStep) {
                    var self = this;
                    self.stepError = '';

                    // Validate Step 1
                    if (self.step === 1 && targetStep > 1) {
                        self.validateField('studentName');
                        self.validateField('parentName');
                        self.validateField('educatorName');
                        self.validateField('email');
                        self.validateField('phone');
                        self.validateField('password');
                        self.validateField('confirmPassword');

                        if (self.errors.studentName || self.errors.parentName || self.errors.educatorName || self.errors.email || self.errors.phone || self.errors.password || self.errors.confirmPassword) {
                            self.stepError = 'Please fill in all required fields.';
                            return;
                        }

                        // Check availability before proceeding
                        self.isChecking = true;
                        self.checkAvailability().then(function(data) {
                            self.isChecking = false;
                            if (data && (data.email_exists || data.phone_exists)) {
                                self.stepError = 'This email or phone number is already registered.';
                                return;
                            }
                            self.step = targetStep;
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        }).catch(function() {
                            self.isChecking = false;
                            self.step = targetStep;
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        });
                        return;
                    }

                    // Validate Step 2
                    if (self.step === 2 && targetStep > 2) {
                        self.validateField('subjects');
                        if (self.errors.subjects) {
                            self.stepError = self.errors.subjects;
                            return;
                        }
                    }

                    self.step = targetStep;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            };
        }

        if (window.Alpine) {
            window.Alpine.data('freeTrialManager', freeTrialComponent);
        } else {
            document.addEventListener('alpine:init', function() {
                window.Alpine.data('freeTrialManager', freeTrialComponent);
            });
        }
    })();
    </script>
</body>
</html>
