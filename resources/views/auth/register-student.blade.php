<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Registration | Olympian Scholars</title>
    <meta name="description" content="Create your student account with Olympian Scholars. Register using your MyKad IC number.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $initialStep = 1;
    if ($errors->hasAny(['password', 'password_confirmation'])) {
        $initialStep = 4;
    } elseif ($errors->hasAny(['subjects', 'subjects.*'])) {
        $initialStep = 3;
    } elseif ($errors->hasAny(['programme', 'level', 'school_name'])) {
        $initialStep = 2;
    } elseif ($errors->any()) {
        $initialStep = 1;
    }
@endphp
<body class="bg-surface min-h-screen py-10 px-4" x-data="studentRegData()">

<script>
function studentRegData() {
    return {
        step: {{ $initialStep }},
        totalSteps: 4,
        selectedEducationType: '{{ old('programme', $selectedProgramme ?? 'secondary') }}',
        selectedLevel: '{{ old('level', '') }}',
        selectedSubjects: {!! json_encode(old('subjects', [])) !!},
        allSubjects: {!! json_encode($subjects->map(function($s) {
            return [
                'id'             => $s->id,
                'name'           => $s->name,
                'display_name'   => $s->display_name,
                'education_type' => $s->education_type,
                'stage'          => $s->stage,
                'form_year'      => $s->form_year,
                'category'       => $s->category ?? 'Core',
                'monthly_fee'    => (float) ($s->monthly_fee ?? 0),
            ];
        })) !!},
        allFeeStructures: {!! json_encode($feeStructures->map(function($f) {
            return [
                'id'              => $f->id,
                'name'            => $f->name,
                'description'     => $f->description,
                'amount'          => (float) $f->amount,
                'education_level' => $f->education_level,
                'frequency'       => $f->frequency,
            ];
        })) !!},
        bundleDiscountPct: {{ $bundleElectiveDiscountPct }},
        invoiceDueDays: {{ $invoiceDueDays }},
        selectedFeeStructureId: {{ old('fee_structure_id', 'null') }},
        selectedPlanType: '{{ old('fee_structure_id') ? 'package' : 'package' }}',
        icRaw: '{{ old('ic_number', '') }}',
        icValid: false,
        icGender: '',
        icDob: '',
        malaysianStates: {!! json_encode($malaysianStates) !!},

        password: '',
        passwordConfirmation: '',
        passwordError: '',

        init() {
            if (this.icRaw) {
                this.parseIC(this.icRaw);
            }
            const initialLevel = '{{ old('level', '') }}';
            this.$nextTick(() => {
                if (initialLevel && this.levelsForType().includes(initialLevel)) {
                    this.selectedLevel = initialLevel;
                }
                const available = this.availableFeeStructures();
                if (available.length > 0 && !this.selectedFeeStructureId && this.selectedPlanType === 'package') {
                    this.selectFeeStructure(available[0]);
                }
            });

            this.$watch('selectedEducationType', () => {
                if (!this.levelsForType().includes(this.selectedLevel)) {
                    this.selectedLevel = '';
                }
                this.pruneSelectedSubjects();
                this.syncFeeStructureForLevel();
            });
            this.$watch('selectedLevel', () => {
                this.pruneSelectedSubjects();
                this.syncFeeStructureForLevel();
            });
        },

        syncFeeStructureForLevel() {
            const available = this.availableFeeStructures();
            if (this.selectedPlanType === 'package' && available.length > 0) {
                const stillValid = available.some(f => f.id == this.selectedFeeStructureId);
                if (!stillValid) {
                    this.selectFeeStructure(available[0]);
                }
            }
        },

        availableFeeStructures() {
            if (!this.selectedLevel && !this.selectedEducationType) {
                return this.allFeeStructures;
            }
            const lvl = (this.selectedLevel || '').toLowerCase();
            const edu = (this.selectedEducationType || '').toLowerCase();

            return this.allFeeStructures.filter(fs => {
                const target = (fs.education_level || '').toLowerCase();
                if (target === 'all') return true;

                if (lvl && target.includes(lvl)) return true;
                if (edu === 'secondary' && (target.includes('form') || target.includes('secondary') || target.includes('spm') || target.includes('pt3'))) {
                    if (lvl.includes('form 1') || lvl.includes('form 2') || lvl.includes('form 3')) {
                        return target.includes('1–3') || target.includes('1-3') || target.includes('pt3') || target.includes('lower');
                    }
                    if (lvl.includes('form 4') || lvl.includes('form 5')) {
                        return target.includes('4–5') || target.includes('4-5') || target.includes('spm') || target.includes('upper');
                    }
                    return true;
                }
                if (edu === 'primary' && (target.includes('year') || target.includes('primary') || target.includes('upsr'))) {
                    return true;
                }
                return false;
            });
        },

        getCurrentFeeStructure() {
            if (!this.selectedFeeStructureId) return null;
            return this.allFeeStructures.find(f => f.id == this.selectedFeeStructureId) || null;
        },

        selectFeeStructure(fs) {
            if (!fs) {
                this.selectedFeeStructureId = null;
                this.selectedPlanType = 'custom';
                return;
            }
            this.selectedFeeStructureId = fs.id;
            this.selectedPlanType = 'package';

            // Auto-select Core subjects for this level dynamically
            const coreList = this.coreSubjects();
            if (coreList.length > 0) {
                coreList.forEach(s => {
                    if (!this.selectedSubjects.includes(s.name)) {
                        this.selectedSubjects.push(s.name);
                    }
                });
            }
        },

        coreSubjects() {
            return this.filteredSubjects().filter(s => (s.category || '').toLowerCase() === 'core');
        },

        electiveSubjects() {
            return this.filteredSubjects().filter(s => (s.category || '').toLowerCase() !== 'core');
        },

        calculatedPricing() {
            const fs = this.getCurrentFeeStructure();
            const selectedList = this.filteredSubjects().filter(s => this.selectedSubjects.includes(s.name));

            if (!fs || this.selectedPlanType === 'custom') {
                const regularTotal = selectedList.reduce((sum, s) => sum + (s.monthly_fee || 0), 0);
                return {
                    isPackage: false,
                    packageName: 'Custom Subject Enrollment',
                    basePrice: regularTotal,
                    coreCount: selectedList.length,
                    electiveCount: 0,
                    regularTotal: regularTotal,
                    savings: 0,
                    finalTotal: regularTotal
                };
            }

            const coreSelected = selectedList.filter(s => (s.category || '').toLowerCase() === 'core');
            const electiveSelected = selectedList.filter(s => (s.category || '').toLowerCase() !== 'core');

            const coreRegular = coreSelected.reduce((sum, s) => sum + (s.monthly_fee || 0), 0);
            const electiveRegular = electiveSelected.reduce((sum, s) => sum + (s.monthly_fee || 0), 0);
            const discountFactor = 1 - (this.bundleDiscountPct / 100);
            const electiveDiscounted = electiveSelected.reduce((sum, s) => sum + Math.round((s.monthly_fee || 0) * discountFactor * 100) / 100, 0);

            const effectivePackageBase = fs.amount;
            const finalTotal = effectivePackageBase + electiveDiscounted;
            const subtotal = coreRegular + electiveRegular;
            const savings = Math.max(0, subtotal - finalTotal);

            return {
                isPackage: true,
                packageName: fs.name,
                basePrice: effectivePackageBase,
                coreCount: coreSelected.length,
                electiveCount: electiveSelected.length,
                regularTotal: subtotal,
                savings: savings,
                finalTotal: finalTotal
            };
        },

        formatIC(raw) {
            let digits = (raw || '').replace(/\D/g, '').substring(0, 12);
            if (digits.length <= 6) return digits;
            if (digits.length <= 8) return digits.substring(0,6) + '-' + digits.substring(6);
            return digits.substring(0,6) + '-' + digits.substring(6,8) + '-' + digits.substring(8);
        },

        parseIC(raw) {
            let formatted = this.formatIC(raw);
            this.icRaw = formatted;
            let digits = formatted.replace(/\D/g, '');
            if (digits.length === 12) {
                this.icValid = true;
                let yy = digits.substring(0, 2);
                let mm = digits.substring(2, 4);
                let dd = digits.substring(4, 6);
                let currentYY = new Date().getFullYear() % 100;
                let year = parseInt(yy, 10) > currentYY ? '19' + yy : '20' + yy;
                this.icDob = year + '-' + mm + '-' + dd;
                let lastDigit = parseInt(digits.charAt(11), 10);
                this.icGender = lastDigit % 2 === 1 ? 'Male' : 'Female';
            } else {
                this.icValid = false;
                this.icDob = '';
                this.icGender = '';
            }
        },

        onICInput(event) {
            let formatted = this.formatIC(event.target.value);
            event.target.value = formatted;
            this.parseIC(formatted);
        },

        levelsForType() {
            if (this.selectedEducationType === 'primary') {
                return ['Year 4', 'Year 5', 'Year 6'];
            }
            return ['Form 1', 'Form 2', 'Form 3', 'Form 4', 'Form 5'];
        },

        filteredSubjects() {
            let list = this.allSubjects;
            if (this.selectedEducationType) {
                list = list.filter(s => !s.education_type || s.education_type === this.selectedEducationType);
            }

            if (this.selectedLevel) {
                list = list.filter(s => s.form_year === this.selectedLevel);
            }

            // Deduplicate by name so each subject appears only once
            const seen = new Set();
            return list.filter(s => {
                if (seen.has(s.name)) {
                    return false;
                }
                seen.add(s.name);
                return true;
            });
        },

        pruneSelectedSubjects() {
            const availableNames = new Set(this.filteredSubjects().map(s => s.name));
            this.selectedSubjects = this.selectedSubjects.filter(name => availableNames.has(name));
        },

        toggleSubject(name) {
            if (this.selectedSubjects.includes(name)) {
                this.selectedSubjects = this.selectedSubjects.filter(s => s !== name);
            } else {
                this.selectedSubjects.push(name);
            }
        },

        validatePassword() {
            this.passwordError = '';
            const pass = this.password || document.getElementById('password')?.value || '';
            const passConf = this.passwordConfirmation || document.getElementById('password_confirmation')?.value || '';

            if (!pass) {
                this.passwordError = 'Please create a password.';
                return false;
            }
            if (pass.length < 8) {
                this.passwordError = 'Password must be at least 8 characters long.';
                return false;
            }
            const hasUpper = /[A-Z]/.test(pass);
            const hasNumber = /[0-9]/.test(pass);
            const hasSpecial = /[^A-Za-z0-9]/.test(pass);
            if (!hasUpper || !hasNumber || !hasSpecial) {
                this.passwordError = 'Password must contain at least one uppercase letter, one number, and one special character.';
                return false;
            }
            if (pass !== passConf) {
                this.passwordError = 'Passwords do not match. Please re-enter.';
                return false;
            }
            return true;
        },

        submitForm(event) {
            if (!this.validatePassword()) {
                event.preventDefault();
                const pwd = document.getElementById('password');
                if (pwd) pwd.focus();
                return false;
            }
        },

        nextStep() {
            if (this.step === 1) {
                let digits = this.icRaw.replace(/\D/g, '');
                if (digits.length !== 12) {
                    alert('Please enter a valid 12-digit IC number in the format XXXXXX-XX-XXXX.');
                    return;
                }
            }
            if (this.step < this.totalSteps) this.step++;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        prevStep() {
            if (this.step > 1) this.step--;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    };
}
</script>

    <div class="w-full max-w-xl mx-auto">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-flex flex-col items-center gap-2 group">
                <img src="{{ asset('images/logo.png') }}" alt="Olympian Scholars" class="h-16 w-auto object-contain transition-transform group-hover:scale-105">
            </a>
        </div>

        {{-- Progress Bar --}}
        <div class="mb-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-muted">Step <span x-text="step"></span> of 4</span>
                <span class="text-xs text-muted"
                      x-text="['Identity & Contact', 'Education', 'Subjects', 'Account & Confirm'][step-1]"></span>
            </div>
            <div class="h-2 bg-primary-50 rounded-full">
                <div class="h-2 bg-gradient-brand rounded-full transition-all duration-500"
                     :style="'width:' + (step / 4 * 100) + '%'"></div>
            </div>
            <div class="flex justify-between mt-2">
                <template x-for="i in 4" :key="i">
                    <div class="flex flex-col items-center gap-1">
                        <div class="w-6 h-6 rounded-full text-xs flex items-center justify-center font-bold transition-all duration-300"
                             :class="i <= step ? 'bg-primary text-white' : 'bg-primary-50 text-muted'">
                            <span x-text="i < step ? '✓' : i"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Errors --}}
        @if ($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-100 text-red-600 rounded-xl text-sm">
            <p class="font-semibold mb-1">Please fix the following:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('register.student.submit') }}" @submit="submitForm($event)" class="space-y-5">
            @csrf

            {{-- STEP 1: Identity & Contact --}}
            <div x-show="step === 1" class="card space-y-4">
                <div class="mb-2">
                    <h2 class="font-heading font-bold text-xl text-charcoal">Identity & Contact</h2>
                    <p class="text-sm text-muted mt-1">Your MyKad IC is used to identify your account.</p>
                </div>

                {{-- Full Name --}}
                <div>
                    <label for="name" class="block text-sm font-semibold text-charcoal mb-1.5">
                        Full Name (as per IC) <span class="text-red-500">*</span>
                    </label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required autocomplete="name"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm transition-all"
                           placeholder="e.g. Muhammad Adam bin Zakaria">
                </div>

                {{-- IC Number --}}
                <div>
                    <label for="ic_number" class="block text-sm font-semibold text-charcoal mb-1.5">
                        MyKad / IC Number <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input id="ic_number" name="ic_number" type="text"
                               x-model="icRaw"
                               @input="onICInput($event)"
                               value="{{ old('ic_number') }}"
                               required
                               maxlength="14"
                               placeholder="e.g. 123456-78-9012"
                               class="w-full px-4 py-2.5 pr-10 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm transition-all"
                               :class="icRaw.length > 0 ? (icValid ? 'border-emerald-400 bg-emerald-50/30' : 'border-red-300') : ''">
                        <div class="absolute right-3 top-1/2 -translate-y-1/2" x-show="icRaw.length > 0">
                            <svg x-show="icValid" class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <svg x-show="!icValid" class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Date of Birth --}}
                    <div>
                        <label for="date_of_birth" class="block text-sm font-semibold text-charcoal mb-1.5">
                            Date of Birth
                        </label>
                        <input id="date_of_birth" name="date_of_birth" type="text"
                               x-model="icDob"
                               value="{{ old('date_of_birth') }}"
                               placeholder="YYYY-MM-DD"
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/60 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm transition-all"
                               readonly>
                    </div>

                    {{-- Gender --}}
                    <div>
                        <label for="gender" class="block text-sm font-semibold text-charcoal mb-1.5">
                            Gender
                        </label>
                        <input id="gender" name="gender" type="text"
                               x-model="icGender"
                               value="{{ old('gender') }}"
                               placeholder="Male / Female"
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50/60 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm transition-all"
                               readonly>
                    </div>
                </div>

                {{-- Nationality --}}
                <div>
                    <label for="nationality" class="block text-sm font-semibold text-charcoal mb-1.5">Nationality <span class="text-red-500">*</span></label>
                    <select id="nationality" name="nationality" required
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm transition-all">
                        <option value="">-- Select Nationality --</option>
                        <option value="Malaysian" {{ old('nationality') === 'Malaysian' ? 'selected' : '' }}>Malaysian</option>
                        <option value="International" {{ old('nationality') === 'International' ? 'selected' : '' }}>International Student</option>
                        <option value="Permanent Resident" {{ old('nationality') === 'Permanent Resident' ? 'selected' : '' }}>Permanent Resident (PR)</option>
                    </select>
                </div>

                {{-- State --}}
                <div>
                    <label for="state" class="block text-sm font-semibold text-charcoal mb-1.5">State <span class="text-red-500">*</span></label>
                    <select id="state" name="state" required
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm transition-all">
                        <option value="">-- Select State --</option>
                        @foreach($malaysianStates as $state)
                            <option value="{{ $state }}" {{ old('state') === $state ? 'selected' : '' }}>{{ $state }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Divider --}}
                <div class="border-t border-gray-100 pt-2">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Contact Information</p>
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-semibold text-charcoal mb-1.5">Email Address <span class="text-red-500">*</span></label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm transition-all"
                           placeholder="your@email.com">
                </div>

                {{-- Phone --}}
                <div>
                    <label for="phone" class="block text-sm font-semibold text-charcoal mb-1.5">Mobile Number <span class="text-red-500">*</span></label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required autocomplete="tel"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm transition-all"
                           placeholder="+60 12-345 6789">
                </div>

                <button type="button" @click="nextStep()"
                        class="w-full py-3 px-6 rounded-xl bg-primary hover:bg-primary-700 text-white font-semibold text-sm shadow-sm transition-all flex items-center justify-center gap-2 cursor-pointer mt-2">
                    Continue → Education
                </button>
            </div>

            {{-- STEP 2: Education --}}
            <div x-show="step === 2" class="card space-y-4">
                <div class="mb-2">
                    <h2 class="font-heading font-bold text-xl text-charcoal">Education</h2>
                    <p class="text-sm text-muted mt-1">Select the programme that matches your studies.</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-charcoal mb-2">Education Level <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="programme" value="primary" x-model="selectedEducationType" class="sr-only peer">
                            <div class="p-3 text-center rounded-xl border-2 border-gray-200 peer-checked:border-primary peer-checked:bg-primary-50 transition-all">
                                <p class="text-xs font-bold text-charcoal">Primary School</p>
                                <p class="text-[10px] text-muted mt-0.5">Year 4 – 6</p>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="programme" value="secondary" x-model="selectedEducationType" class="sr-only peer">
                            <div class="p-3 text-center rounded-xl border-2 border-gray-200 peer-checked:border-primary peer-checked:bg-primary-50 transition-all">
                                <p class="text-xs font-bold text-charcoal">Secondary School</p>
                                <p class="text-[10px] text-muted mt-0.5">Form 1 – 5</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label for="level" class="block text-sm font-semibold text-charcoal mb-1.5">Form / Year</label>
                    <select id="level" name="level" x-model="selectedLevel"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm transition-all">
                        <option value="">-- Select Form / Year --</option>
                        <template x-for="lvl in levelsForType()" :key="lvl">
                            <option :value="lvl" x-text="lvl"></option>
                        </template>
                        @if(old('level'))
                            <option value="{{ old('level') }}" x-show="false" selected>{{ old('level') }}</option>
                        @endif
                    </select>
                </div>

                <div>
                    <label for="school_name" class="block text-sm font-semibold text-charcoal mb-1.5">School Name</label>
                    <input id="school_name" name="school_name" type="text" value="{{ old('school_name') }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm transition-all"
                           placeholder="e.g. SMK Taman Melawati">
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" @click="prevStep()" class="flex-1 py-3 px-4 rounded-xl border border-gray-200 text-sm font-semibold text-muted hover:bg-gray-50 transition-colors flex items-center justify-center">← Back</button>
                    <button type="button" @click="nextStep()" class="flex-1 py-3 px-4 rounded-xl bg-primary hover:bg-primary-700 text-white text-sm font-semibold shadow-sm transition-colors flex items-center justify-center">Continue →</button>
                </div>
            </div>

            {{-- STEP 3: Tuition Package & Subjects --}}
            <div x-show="step === 3" class="card space-y-5">
                <input type="hidden" name="fee_structure_id" :value="selectedFeeStructureId">

                <div class="border-b border-gray-100 pb-3">
                    <h2 class="font-heading font-bold text-xl text-charcoal">Tuition Package &amp; Subjects</h2>
                    <p class="text-sm text-muted mt-0.5">Select a bundled tuition plan for maximum savings, or choose individual subjects.</p>
                </div>

                {{-- 1. Package vs Custom Selector --}}
                <div class="space-y-2.5">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Choose Enrollment Plan</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <template x-for="fs in availableFeeStructures()" :key="fs.id">
                            <div @click="selectFeeStructure(fs)"
                                 :class="selectedFeeStructureId == fs.id ? 'border-primary ring-2 ring-primary/20 bg-primary-50/40 shadow-xs' : 'border-gray-200 hover:border-gray-300 bg-white'"
                                 class="p-4 rounded-2xl border cursor-pointer transition-all flex flex-col justify-between space-y-2">
                                <div>
                                    <div class="flex items-center justify-between gap-1 mb-1">
                                        <span class="px-2 py-0.5 rounded-md bg-blue-100 text-primary text-[10px] font-bold uppercase tracking-wider">Bundle Plan</span>
                                        <span class="text-[10px] font-semibold text-gray-400" x-text="fs.education_level"></span>
                                    </div>
                                    <h4 class="font-heading font-bold text-sm text-charcoal leading-tight" x-text="fs.name"></h4>
                                    <p class="text-[11px] text-muted mt-1 leading-snug line-clamp-2" x-text="fs.description"></p>
                                </div>
                                <div class="pt-2 border-t border-gray-100 flex items-center justify-between">
                                    <span class="font-black text-primary text-base" x-text="'RM ' + Number(fs.amount).toFixed(2)"></span>
                                    <span class="text-[10px] font-bold uppercase text-gray-400" x-text="'/' + fs.frequency"></span>
                                </div>
                            </div>
                        </template>

                        {{-- Custom / Per-subject option --}}
                        <div @click="selectFeeStructure(null)"
                             :class="selectedPlanType === 'custom' ? 'border-primary ring-2 ring-primary/20 bg-primary-50/40 shadow-xs' : 'border-gray-200 hover:border-gray-300 bg-white'"
                             class="p-4 rounded-2xl border cursor-pointer transition-all flex flex-col justify-between space-y-2">
                            <div>
                                <div class="flex items-center justify-between gap-1 mb-1">
                                    <span class="px-2 py-0.5 rounded-md bg-gray-100 text-gray-600 text-[10px] font-bold uppercase tracking-wider">Custom</span>
                                    <span class="text-[10px] font-semibold text-gray-400">Flexible</span>
                                </div>
                                <h4 class="font-heading font-bold text-sm text-charcoal leading-tight">Pay Per Subject</h4>
                                <p class="text-[11px] text-muted mt-1 leading-snug">Pick only the individual subjects you need at standard tuition fees.</p>
                            </div>
                            <div class="pt-2 border-t border-gray-100 flex items-center justify-between">
                                <span class="font-bold text-gray-700 text-xs">Standard Rates</span>
                                <span class="text-[10px] font-semibold text-gray-400">Unbundled</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. Core Subjects (Included in Package) --}}
                <div class="space-y-2 pt-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Core Curriculum Subjects
                            <span class="text-primary font-normal text-[11px]" x-show="selectedPlanType === 'package'">(Included in Bundle)</span>
                        </label>
                        <span class="text-[11px] text-muted" x-text="coreSubjects().filter(s => selectedSubjects.includes(s.name)).length + '/' + coreSubjects().length + ' selected'"></span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 max-h-56 overflow-y-auto pr-1">
                        <template x-for="subject in coreSubjects()" :key="subject.name">
                            <label class="relative cursor-pointer">
                                <input type="checkbox" name="subjects[]" :value="subject.name"
                                       :checked="selectedSubjects.includes(subject.name)"
                                       @change="toggleSubject(subject.name)"
                                       class="sr-only peer">
                                <div class="p-2.5 rounded-xl border-2 transition-all flex items-center justify-between gap-2"
                                     :class="selectedSubjects.includes(subject.name)
                                         ? 'border-primary text-primary bg-primary-50 font-semibold shadow-xs'
                                         : 'border-gray-200 text-muted hover:border-gray-300'">
                                    <span class="text-xs truncate font-medium" x-text="subject.name"></span>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded font-bold shrink-0"
                                          :class="selectedPlanType === 'package' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600'"
                                          x-text="selectedPlanType === 'package' ? 'Included' : ('RM ' + Number(subject.monthly_fee).toFixed(0))">
                                    </span>
                                </div>
                            </label>
                        </template>
                    </div>
                </div>

                {{-- 3. Electives & Add-on Subjects --}}
                <template x-if="electiveSubjects().length > 0">
                    <div class="space-y-2 pt-2 border-t border-gray-100">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Electives &amp; Add-on Subjects
                            </label>
                            <span x-show="selectedPlanType === 'package' && bundleDiscountPct > 0"
                                  class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">
                                <span x-text="bundleDiscountPct + '% Package Add-on Discount Active'"></span>
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 max-h-56 overflow-y-auto pr-1">
                            <template x-for="subject in electiveSubjects()" :key="subject.name">
                                <label class="relative cursor-pointer">
                                    <input type="checkbox" name="subjects[]" :value="subject.name"
                                           :checked="selectedSubjects.includes(subject.name)"
                                           @change="toggleSubject(subject.name)"
                                           class="sr-only peer">
                                    <div class="p-2.5 rounded-xl border-2 transition-all flex items-center justify-between gap-2"
                                         :class="selectedSubjects.includes(subject.name)
                                             ? 'border-primary text-primary bg-primary-50 font-semibold shadow-xs'
                                             : 'border-gray-200 text-muted hover:border-gray-300'">
                                        <span class="text-xs truncate font-medium" x-text="subject.name"></span>
                                        <span class="text-[10px] px-1.5 py-0.5 rounded font-bold shrink-0 bg-gray-100 text-gray-700"
                                              x-text="'RM ' + Number(selectedPlanType === 'package' ? subject.monthly_fee * (1 - bundleDiscountPct/100) : subject.monthly_fee).toFixed(0)">
                                        </span>
                                    </div>
                                </label>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- 4. Live Dynamic Tuition Summary Box --}}
                <div class="bg-gray-50 border border-gray-200/80 rounded-2xl p-4 space-y-2.5">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-500">Selected Plan:</span>
                        <span class="font-bold text-gray-900" x-text="calculatedPricing().packageName"></span>
                    </div>

                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-500">Subjects Enrolled:</span>
                        <span class="font-semibold text-gray-800" x-text="selectedSubjects.length + ' Subject(s)'"></span>
                    </div>

                    <template x-if="calculatedPricing().savings > 0">
                        <div class="flex items-center justify-between text-xs text-emerald-700 font-semibold bg-emerald-50/80 px-2.5 py-1.5 rounded-xl border border-emerald-200/60">
                            <span>Bundle Plan Savings:</span>
                            <span x-text="'- RM ' + Number(calculatedPricing().savings).toFixed(2)"></span>
                        </div>
                    </template>

                    <div class="pt-2 border-t border-gray-200 flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-gray-900 block">Total Monthly Tuition</span>
                            <span class="text-[10px] text-gray-400">Formal invoice issued upon registration</span>
                        </div>
                        <div class="text-right">
                            <span class="text-lg font-black text-primary font-heading" x-text="'RM ' + Number(calculatedPricing().finalTotal).toFixed(2)"></span>
                            <span class="text-[10px] text-gray-400 block">/ month</span>
                        </div>
                    </div>

                    <div class="p-2.5 bg-blue-50/70 border border-blue-100 rounded-xl text-[11px] text-blue-900 flex items-start gap-2">
                        <svg class="w-4 h-4 text-primary shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>
                            <strong>No payment required upfront today.</strong> An official invoice with a <span x-text="invoiceDueDays"></span>-day payment window will be generated for your convenience.
                        </span>
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" @click="prevStep()" class="flex-1 py-3 px-4 rounded-xl border border-gray-200 text-sm font-semibold text-muted hover:bg-gray-50 transition-colors flex items-center justify-center">← Back</button>
                    <button type="button" @click="nextStep()" class="flex-1 py-3 px-4 rounded-xl bg-primary hover:bg-primary-700 text-white text-sm font-semibold shadow-sm transition-colors flex items-center justify-center">Continue →</button>
                </div>
            </div>

            {{-- STEP 4: Account & Confirm --}}
            <div x-show="step === 4" class="card space-y-4">
                <div class="mb-2">
                    <h2 class="font-heading font-bold text-xl text-charcoal">Create Password</h2>
                    <p class="text-sm text-muted mt-1">Almost done! Set a strong password for your account.</p>
                </div>

                <div x-show="passwordError" x-cloak class="p-3 bg-red-50 border border-red-200 text-red-600 rounded-xl text-xs font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span x-text="passwordError"></span>
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-charcoal mb-1.5">Password <span class="text-red-500">*</span></label>
                    <input id="password" name="password" type="password" required autocomplete="new-password"
                           x-model="password"
                           @input="if (passwordError) validatePassword()"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm transition-all"
                           placeholder="Min 8 characters">
                    <p class="text-xs text-muted mt-1">Must contain uppercase, number, and special character.</p>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-charcoal mb-1.5">Confirm Password <span class="text-red-500">*</span></label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                           x-model="passwordConfirmation"
                           @input="if (passwordError) validatePassword()"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm transition-all"
                           placeholder="Re-enter your password">
                </div>


                <div class="text-xs text-muted p-3 bg-blue-50 border border-blue-100 rounded-xl flex gap-2">
                    <svg class="w-4 h-4 text-blue-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Your IC number is stored securely as a one-way hash. It cannot be read or shared by anyone.
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" @click="prevStep()" class="flex-1 py-3 px-4 rounded-xl border border-gray-200 text-sm font-semibold text-muted hover:bg-gray-50 transition-colors flex items-center justify-center">← Back</button>
                    <button type="submit" class="flex-1 py-3 px-4 rounded-xl bg-primary hover:bg-primary-700 text-white text-sm font-semibold shadow-sm transition-colors flex items-center justify-center">Create Account ✓</button>
                </div>
            </div>
        </form>

        <p class="text-center text-sm text-muted mt-6">
            Already have an account?
            <a href="{{ route('login') }}" class="text-primary font-semibold hover:underline">Sign in</a>
        </p>
        <p class="text-center text-sm text-muted mt-1">
            Registering a child?
            <a href="{{ route('register.parent') }}" class="text-amber-600 font-semibold hover:underline">Register as Parent</a>
        </p>
    </div>
</body>
</html>
