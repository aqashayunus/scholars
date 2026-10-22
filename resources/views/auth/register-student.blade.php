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
    } elseif ($errors->hasAny(['programme', 'level', 'levels', 'levels.*', 'school_name'])) {
        $initialStep = 2;
    } elseif ($errors->any()) {
        $initialStep = 1;
    }

    $oldLevels = old('levels');
    if (!$oldLevels && old('level')) {
        $oldLevels = array_filter(array_map('trim', explode(',', old('level'))));
    }
    if (!is_array($oldLevels)) {
        $oldLevels = [];
    }
    if (empty($oldLevels)) {
        $oldLevels = ($selectedProgramme ?? 'secondary') === 'primary' ? ['Year 4'] : ['Form 1'];
    }
@endphp
<body class="bg-surface min-h-screen py-10 px-4" x-data="studentRegData()">

<script>
function studentRegData() {
    return {
        step: {{ $initialStep }},
        totalSteps: 4,
        selectedEducationType: '{{ old('programme', $selectedProgramme ?? 'secondary') }}',
        selectedLevels: {!! json_encode(array_values($oldLevels)) !!},
        selectedLevel: '{{ implode(', ', $oldLevels) }}',
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
                'id'               => $f->id,
                'name'             => $f->name,
                'description'      => $f->description,
                'amount'           => (float) $f->amount,
                'education_level'  => $f->education_level,
                'frequency'        => $f->frequency,
                'package_type'     => $f->package_type ?? 'individual',
                'minimum_subjects' => $f->minimum_subjects,
                'maximum_subjects' => $f->maximum_subjects,
                'subject_names'    => $f->subjects->pluck('name')->unique()->values()->toArray(),
                'subject_ids'      => $f->subjects->pluck('id')->unique()->values()->toArray(),
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
            this.$nextTick(() => {
                const available = this.availableFeeStructures();
                if (available.length > 0 && !this.selectedFeeStructureId && this.selectedPlanType === 'package') {
                    this.selectFeeStructure(available[0]);
                }
            });

            this.$watch('selectedEducationType', (newVal, oldVal) => {
                if (oldVal !== undefined && newVal !== oldVal) {
                    const validLevels = this.levelsForType();
                    this.selectedLevels = this.selectedLevels.filter(lvl => validLevels.includes(lvl));
                    if (this.selectedLevels.length === 0 && validLevels.length > 0) {
                        this.selectedLevels = [validLevels[0]];
                    }
                    this.selectedLevel = this.selectedLevels.join(', ');
                    this.selectedSubjects = [];
                    this.syncFeeStructureForLevel();
                }
            });
        },

        toggleLevel(lvl) {
            if (this.selectedLevels.includes(lvl)) {
                this.selectedLevels = this.selectedLevels.filter(l => l !== lvl);
            } else {
                this.selectedLevels.push(lvl);
            }
            this.selectedLevel = this.selectedLevels.join(', ');
            this.pruneSelectedSubjects();
            this.syncFeeStructureForLevel();
            if (this.selectedPlanType === 'package' && this.selectedSubjects.length === 0) {
                const fs = this.getCurrentFeeStructure();
                if (fs) {
                    this.selectFeeStructure(fs);
                }
            }
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
            if (this.selectedLevels.length === 0 && !this.selectedEducationType) {
                return this.allFeeStructures;
            }
            const levels = this.selectedLevels.map(l => (l || '').toLowerCase());
            const edu = (this.selectedEducationType || '').toLowerCase();

            return this.allFeeStructures.filter(fs => {
                const target = (fs.education_level || '').toLowerCase();
                if (target === 'all') return true;

                if (levels.length === 0) {
                    if (edu === 'secondary') {
                        return target.includes('form') || target.includes('secondary') || target.includes('spm') || target.includes('pt3');
                    }
                    if (edu === 'primary') {
                        return target.includes('year') || target.includes('primary') || target.includes('upsr');
                    }
                    return true;
                }

                return levels.some(lvl => {
                    if (lvl && target.includes(lvl)) return true;
                    if (edu === 'secondary' && (target.includes('form') || target.includes('secondary') || target.includes('spm') || target.includes('pt3'))) {
                        if ((lvl.includes('form 1') || lvl.includes('form 2') || lvl.includes('form 3')) &&
                            (target.includes('1–3') || target.includes('1-3') || target.includes('pt3') || target.includes('lower'))) {
                            return true;
                        }
                        if ((lvl.includes('form 4') || lvl.includes('form 5')) &&
                            (target.includes('4–5') || target.includes('4-5') || target.includes('spm') || target.includes('upper'))) {
                            return true;
                        }
                    }
                    if (edu === 'primary' && (target.includes('year') || target.includes('primary') || target.includes('upsr'))) {
                        return true;
                    }
                    return false;
                });
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

            const validIdentifiers = this.filteredSubjects().map(s => this.subjectIdentifier(s));

            if (fs.package_type === 'fixed_bundle' && fs.subject_names && fs.subject_names.length > 0) {
                // Fixed bundle: admin fixed the subjects, select those matching current student level
                const bundleForLevel = this.filteredSubjects()
                    .filter(s => fs.subject_names.includes(s.name))
                    .map(s => this.subjectIdentifier(s));
                this.selectedSubjects = bundleForLevel.length > 0
                    ? [...new Set(bundleForLevel)]
                    : [...new Set(this.coreSubjects().map(s => this.subjectIdentifier(s)))];
            } else if (fs.package_type === 'pick_n') {
                // Pick-N: if nothing selected yet or selected more than max, pick top subjects
                const pool = (fs.subject_names && fs.subject_names.length > 0)
                    ? this.filteredSubjects().filter(s => fs.subject_names.includes(s.name))
                    : this.filteredSubjects();
                const max = fs.maximum_subjects || 4;
                const currentValid = this.selectedSubjects.filter(id => validIdentifiers.includes(id));
                if (currentValid.length === 0 || currentValid.length > max) {
                    this.selectedSubjects = pool.slice(0, max).map(s => this.subjectIdentifier(s));
                } else {
                    this.selectedSubjects = [...new Set(currentValid)];
                }
            } else {
                // Standard package or individual: auto-select core subjects for current level
                const coreList = this.coreSubjects().map(s => this.subjectIdentifier(s));
                const currentValid = this.selectedSubjects.filter(id => validIdentifiers.includes(id));
                this.selectedSubjects = [...new Set([...currentValid, ...coreList])];
            }
        },

        enrolledCount() {
            return this.filteredSubjects().filter(s => this.selectedSubjects.includes(this.subjectIdentifier(s))).length;
        },

        coreSubjects() {
            return this.filteredSubjects().filter(s => (s.category || '').toLowerCase() === 'core');
        },

        electiveSubjects() {
            return this.filteredSubjects().filter(s => (s.category || '').toLowerCase() !== 'core');
        },

        calculatedPricing() {
            const fs = this.getCurrentFeeStructure();
            const selectedList = this.filteredSubjects().filter(s => this.selectedSubjects.includes(this.subjectIdentifier(s)));

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
                    finalTotal: regularTotal,
                    totalCount: selectedList.length
                };
            }

            const effectivePackageBase = fs.amount;

            if (fs.package_type === 'fixed_bundle' || fs.package_type === 'pick_n') {
                const regularTotal = selectedList.reduce((sum, s) => sum + (s.monthly_fee || 0), 0);
                const max = fs.maximum_subjects || (fs.subject_names && fs.subject_names.length > 0 ? fs.subject_names.length : 4);
                const extraCount = Math.max(0, selectedList.length - max);
                let extraCost = 0;
                if (extraCount > 0) {
                    const discountFactor = 1 - (this.bundleDiscountPct / 100);
                    const extras = selectedList.slice(max);
                    extraCost = extras.reduce((sum, s) => sum + Math.round((s.monthly_fee || 0) * discountFactor * 100) / 100, 0);
                }
                const finalTotal = effectivePackageBase + extraCost;
                const savings = Math.max(0, regularTotal - finalTotal);

                return {
                    isPackage: true,
                    packageName: fs.name,
                    basePrice: effectivePackageBase,
                    coreCount: Math.min(selectedList.length, max),
                    electiveCount: extraCount,
                    regularTotal: regularTotal,
                    savings: savings,
                    finalTotal: finalTotal,
                    totalCount: selectedList.length
                };
            }

            const coreSelected = selectedList.filter(s => (s.category || '').toLowerCase() === 'core');
            const electiveSelected = selectedList.filter(s => (s.category || '').toLowerCase() !== 'core');

            const coreRegular = coreSelected.reduce((sum, s) => sum + (s.monthly_fee || 0), 0);
            const electiveRegular = electiveSelected.reduce((sum, s) => sum + (s.monthly_fee || 0), 0);
            const discountFactor = 1 - (this.bundleDiscountPct / 100);
            const electiveDiscounted = electiveSelected.reduce((sum, s) => sum + Math.round((s.monthly_fee || 0) * discountFactor * 100) / 100, 0);

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
                finalTotal: finalTotal,
                totalCount: selectedList.length
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

        subjectIdentifier(s) {
            if (this.selectedLevels.length > 1 && s.form_year) {
                return s.display_name || `${s.name} - ${s.form_year}`;
            }
            return s.name;
        },

        filteredSubjects() {
            let list = this.allSubjects;
            if (this.selectedEducationType) {
                list = list.filter(s => !s.education_type || s.education_type === this.selectedEducationType);
            }

            if (this.selectedLevels && this.selectedLevels.length > 0) {
                list = list.filter(s => !s.form_year || this.selectedLevels.includes(s.form_year));
            }

            // Deduplicate: if multiple levels selected, keep per-level subjects; otherwise keep unique by name
            const seen = new Set();
            return list.filter(s => {
                const key = this.subjectIdentifier(s);
                if (seen.has(key)) {
                    return false;
                }
                seen.add(key);
                return true;
            });
        },

        pruneSelectedSubjects() {
            const availableNames = new Set(this.filteredSubjects().map(s => this.subjectIdentifier(s)));
            this.selectedSubjects = [...new Set(this.selectedSubjects.filter(name => availableNames.has(name)))];
        },

        toggleSubject(name) {
            if (this.selectedSubjects.includes(name)) {
                this.selectedSubjects = this.selectedSubjects.filter(s => s !== name);
            } else {
                this.selectedSubjects = [...new Set([...this.selectedSubjects, name])];
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
            if (this.step === 2) {
                if (!this.selectedLevels || this.selectedLevels.length === 0) {
                    alert('Please select at least one Form / Year before continuing.');
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
                <img src="{{ asset('images/logo-vertical.png') }}" alt="Olympian Scholars" class="h-24 w-auto object-contain transition-transform group-hover:scale-105">
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
                            class="w-full pl-4 pr-10 py-2.5 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm transition-all">
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
                            class="w-full pl-4 pr-10 py-2.5 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm transition-all">
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
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-sm font-semibold text-charcoal">
                            Form / Year <span class="text-red-500">*</span>
                        </label>
                        <span class="text-xs font-semibold text-primary"
                              x-show="selectedLevels.length > 0"
                              x-text="selectedLevels.length + ' selected'"></span>
                    </div>
                    <p class="text-xs text-muted mb-2.5">
                        Select one or more forms/years you wish to access (e.g. Form 4 &amp; Form 5).
                    </p>

                    {{-- Hidden input for backward compatibility --}}
                    <input type="hidden" name="level" :value="selectedLevels.join(', ')">

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                        <template x-for="lvl in levelsForType()" :key="lvl">
                            <label class="relative cursor-pointer select-none group">
                                <input type="checkbox" name="levels[]" :value="lvl"
                                       :checked="selectedLevels.includes(lvl)"
                                       @change="toggleLevel(lvl)"
                                       class="sr-only peer">
                                <div class="p-3 rounded-xl border-2 transition-all flex items-center justify-between gap-2"
                                     :class="selectedLevels.includes(lvl)
                                         ? 'border-primary bg-primary-50/70 text-primary font-bold shadow-xs'
                                         : 'border-gray-200 bg-white text-charcoal hover:border-gray-300 hover:bg-gray-50/50'">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-4 h-4 rounded border flex items-center justify-center shrink-0 transition-colors"
                                             :class="selectedLevels.includes(lvl) ? 'bg-primary border-primary text-white' : 'border-gray-300 bg-white group-hover:border-gray-400'">
                                            <svg x-show="selectedLevels.includes(lvl)" class="w-3 h-3 stroke-current" fill="none" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <span class="text-xs sm:text-sm font-semibold truncate" x-text="lvl"></span>
                                    </div>
                                </div>
                            </label>
                        </template>
                    </div>

                    <div x-show="selectedLevels.length === 0" x-cloak class="mt-2 text-xs text-red-500 font-medium">
                        * Please select at least one Form / Year.
                    </div>
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
                                        <div class="flex items-center gap-1">
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider"
                                                  :class="fs.package_type === 'fixed_bundle' ? 'bg-amber-100 text-amber-800' : (fs.package_type === 'pick_n' ? 'bg-indigo-100 text-indigo-800' : 'bg-blue-100 text-primary')"
                                                  x-text="fs.package_type === 'fixed_bundle' ? '📦 Fixed Bundle' : (fs.package_type === 'pick_n' ? ('🎯 Pilih ' + (fs.maximum_subjects || 4) + ' Subjek') : 'Bundle Plan')">
                                            </span>
                                            <template x-if="fs.frequency === 'yearly'">
                                                <span class="px-1.5 py-0.5 rounded-md bg-emerald-100 text-emerald-800 text-[9px] font-bold uppercase tracking-wider">Tahunan</span>
                                            </template>
                                        </div>
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

                    {{-- Package Guidance Callout --}}
                    <template x-if="selectedPlanType === 'package' && getCurrentFeeStructure()">
                        <div class="p-3.5 rounded-2xl border text-xs transition-all"
                             :class="getCurrentFeeStructure().package_type === 'fixed_bundle' ? 'bg-amber-50/80 border-amber-200 text-amber-900' : (getCurrentFeeStructure().package_type === 'pick_n' ? 'bg-indigo-50/80 border-indigo-200 text-indigo-900' : 'bg-blue-50/80 border-blue-200 text-blue-900')">
                            <template x-if="getCurrentFeeStructure().package_type === 'fixed_bundle'">
                                <div class="flex items-start gap-2.5">
                                    <span class="text-base shrink-0">📦</span>
                                    <div>
                                        <p class="font-bold">Pakej Subjek Tetap (Fixed Bundle):</p>
                                        <p class="mt-0.5 text-[11px] text-amber-800 leading-relaxed">
                                            Pakej ini telah menetapkan <strong x-text="(getCurrentFeeStructure().subject_names || []).length"></strong> subjek yang telah dipilih secara automatik. Semua subjek ini disertakan dalam harga pakej.
                                        </p>
                                    </div>
                                </div>
                            </template>
                            <template x-if="getCurrentFeeStructure().package_type === 'pick_n'">
                                <div class="flex items-center justify-between gap-3 flex-wrap">
                                    <div class="flex items-start gap-2.5">
                                        <span class="text-base shrink-0">🎯</span>
                                        <div>
                                            <p class="font-bold">Pakej Pilihan Pelajar (Pick-N):</p>
                                            <p class="mt-0.5 text-[11px] text-indigo-800 leading-relaxed">
                                                Pilih mana-mana subjek di bawah sehingga had <strong x-text="getCurrentFeeStructure().maximum_subjects || 4"></strong> subjek.
                                            </p>
                                        </div>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-full font-bold text-xs shadow-2xs"
                                          :class="enrolledCount() === (getCurrentFeeStructure().maximum_subjects || 4) ? 'bg-emerald-600 text-white' : 'bg-indigo-200 text-indigo-900'"
                                          x-text="`${enrolledCount()}/${getCurrentFeeStructure().maximum_subjects || 4} Subjek Dipilih`">
                                    </span>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                {{-- 2. Core Subjects (Included in Package) --}}
                <div class="space-y-2 pt-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Core Curriculum Subjects
                            <span class="text-primary font-normal text-[11px]" x-show="selectedPlanType === 'package'">(Included in Bundle)</span>
                        </label>
                        <span class="text-[11px] text-muted" x-text="coreSubjects().filter(s => selectedSubjects.includes(subjectIdentifier(s))).length + '/' + coreSubjects().length + ' selected'"></span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 max-h-56 overflow-y-auto pr-1">
                        <template x-for="subject in coreSubjects()" :key="subjectIdentifier(subject)">
                            <label class="relative cursor-pointer">
                                <input type="checkbox" name="subjects[]" :value="subjectIdentifier(subject)"
                                       :checked="selectedSubjects.includes(subjectIdentifier(subject))"
                                       @change="toggleSubject(subjectIdentifier(subject))"
                                       class="sr-only peer">
                                <div class="p-2.5 rounded-xl border-2 transition-all flex items-center justify-between gap-2"
                                     :class="selectedSubjects.includes(subjectIdentifier(subject))
                                         ? 'border-primary text-primary bg-primary-50 font-semibold shadow-xs'
                                         : 'border-gray-200 text-muted hover:border-gray-300'">
                                    <span class="text-xs truncate font-medium"
                                          x-text="selectedLevels.length > 1 ? (subject.display_name || `${subject.name} - ${subject.form_year}`) : (subject.display_name ? subject.name : subject.name)">
                                    </span>
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
                            <template x-for="subject in electiveSubjects()" :key="subjectIdentifier(subject)">
                                <label class="relative cursor-pointer">
                                    <input type="checkbox" name="subjects[]" :value="subjectIdentifier(subject)"
                                           :checked="selectedSubjects.includes(subjectIdentifier(subject))"
                                           @change="toggleSubject(subjectIdentifier(subject))"
                                           class="sr-only peer">
                                    <div class="p-2.5 rounded-xl border-2 transition-all flex items-center justify-between gap-2"
                                         :class="selectedSubjects.includes(subjectIdentifier(subject))
                                             ? 'border-primary text-primary bg-primary-50 font-semibold shadow-xs'
                                             : 'border-gray-200 text-muted hover:border-gray-300'">
                                        <span class="text-xs truncate font-medium"
                                              x-text="selectedLevels.length > 1 ? (subject.display_name || `${subject.name} - ${subject.form_year}`) : (subject.display_name ? subject.name : subject.name)">
                                        </span>
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
                        <span class="font-semibold text-gray-800" x-text="enrolledCount() + ' Subject(s)'"></span>
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
                    <div class="relative" x-data="{ show: false }">
                        <input id="password" name="password" :type="show ? 'text' : 'password'" required autocomplete="new-password"
                               x-model="password"
                               @input="if (passwordError) validatePassword()"
                               class="w-full px-4 py-2.5 pr-10 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm transition-all"
                               placeholder="Min 8 characters">
                        <button type="button" @click="show = !show"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-charcoal focus:outline-none transition-colors"
                                :title="show ? 'Hide password' : 'Show password'"
                                aria-label="Toggle password visibility">
                            <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="show" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                    <p class="text-xs text-muted mt-1">Must contain uppercase, number, and special character.</p>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-charcoal mb-1.5">Confirm Password <span class="text-red-500">*</span></label>
                    <div class="relative" x-data="{ show: false }">
                        <input id="password_confirmation" name="password_confirmation" :type="show ? 'text' : 'password'" required autocomplete="new-password"
                               x-model="passwordConfirmation"
                               @input="if (passwordError) validatePassword()"
                               class="w-full px-4 py-2.5 pr-10 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm transition-all"
                               placeholder="Re-enter your password">
                        <button type="button" @click="show = !show"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-charcoal focus:outline-none transition-colors"
                                :title="show ? 'Hide password' : 'Show password'"
                                aria-label="Toggle password visibility">
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
