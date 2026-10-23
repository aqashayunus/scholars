<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Parent Registration | Olympian Scholars</title>
    <meta name="description" content="Register as a parent on Olympian Scholars to monitor your child's learning progress.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $initialStep = 1;
    if ($errors->hasAny(['child_name', 'child_ic_number', 'programme', 'level', 'school_name', 'subjects', 'subjects.*'])) {
        $initialStep = 2;
    } elseif ($errors->any()) {
        $initialStep = 1;
    }
@endphp
<body class="bg-surface min-h-screen py-10 px-4" x-data="parentRegData()">

<script>
function parentRegData() {
    return {
        step: {{ $initialStep }},
        totalSteps: 3,
        nationalityType: '{{ old('nationality_type', 'local') }}',
        childHasAccount: '{{ old('child_has_account', 'yes') }}',

        // Parent IC / Passport
        parentIcRaw: '{{ old('parent_ic_number', '') }}',
        parentIcValid: false,
        passportNumber: '{{ old('passport_number', '') }}',

        // Password & Validation
        parentPassword: '',
        parentPasswordConfirmation: '',
        parentPasswordError: '',

        // Address Fields & Auto-detection
        addressLine1: '{{ old('address_line_1', '') }}',
        addressLine2: '{{ old('address_line_2', '') }}',
        postcode: '{{ old('postcode', '') }}',
        city: '{{ old('city', '') }}',
        state: '{{ old('state', '') }}',
        country: '{{ old('country', 'Malaysia') }}',

        // Child IC lookup
        childIcRaw: '{{ old('child_ic_number', '') }}',
        childIcValid: false,
        childIcStatus: 'idle',
        childFound: false,
        childFoundName: '',
        childFoundLevel: '',
        childFoundSchool: '',

        selectedEducationType: '{{ old('programme', 'secondary') }}',
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

        init() {
            // Restore Parent IC validity if already provided
            if (this.parentIcRaw) {
                this.parentIcRaw = this.formatIC(this.parentIcRaw);
                this.parentIcValid = this.parentIcRaw.replace(/\D/g, '').length === 12;
            }

            // Restore Child IC validity and re-verify if existing child account
            if (this.childIcRaw) {
                this.childIcRaw = this.formatIC(this.childIcRaw);
                this.childIcValid = this.childIcRaw.replace(/\D/g, '').length === 12;
                if (this.childHasAccount === 'yes' && this.childIcValid) {
                    this.verifyChildIc();
                }
            }

            // Restore level for new child registration if provided
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

            this.$watch('postcode', (val) => {
                this.autoDetectPostcode(val);
            });
            if (this.postcode) {
                this.autoDetectPostcode(this.postcode);
            }
            this.$watch('selectedEducationType', (newVal, oldVal) => {
                if (oldVal !== undefined && newVal !== oldVal) {
                    if (!this.levelsForType().includes(this.selectedLevel)) {
                        this.selectedLevel = '';
                    }
                    this.selectedSubjects = [];
                    this.syncFeeStructureForLevel();
                }
            });
            this.$watch('selectedLevel', (newVal, oldVal) => {
                if (oldVal !== undefined && newVal !== oldVal) {
                    this.pruneSelectedSubjects();
                    this.syncFeeStructureForLevel();
                    if (this.selectedPlanType === 'package' && this.selectedSubjects.length === 0) {
                        const fs = this.getCurrentFeeStructure();
                        if (fs) {
                            this.selectFeeStructure(fs);
                        }
                    }
                }
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

            const validNames = this.filteredSubjects().map(s => s.name);

            if (fs.package_type === 'fixed_bundle' && fs.subject_names && fs.subject_names.length > 0) {
                // Fixed bundle: admin fixed the subjects, select those matching current student level
                const bundleForLevel = fs.subject_names.filter(name => validNames.includes(name));
                this.selectedSubjects = bundleForLevel.length > 0
                    ? [...new Set(bundleForLevel)]
                    : [...new Set(this.coreSubjects().map(s => s.name))];
            } else if (fs.package_type === 'pick_n') {
                // Pick-N: if nothing selected yet or selected more than max, pick top subjects
                const pool = (fs.subject_names && fs.subject_names.length > 0)
                    ? this.filteredSubjects().filter(s => fs.subject_names.includes(s.name))
                    : this.filteredSubjects();
                const max = fs.maximum_subjects || 4;
                const currentValid = this.selectedSubjects.filter(name => validNames.includes(name));
                if (currentValid.length === 0 || currentValid.length > max) {
                    this.selectedSubjects = pool.slice(0, max).map(s => s.name);
                } else {
                    this.selectedSubjects = [...new Set(currentValid)];
                }
            } else {
                // Standard package or individual: auto-select core subjects for current level
                const coreList = this.coreSubjects().map(s => s.name);
                const currentValid = this.selectedSubjects.filter(name => validNames.includes(name));
                this.selectedSubjects = [...new Set([...currentValid, ...coreList])];
            }
        },

        enrolledCount() {
            return this.filteredSubjects().filter(s => this.selectedSubjects.includes(s.name)).length;
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
            if (digits.length <= 8) return digits.substring(0, 6) + '-' + digits.substring(6);
            return digits.substring(0, 6) + '-' + digits.substring(6, 8) + '-' + digits.substring(8);
        },

        onParentICInput(event) {
            let formatted = this.formatIC(event.target.value);
            event.target.value = formatted;
            this.parentIcRaw = formatted;
            this.parentIcValid = formatted.replace(/\D/g, '').length === 12;
        },

        onPostcodeInput(event) {
            let val = event.target.value;
            this.postcode = val;
            this.autoDetectPostcode(val);
        },

        autoDetectPostcode(val) {
            if (!val || this.nationalityType !== 'local') {
                if (!val && this.nationalityType === 'local') {
                    this.city = '';
                    this.state = '';
                    const cityInput = document.getElementById('city');
                    const stateInput = document.getElementById('state');
                    if (cityInput) cityInput.value = '';
                    if (stateInput) stateInput.value = '';
                }
                return;
            }
            let clean = val.toString().replace(/\D/g, '');
            if (clean.length < 5) {
                this.city = '';
                this.state = '';
                const cityInput = document.getElementById('city');
                const stateInput = document.getElementById('state');
                if (cityInput) cityInput.value = '';
                if (stateInput) stateInput.value = '';
                return;
            }
            
            let num = parseInt(clean, 10);
            let p2 = clean.substring(0, 2);
            let p3 = clean.substring(0, 3);
            let p4 = clean.substring(0, 4);
            
            let detectedCity = '';
            let detectedState = '';

            // ==========================================
            // 1. PERLIS (01000 - 02700)
            // ==========================================
            if (p2 === '01' || p2 === '02') {
                detectedState = 'Perlis';
                if (num >= 2000 && num <= 2009) detectedCity = 'Kuala Perlis';
                else if (num >= 2100 && num <= 2190) detectedCity = 'Padang Besar';
                else if (num >= 2200 && num <= 2290) detectedCity = 'Kaki Bukit';
                else if (num >= 2600 && num <= 2690) detectedCity = 'Arau';
                else if (num >= 2700 && num <= 2790) detectedCity = 'Simpang Ampat';
                else detectedCity = 'Kangar';
            }

            // ==========================================
            // 2. KEDAH (05000 - 09810)
            // ==========================================
            else if (p2 === '05' || p2 === '06' || p2 === '07' || p2 === '08' || p2 === '09') {
                detectedState = 'Kedah';
                if (p2 === '07') detectedCity = 'Langkawi';
                else if (p3 === '060') {
                    if (num >= 6010 && num <= 6049) detectedCity = 'Changlun';
                    else if (num >= 6050) detectedCity = 'Bukit Kayu Hitam';
                    else detectedCity = 'Jitra';
                }
                else if (p3 === '061') detectedCity = 'Kodiang';
                else if (p3 === '062') detectedCity = 'Kepala Batas';
                else if (p3 === '063') detectedCity = 'Kuala Nerang';
                else if (p3 === '064') detectedCity = 'Pokok Sena';
                else if (p3 === '065') detectedCity = 'Langgar';
                else if (p3 === '066') detectedCity = 'Kuala Kedah';
                else if (p3 === '067') detectedCity = 'Pendang';
                else if (p3 === '068') detectedCity = 'Kota Sarang Semut';
                else if (p3 === '069') detectedCity = 'Yan';
                else if (p2 === '05') detectedCity = 'Alor Setar';
                else if (p3 === '081') detectedCity = 'Bedong';
                else if (p3 === '082') detectedCity = 'Sik';
                else if (p3 === '083') detectedCity = 'Gurun';
                else if (p3 === '084') detectedCity = 'Merbok';
                else if (p3 === '085') detectedCity = 'Kota Kuala Muda';
                else if (p3 === '086') detectedCity = 'Tikam Batu';
                else if (p3 === '087') detectedCity = 'Jeniang';
                else if (p3 === '088') detectedCity = 'Guar Chempedak';
                else if (p2 === '08') detectedCity = 'Sungai Petani';
                else if (p3 === '091') detectedCity = 'Baling';
                else if (p3 === '092') detectedCity = 'Kupang';
                else if (p3 === '093') detectedCity = 'Kuala Ketil';
                else if (p3 === '094') detectedCity = 'Padang Serai';
                else if (p3 === '096') detectedCity = 'Lunas';
                else if (p3 === '097') detectedCity = 'Karangan';
                else if (p3 === '098') {
                    if (num >= 9810) detectedCity = 'Bandar Baharu';
                    else detectedCity = 'Serdang';
                }
                else if (p2 === '09') detectedCity = 'Kulim';
            }

            // ==========================================
            // 3. PULAU PINANG (10000 - 14400)
            // ==========================================
            else if (p2 >= '10' && p2 <= '14') {
                detectedState = 'Pulau Pinang';
                if (p2 === '10') detectedCity = 'George Town';
                else if (p3 === '110') detectedCity = 'Balik Pulau';
                else if (p3 === '111') detectedCity = 'Batu Ferringhi';
                else if (p3 === '112') detectedCity = 'Tanjung Bungah';
                else if (p3 === '114' || p3 === '115') detectedCity = 'Ayer Itam';
                else if (p3 === '116') detectedCity = 'Jelutong';
                else if (p3 === '117') detectedCity = 'Gelugor';
                else if (p3 === '119') detectedCity = 'Bayan Lepas';
                else if (p3 === '130') detectedCity = 'Sungai Dua';
                else if (p3 === '131') detectedCity = 'Penaga';
                else if (p3 === '132') detectedCity = 'Kepala Batas';
                else if (p3 === '133') detectedCity = 'Tasek Gelugor';
                else if (p3 === '135') detectedCity = 'Permatang Pauh';
                else if (p3 === '136' || p3 === '137') detectedCity = 'Perai';
                else if (p2 === '12' || p2 === '13') detectedCity = 'Butterworth';
                else if (p3 === '141') detectedCity = 'Simpang Ampat';
                else if (p3 === '142') detectedCity = 'Sungai Jawi';
                else if (p3 === '143') detectedCity = 'Nibong Tebal';
                else if (p3 === '144') detectedCity = 'Kubang Semang';
                else if (p2 === '14') detectedCity = 'Bukit Mertajam';
            }

            // ==========================================
            // 4. KELANTAN (15000 - 18500)
            // ==========================================
            else if (p2 >= '15' && p2 <= '18') {
                detectedState = 'Kelantan';
                if (num >= 16300 && num <= 16390) detectedCity = 'Bachok';
                else if (num >= 16800 && num <= 16810) detectedCity = 'Pasir Puteh';
                else if (num >= 16200 && num <= 16250) detectedCity = 'Tumpat';
                else if (num >= 16400 && num <= 16450) detectedCity = 'Ketereh / Melor';
                else if (num >= 16600 && num <= 16690) detectedCity = 'Pulai Chondong';
                else if (num >= 16700 && num <= 16790) detectedCity = 'Cherang Ruku';
                else if (p2 === '15' || p2 === '16') detectedCity = 'Kota Bharu';
                else if (num >= 17500 && num <= 17590) detectedCity = 'Tanah Merah';
                else if (num >= 17600 && num <= 17690) detectedCity = 'Jeli';
                else if (num >= 17700 && num <= 17790) detectedCity = 'Ayer Lanas';
                else if (num >= 17200 && num <= 17290) detectedCity = 'Rantau Panjang';
                else if (p2 === '17') detectedCity = 'Pasir Mas';
                else if (num >= 18500 && num <= 18590) detectedCity = 'Machang';
                else if (num >= 18300 && num <= 18390) detectedCity = 'Gua Musang';
                else if (num >= 18200 && num <= 18290) detectedCity = 'Dabong';
                else if (p2 === '18') detectedCity = 'Kuala Krai';
            }

            // ==========================================
            // 5. TERENGGANU (20000 - 24300)
            // ==========================================
            else if (p2 >= '20' && p2 <= '24') {
                detectedState = 'Terengganu';
                if (num >= 21000 && num <= 21300) detectedCity = 'Kuala Nerus';
                else if (p2 === '20' || p2 === '21') {
                    if (p3 === '214' || p3 === '216') detectedCity = 'Marang';
                    else if (p3 === '215') detectedCity = 'Sungai Tong';
                    else if (p3 === '217') detectedCity = 'Kuala Berang';
                    else if (p3 === '218') detectedCity = 'Ajil';
                    else detectedCity = 'Kuala Terengganu';
                }
                else if (p2 === '22') {
                    if (p3 === '221') detectedCity = 'Permaisuri';
                    else if (p3 === '222' || p3 === '223') detectedCity = 'Besut';
                    else detectedCity = 'Jerteh';
                }
                else if (p2 === '23') {
                    if (p3 === '231') detectedCity = 'Paka';
                    else if (p3 === '232') detectedCity = 'Bukit Besi';
                    else if (p3 === '233') detectedCity = 'Ketengah Jaya';
                    else if (p3 === '234') detectedCity = 'Al Muktafi Billah Shah';
                    else detectedCity = 'Dungun';
                }
                else if (p2 === '24') {
                    if (p3 === '242' || p3 === '243') detectedCity = 'Kerteh';
                    else if (p3 === '241' || num === 24050) detectedCity = 'Kijal';
                    else if (num === 24060) detectedCity = 'Kemasik';
                    else detectedCity = 'Kemaman';
                }
            }

            // ==========================================
            // 6. PAHANG (25000 - 28800, 39000-39200, 49000, 69000)
            // ==========================================
            else if ((p2 >= '25' && p2 <= '28') || p2 === '39' || clean === '49000' || clean === '69000') {
                detectedState = 'Pahang';
                if (p2 === '39') detectedCity = 'Cameron Highlands';
                else if (clean === '49000') detectedCity = 'Bukit Fraser';
                else if (clean === '69000') detectedCity = 'Genting Highlands';
                else if (p2 === '25') detectedCity = 'Kuantan';
                else if (p2 === '26') {
                    if (p3 === '264') detectedCity = 'Bandar Pusat Jengka';
                    else if (p3 === '265') detectedCity = 'Maran';
                    else if (p3 === '266') detectedCity = 'Pekan';
                    else if (p3 === '267') detectedCity = 'Muadzam Shah';
                    else if (p3 === '268' || p3 === '269') detectedCity = 'Kuala Rompin';
                    else if (p3 === '262') detectedCity = 'Sungai Lembing';
                    else if (p3 === '260' || p3 === '261' || p3 === '263') detectedCity = 'Gambang / Beserah';
                    else detectedCity = 'Kuantan';
                }
                else if (p2 === '27') {
                    if (p3 === '270') detectedCity = 'Jerantut';
                    else if (p3 === '271' || p3 === '272') detectedCity = 'Kuala Lipis';
                    else if (p3 === '273') detectedCity = 'Benta';
                    else if (p3 === '274' || p3 === '276') detectedCity = 'Raub';
                    else detectedCity = 'Jerantut';
                }
                else if (p2 === '28') {
                    if (p3 === '280') detectedCity = 'Temerloh';
                    else if (p3 === '281') detectedCity = 'Chenor';
                    else if (p3 === '282') detectedCity = 'Bera';
                    else if (p3 === '283') detectedCity = 'Triang';
                    else if (p3 === '284') detectedCity = 'Mentakab';
                    else if (p3 === '285') detectedCity = 'Lanchang';
                    else if (p3 === '286') detectedCity = 'Karak';
                    else if (p3 === '287' || p3 === '288') detectedCity = 'Bentong';
                    else detectedCity = 'Temerloh';
                }
            }

            // ==========================================
            // 7. PERAK (30000 - 36810)
            // ==========================================
            else if (p2 >= '30' && p2 <= '36') {
                detectedState = 'Perak';
                if (p2 === '30') detectedCity = 'Ipoh';
                else if (p2 === '31') {
                    if (p3 === '312') detectedCity = 'Chemor';
                    else if (p3 === '315') detectedCity = 'Lahat / Pusing';
                    else if (p3 === '316') detectedCity = 'Gopeng';
                    else if (p3 === '317') detectedCity = 'Malim Nawar';
                    else if (p3 === '318') detectedCity = 'Tanjung Tualang';
                    else if (p3 === '319') detectedCity = 'Kampar';
                    else if (num >= 31150 && num <= 31250) detectedCity = 'Tanjung Rambutan';
                    else detectedCity = 'Ipoh';
                }
                else if (p2 === '32') {
                    if (p3 === '321' || p3 === '322') detectedCity = 'Lumut';
                    else if (p3 === '323') detectedCity = 'Pulau Pangkor';
                    else if (p3 === '324') detectedCity = 'Ayer Tawar';
                    else if (p3 === '326') detectedCity = 'Seri Iskandar / Bota';
                    else if (p3 === '327') detectedCity = 'Beruas';
                    else if (p3 === '328') detectedCity = 'Parit';
                    else if (p3 === '329') detectedCity = 'Pantai Remis';
                    else if (num >= 32040) detectedCity = 'Seri Manjung';
                    else detectedCity = 'Sitiawan';
                }
                else if (p2 === '33') {
                    if (p3 === '331') detectedCity = 'Pengkalan Hulu';
                    else if (p3 === '333') detectedCity = 'Gerik';
                    else if (p3 === '334') detectedCity = 'Lenggong';
                    else if (p3 === '335') detectedCity = 'Sauk';
                    else if (p3 === '336') detectedCity = 'Enggor';
                    else if (p3 === '337') detectedCity = 'Padang Rengas';
                    else detectedCity = 'Kuala Kangsar';
                }
                else if (p2 === '34') {
                    if (p3 === '341') detectedCity = 'Selama';
                    else if (p3 === '342') detectedCity = 'Parit Buntar';
                    else if (p3 === '343') detectedCity = 'Bagan Serai';
                    else if (p3 === '345') detectedCity = 'Batu Kurau';
                    else if (p3 === '346') detectedCity = 'Kamunting';
                    else if (p3 === '347') detectedCity = 'Simpang';
                    else if (p3 === '348') detectedCity = 'Changkat Jering';
                    else detectedCity = 'Taiping';
                }
                else if (p2 === '35') {
                    if (p3 === '355') detectedCity = 'Bidor';
                    else if (p3 === '356') detectedCity = 'Sungkai';
                    else if (p3 === '357') detectedCity = 'Trolak';
                    else if (p3 === '358') detectedCity = 'Slim River';
                    else if (p3 === '359') detectedCity = 'Tanjong Malim';
                    else detectedCity = 'Tapah';
                }
                else if (p2 === '36') {
                    if (p3 === '362') detectedCity = 'Selekoh';
                    else if (p3 === '363') detectedCity = 'Sungai Sumun';
                    else if (p3 === '364') detectedCity = 'Hutan Melintang';
                    else if (p3 === '367') detectedCity = 'Langkap';
                    else if (p3 === '368') detectedCity = 'Kampung Gajah';
                    else detectedCity = 'Teluk Intan';
                }
            }

            // ==========================================
            // 8. SELANGOR (40000 - 48300, 63000-68100)
            // ==========================================
            else if ((p2 >= '40' && p2 <= '48') || p2 === '63' || p2 === '64' || p2 === '68') {
                detectedState = 'Selangor';
                if (p2 === '40') detectedCity = 'Shah Alam';
                else if (p2 === '41') detectedCity = 'Klang';
                else if (p2 === '42') {
                    if (p3 === '420') detectedCity = 'Pelabuhan Klang';
                    else if (p3 === '422') detectedCity = 'Kapar';
                    else if (p3 === '423') detectedCity = 'Bandar Puncak Alam';
                    else if (p3 === '425') detectedCity = 'Telok Panglima Garang';
                    else if (p3 === '426') detectedCity = 'Jenjarom';
                    else if (p3 === '427') detectedCity = 'Banting';
                    else if (p3 === '428') detectedCity = 'Tanjong Sepat';
                    else if (num === 42920) detectedCity = 'Pulau Carey';
                    else if (num === 42940) detectedCity = 'Pulau Indah';
                    else if (num === 42960) detectedCity = 'Pulau Ketam';
                    else detectedCity = 'Klang';
                }
                else if (p2 === '43') {
                    if (p3 === '435') detectedCity = 'Semenyih';
                    else if (p3 === '436') detectedCity = 'Bandar Baru Bangi';
                    else if (p3 === '433') detectedCity = 'Seri Kembangan';
                    else if (p3 === '434') detectedCity = 'Serdang';
                    else if (p3 === '437') detectedCity = 'Beranang';
                    else if (p3 === '438') detectedCity = 'Dengkil';
                    else if (p3 === '439') {
                        if (num >= 43950) detectedCity = 'Sungai Pelek';
                        else detectedCity = 'Sepang';
                    }
                    else if (p3 === '431') detectedCity = 'Hulu Langat';
                    else if (p3 === '432') detectedCity = 'Cheras';
                    else detectedCity = 'Kajang';
                }
                else if (p2 === '44') {
                    if (p3 === '441') detectedCity = 'Kerling';
                    else if (p3 === '442') detectedCity = 'Rasa';
                    else if (p3 === '443') detectedCity = 'Batang Kali';
                    else detectedCity = 'Kuala Kubu Bharu';
                }
                else if (p2 === '45') {
                    if (p3 === '451') detectedCity = 'Sungai Air Tawar';
                    else if (p3 === '452') detectedCity = 'Sabak Bernam';
                    else if (p3 === '453') detectedCity = 'Sungai Besar';
                    else if (p3 === '454') detectedCity = 'Sekinchan';
                    else if (p3 === '455') detectedCity = 'Tanjong Karang';
                    else if (p3 === '456') detectedCity = 'Bestari Jaya';
                    else if (p3 === '457') detectedCity = 'Bukit Rotan';
                    else if (p3 === '458') detectedCity = 'Jeram';
                    else detectedCity = 'Kuala Selangor';
                }
                else if (p2 === '46') detectedCity = 'Petaling Jaya';
                else if (p2 === '47') {
                    if (p3 === '470') detectedCity = 'Sungai Buloh';
                    else if (p3 === '471') detectedCity = 'Puchong';
                    else if (p3 === '475' || p3 === '476') detectedCity = 'Subang Jaya';
                    else if (p3 === '478') detectedCity = 'Petaling Jaya / Kota Damansara';
                    else detectedCity = 'Petaling Jaya';
                }
                else if (p2 === '48') {
                    if (p3 === '481') detectedCity = 'Batu Arang';
                    else if (p3 === '482') detectedCity = 'Serendah';
                    else detectedCity = 'Rawang';
                }
                else if (p2 === '63') detectedCity = 'Cyberjaya';
                else if (p2 === '64') detectedCity = 'KLIA / Sepang';
                else if (p2 === '68') {
                    if (p3 === '681') detectedCity = 'Batu Caves';
                    else detectedCity = 'Ampang';
                }
            }

            // ==========================================
            // 9. W.P. KUALA LUMPUR (50000 - 60000)
            // ==========================================
            else if (num >= 50000 && num <= 60000) {
                detectedState = 'W.P. Kuala Lumpur';
                if (p3 === '510' || p3 === '511') detectedCity = 'Sentul';
                else if (p3 === '512') detectedCity = 'Segambut';
                else if (p3 === '520') detectedCity = 'Jinjang';
                else if (p3 === '521' || p3 === '522') detectedCity = 'Kepong';
                else if (p3 === '530' || p3 === '532') detectedCity = 'Setapak';
                else if (p3 === '531') detectedCity = 'Gombak';
                else if (p3 === '533') detectedCity = 'Wangsa Maju';
                else if (p3 === '540' || p3 === '542') detectedCity = 'Setiawangsa';
                else if (p3 === '553' || p3 === '560' || p3 === '561') detectedCity = 'Cheras';
                else if (p3 === '570') detectedCity = 'Bukit Jalil';
                else if (p3 === '571') detectedCity = 'Sri Petaling';
                else if (p3 === '580') detectedCity = 'Taman Desa';
                else if (p3 === '581') detectedCity = 'Kuchai Lama';
                else if (p3 === '582') detectedCity = 'Old Klang Road';
                else if (p3 === '590' || p3 === '591') detectedCity = 'Bangsar';
                else if (p3 === '592') detectedCity = 'Bangsar South';
                else if (p3 === '600') detectedCity = 'Taman Tun Dr Ismail (TTDI)';
                else detectedCity = 'Kuala Lumpur';
            }

            // ==========================================
            // 10. W.P. PUTRAJAYA (62000 - 62988)
            // ==========================================
            else if (p2 === '62') {
                detectedCity = 'Putrajaya';
                detectedState = 'W.P. Putrajaya';
            }

            // ==========================================
            // 11. NEGERI SEMBILAN (70000 - 73509)
            // ==========================================
            else if (p2 >= '70' && p2 <= '73') {
                detectedState = 'Negeri Sembilan';
                if (p2 === '70') detectedCity = 'Seremban';
                else if (p2 === '71') {
                    if (p3 === '710') detectedCity = 'Port Dickson';
                    else if (p3 === '711') detectedCity = 'Linggi';
                    else if (p3 === '712') detectedCity = 'Rantau';
                    else if (p3 === '713') detectedCity = 'Rembau';
                    else if (p3 === '714') detectedCity = 'Pedas';
                    else if (p3 === '715') detectedCity = 'Seri Menanti';
                    else if (p3 === '716') detectedCity = 'Kuala Klawang / Jelebu';
                    else if (p3 === '717') detectedCity = 'Mantin';
                    else if (p3 === '718') detectedCity = 'Nilai';
                    else if (p3 === '719') detectedCity = 'Labu / Enstek';
                    else detectedCity = 'Port Dickson';
                }
                else if (p2 === '72') {
                    if (p3 === '721') detectedCity = 'Bahau';
                    else if (p3 === '722') detectedCity = 'Batu Kikir';
                    else if (p3 === '723') detectedCity = 'Simpang Pertang';
                    else if (p3 === '724') detectedCity = 'Simpang Durian';
                    else if (p3 === '725') detectedCity = 'Juasseh';
                    else detectedCity = 'Kuala Pilah';
                }
                else if (p2 === '73') {
                    if (p3 === '731') detectedCity = 'Johol';
                    else if (p3 === '732') detectedCity = 'Gemencheh';
                    else if (p3 === '734') detectedCity = 'Gemas';
                    else if (p3 === '735') detectedCity = 'Rompin';
                    else detectedCity = 'Tampin';
                }
            }

            // ==========================================
            // 12. MELAKA (75000 - 78309)
            // ==========================================
            else if (p2 >= '75' && p2 <= '78') {
                detectedState = 'Melaka';
                if (p2 === '75') {
                    if (p3 === '754') detectedCity = 'Ayer Keroh';
                    else if (p3 === '753') detectedCity = 'Batu Berendam';
                    else detectedCity = 'Melaka';
                }
                else if (p2 === '76') {
                    if (p3 === '761') detectedCity = 'Durian Tunggal';
                    else if (p3 === '763') detectedCity = 'Sungai Udang';
                    else if (p3 === '764') detectedCity = 'Tanjung Kling';
                    else detectedCity = 'Melaka';
                }
                else if (p2 === '77') {
                    if (p3 === '771') detectedCity = 'Asahan';
                    else if (p3 === '772') detectedCity = 'Bemban';
                    else if (p3 === '773') detectedCity = 'Merlimau';
                    else if (p3 === '774') detectedCity = 'Sungai Rambai';
                    else if (p3 === '775') detectedCity = 'Selandar';
                    else detectedCity = 'Jasin';
                }
                else if (p2 === '78') {
                    if (p3 === '781') detectedCity = 'Lubok China';
                    else if (p3 === '782') detectedCity = 'Kuala Sungai Baru';
                    else if (p3 === '783') detectedCity = 'Masjid Tanah';
                    else detectedCity = 'Alor Gajah';
                }
            }

            // ==========================================
            // 13. JOHOR (80000 - 86900)
            // ==========================================
            else if (p2 >= '80' && p2 <= '86') {
                detectedState = 'Johor';
                if (p2 === '80') detectedCity = 'Johor Bahru';
                else if (p2 === '81') {
                    if (p3 === '810') detectedCity = 'Kulai';
                    else if (p3 === '813') detectedCity = 'Skudai';
                    else if (p3 === '814') detectedCity = 'Senai';
                    else if (p3 === '815') {
                        if (num >= 81550) detectedCity = 'Gelang Patah / Iskandar Puteri';
                        else detectedCity = 'Pekan Nanas';
                    }
                    else if (p3 === '816' || num === 81930) detectedCity = 'Bandar Penawar';
                    else if (p3 === '817') detectedCity = 'Pasir Gudang / Masai';
                    else if (p3 === '818') detectedCity = 'Ulu Tiram';
                    else if (p3 === '819') {
                        if (num >= 81940) detectedCity = 'Pengerang';
                        else detectedCity = 'Kota Tinggi';
                    }
                    else detectedCity = 'Johor Bahru';
                }
                else if (p2 === '82') {
                    if (p3 === '821') detectedCity = 'Ayer Baloi';
                    else if (p3 === '822') detectedCity = 'Benut';
                    else if (p3 === '823') detectedCity = 'Kukup';
                    else detectedCity = 'Pontian';
                }
                else if (p2 === '83') {
                    if (p3 === '831') detectedCity = 'Rengit';
                    else if (p3 === '832') detectedCity = 'Senggarang';
                    else if (p3 === '833') detectedCity = 'Sri Gading';
                    else if (p3 === '835') detectedCity = 'Parit Sulong';
                    else if (p3 === '837') detectedCity = 'Yong Peng';
                    else detectedCity = 'Batu Pahat';
                }
                else if (p2 === '84') {
                    if (p3 === '842') detectedCity = 'Bakri';
                    else if (p3 === '843') detectedCity = 'Bukit Pasir';
                    else if (p3 === '844') detectedCity = 'Sungai Mati';
                    else if (p3 === '845') detectedCity = 'Panchor';
                    else if (p3 === '846') detectedCity = 'Pagoh';
                    else if (p3 === '847') detectedCity = 'Gerisek';
                    else if (p3 === '848') detectedCity = 'Bukit Gambir';
                    else if (p3 === '849') detectedCity = 'Tangkak';
                    else detectedCity = 'Muar';
                }
                else if (p2 === '85') {
                    if (p3 === '851') detectedCity = 'Batu Anam';
                    else if (p3 === '852') detectedCity = 'Jementah';
                    else if (p3 === '853') detectedCity = 'Labis';
                    else if (p3 === '854') detectedCity = 'Chaah';
                    else detectedCity = 'Segamat';
                }
                else if (p2 === '86') {
                    if (p3 === '861' || p3 === '863') detectedCity = 'Renggam';
                    else if (p3 === '862') detectedCity = 'Simpang Renggam';
                    else if (p3 === '864') detectedCity = 'Parit Raja';
                    else if (p3 === '865') detectedCity = 'Bekok';
                    else if (p3 === '866') detectedCity = 'Paloh';
                    else if (p3 === '867') detectedCity = 'Kahang';
                    else if (p3 === '868') detectedCity = 'Mersing';
                    else if (p3 === '869') detectedCity = 'Endau';
                    else detectedCity = 'Kluang';
                }
            }

            // ==========================================
            // 14. W.P. LABUAN (87000 - 87033)
            // ==========================================
            else if (p2 === '87') {
                detectedCity = 'Labuan';
                detectedState = 'W.P. Labuan';
            }

            // ==========================================
            // 15. SABAH (88000 - 91309)
            // ==========================================
            else if (p2 >= '88' && p2 <= '91') {
                detectedState = 'Sabah';
                if (p2 === '88') detectedCity = 'Kota Kinabalu';
                else if (p2 === '89') {
                    if (p3 === '890') detectedCity = 'Keningau';
                    else if (p3 === '891') {
                        if (num >= 89150) detectedCity = 'Kota Belud';
                        else if (num >= 89130) detectedCity = 'Pitas';
                        else if (num >= 89100) detectedCity = 'Kota Marudu';
                        else detectedCity = 'Kudat';
                    }
                    else if (p3 === '892') {
                        if (num >= 89260) detectedCity = 'Tenghilan';
                        else if (num >= 89250) detectedCity = 'Tamparuli';
                        else detectedCity = 'Tuaran';
                    }
                    else if (p3 === '893') {
                        if (num >= 89320) detectedCity = 'Telupid';
                        else detectedCity = 'Ranau';
                    }
                    else if (p3 === '895') detectedCity = 'Penampang';
                    else if (p3 === '896') {
                        if (num >= 89650) detectedCity = 'Bongawan';
                        else detectedCity = 'Papar';
                    }
                    else if (p3 === '897') {
                        if (num >= 89760) detectedCity = 'Menumbok';
                        else if (num >= 89720) detectedCity = 'Kuala Penyu';
                        else detectedCity = 'Membakut';
                    }
                    else if (p3 === '898') {
                        if (num >= 89850) detectedCity = 'Sipitang';
                        else detectedCity = 'Beaufort';
                    }
                    else if (p3 === '899') {
                        if (num >= 89950) detectedCity = 'Nabawan';
                        else detectedCity = 'Tenom';
                    }
                    else detectedCity = 'Kota Kinabalu';
                }
                else if (p2 === '90') {
                    if (num >= 90400) detectedCity = 'Beluran';
                    else detectedCity = 'Sandakan';
                }
                else if (p2 === '91') {
                    if (p3 === '911') detectedCity = 'Lahad Datu';
                    else if (p3 === '912') detectedCity = 'Kunak';
                    else if (p3 === '913') detectedCity = 'Semporna';
                    else detectedCity = 'Tawau';
                }
            }

            // ==========================================
            // 16. SARAWAK (93000 - 98859)
            // ==========================================
            else if (p2 >= '93' && p2 <= '98') {
                detectedState = 'Sarawak';
                if (p2 === '93') detectedCity = 'Kuching';
                else if (p2 === '94') {
                    if (p3 === '940') detectedCity = 'Bau';
                    else if (p3 === '942' || p3 === '945') detectedCity = 'Lundu';
                    else if (p3 === '943') detectedCity = 'Kota Samarahan';
                    else if (p3 === '946') detectedCity = 'Asajaya';
                    else if (p3 === '947') detectedCity = 'Serian';
                    else if (p3 === '948') detectedCity = 'Simunjan';
                    else detectedCity = 'Kuching';
                }
                else if (p2 === '95') {
                    if (p3 === '953') detectedCity = 'Roban';
                    else if (p3 === '954') detectedCity = 'Saratok';
                    else if (p3 === '955') detectedCity = 'Debak';
                    else if (p3 === '956') detectedCity = 'Spaoh';
                    else if (p3 === '957') detectedCity = 'Betong';
                    else if (p3 === '958') detectedCity = 'Engkilili';
                    else if (p3 === '959') detectedCity = 'Lubok Antu';
                    else detectedCity = 'Sri Aman';
                }
                else if (p2 === '96') {
                    if (p3 === '961') detectedCity = 'Sarikei';
                    else if (p3 === '962') detectedCity = 'Daro';
                    else if (p3 === '963') detectedCity = 'Dalat';
                    else if (p3 === '964') detectedCity = 'Mukah';
                    else if (p3 === '965') detectedCity = 'Bintangor';
                    else if (p3 === '966') detectedCity = 'Julau';
                    else if (p3 === '967') detectedCity = 'Kanowit';
                    else if (p3 === '968') detectedCity = 'Kapit';
                    else if (p3 === '969') detectedCity = 'Belaga';
                    else detectedCity = 'Sibu';
                }
                else if (p2 === '97') {
                    if (p3 === '971') detectedCity = 'Sebauh';
                    else if (p3 === '972') detectedCity = 'Tatau';
                    else detectedCity = 'Bintulu';
                }
                else if (p2 === '98') {
                    if (p3 === '981') {
                        if (num >= 98150) detectedCity = 'Bekenu';
                        else detectedCity = 'Lutong';
                    }
                    else if (p3 === '982') detectedCity = 'Niah';
                    else if (p3 === '987') detectedCity = 'Limbang';
                    else if (p3 === '988') detectedCity = 'Lawas';
                    else detectedCity = 'Miri';
                }
            }

            if (detectedCity) {
                this.city = detectedCity;
                this.state = detectedState;
                this.country = 'Malaysia';
                
                const cityInput = document.getElementById('city');
                const stateInput = document.getElementById('state');
                if (cityInput) cityInput.value = detectedCity;
                if (stateInput) stateInput.value = detectedState;
            }
        },

        onChildICInput(event) {
            let formatted = this.formatIC(event.target.value);
            event.target.value = formatted;
            this.childIcRaw = formatted;
            let digits = formatted.replace(/\D/g, '');
            this.childIcValid = digits.length === 12;
            if (!this.childIcValid) {
                this.childIcStatus = 'idle';
                this.childFound = false;
            }
        },

        async verifyChildIc() {
            if (!this.childIcValid) return;
            this.childIcStatus = 'checking';
            this.childFound = false;
            try {
                const res = await fetch('{{ route('register.verify.ic') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: JSON.stringify({ ic_number: this.childIcRaw })
                });
                const data = await res.json();
                if (data.found) {
                    this.childIcStatus = 'found';
                    this.childFound = true;
                    this.childFoundName = data.name;
                    this.childFoundLevel = data.level || '';
                    this.childFoundSchool = data.school || '';
                } else {
                    this.childIcStatus = 'not_found';
                    this.childFound = false;
                }
            } catch(e) {
                this.childIcStatus = 'not_found';
            }
        },

        levelsForType() {
            if (this.selectedEducationType === 'primary') return ['Year 4', 'Year 5', 'Year 6'];
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

            const seen = new Set();
            return list.filter(s => {
                if (seen.has(s.name)) return false;
                seen.add(s.name);
                return true;
            });
        },

        pruneSelectedSubjects() {
            const availableNames = new Set(this.filteredSubjects().map(s => s.name));
            this.selectedSubjects = [...new Set(this.selectedSubjects.filter(name => availableNames.has(name)))];
        },

        toggleSubject(name) {
            if (this.selectedSubjects.includes(name)) {
                this.selectedSubjects = this.selectedSubjects.filter(s => s !== name);
            } else {
                this.selectedSubjects = [...new Set([...this.selectedSubjects, name])];
            }
        },

        validateParentPassword() {
            this.parentPasswordError = '';
            const pass = this.parentPassword || document.getElementById('password')?.value || '';
            const passConf = this.parentPasswordConfirmation || document.getElementById('password_confirmation')?.value || '';
            if (!pass) return true;
            if (pass.length < 8) {
                this.parentPasswordError = 'Password must be at least 8 characters long.';
                return false;
            }
            const hasUpper = /[A-Z]/.test(pass);
            const hasNumber = /[0-9]/.test(pass);
            const hasSpecial = /[^A-Za-z0-9]/.test(pass);
            if (!hasUpper || !hasNumber || !hasSpecial) {
                this.parentPasswordError = 'Password must contain at least one uppercase letter, one number, and one special character.';
                return false;
            }
            if (passConf && pass !== passConf) {
                this.parentPasswordError = 'Passwords do not match. Please re-enter.';
                return false;
            }
            return true;
        },

        nextStep() {
            if (this.step === 1) {
                const name = document.getElementById('parent_name')?.value?.trim();
                const email = document.getElementById('email')?.value?.trim();
                const phone = document.getElementById('phone')?.value?.trim();
                const pass = this.parentPassword || document.getElementById('password')?.value || '';
                const passConf = this.parentPasswordConfirmation || document.getElementById('password_confirmation')?.value || '';
                const rel = document.getElementById('relationship')?.value;
                const addr1 = document.getElementById('address_line_1')?.value?.trim();
                const pcode = document.getElementById('postcode')?.value?.trim();
                const cityVal = document.getElementById('city')?.value?.trim();
                const stateVal = document.getElementById('state')?.value?.trim();

                if (!name || !email || !phone || !pass || !passConf || !rel || !addr1 || !pcode || !cityVal || !stateVal) {
                    alert('Please fill in all required parent and address details before continuing.');
                    return;
                }

                if (this.nationalityType === 'local') {
                    const parentDigits = this.parentIcRaw.replace(/\D/g, '');
                    if (parentDigits.length !== 12) {
                        alert('Please enter a valid 12-digit MyKad / IC number in the format XXXXXX-XX-XXXX.');
                        return;
                    }
                } else {
                    const passport = document.getElementById('passport_number')?.value?.trim();
                    if (!passport) {
                        alert('Please enter your Passport / National ID number.');
                        return;
                    }
                }

                // Enforce password requirements before proceeding to Step 2
                if (pass.length < 8) {
                    this.parentPasswordError = 'Password must be at least 8 characters long.';
                    alert('Password must be at least 8 characters long.');
                    document.getElementById('password')?.focus();
                    return;
                }
                const hasUpper = /[A-Z]/.test(pass);
                const hasNumber = /[0-9]/.test(pass);
                const hasSpecial = /[^A-Za-z0-9]/.test(pass);
                if (!hasUpper || !hasNumber || !hasSpecial) {
                    this.parentPasswordError = 'Password must contain at least one uppercase letter, one number, and one special character.';
                    alert('Password must contain at least one uppercase letter, one number, and one special character.');
                    document.getElementById('password')?.focus();
                    return;
                }
                if (pass !== passConf) {
                    this.parentPasswordError = 'Passwords do not match. Please re-enter.';
                    alert('Passwords do not match. Please re-enter.');
                    document.getElementById('password_confirmation')?.focus();
                    return;
                }
                this.parentPasswordError = '';
            }

            if (this.step === 2) {
                const digits = this.childIcRaw.replace(/\D/g, '');
                if (digits.length !== 12) {
                    alert('Please enter a valid 12-digit IC number for your child.');
                    return;
                }
                if (this.childHasAccount === 'yes' && !this.childFound) {
                    alert('Please verify your child\'s IC number before continuing.');
                    return;
                }
                if (this.childHasAccount === 'no') {
                    const childName = document.getElementById('child_name')?.value?.trim();
                    if (!childName) {
                        alert('Please enter your child\'s full name.');
                        return;
                    }
                }
            }

            if (this.step < this.totalSteps) {
                this.step++;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },

        prevStep() {
            if (this.step > 1) {
                this.step--;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
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
                <span class="text-xs font-semibold text-muted">Step <span x-text="step"></span> of 3</span>
                <span class="text-xs text-muted"
                      x-text="['Parent & Address Information', 'Child Identity & Details', 'Confirm & Submit'][step-1]"></span>
            </div>
            <div class="h-2 bg-primary-100 rounded-full overflow-hidden">
                <div class="h-2 bg-primary rounded-full transition-all duration-500"
                     :style="'width:' + (step / 3 * 100) + '%'"></div>
            </div>
            <div class="flex justify-between mt-2">
                <template x-for="i in 3" :key="i">
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
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl text-sm shadow-xs">
            <p class="font-bold mb-1.5 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Please review the errors below:
            </p>
            <ul class="list-disc list-inside space-y-0.5 text-xs text-red-600 pl-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('register.parent.submit') }}"
              @keydown.enter.prevent="if (step < totalSteps) nextStep()"
              class="space-y-5">
            @csrf

            {{-- STEP 1: Parent & Address Information --}}
            <div x-show="step === 1" class="card space-y-4">
                <div class="mb-2">
                    <h2 class="font-heading font-bold text-xl text-charcoal">Parent Information</h2>
                    <p class="text-sm text-muted mt-1">Create your parent account and provide your identity and address details.</p>
                </div>

                {{-- Full Name --}}
                <div>
                    <label for="parent_name" class="block text-sm font-semibold text-charcoal mb-1.5">Your Full Name <span class="text-red-500">*</span></label>
                    <input id="parent_name" name="parent_name" type="text" value="{{ old('parent_name') }}" required autocomplete="name"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm transition-all"
                           placeholder="e.g. NURUL HIDAYAH BINTI HASSAN">
                </div>

                {{-- Citizenship / Nationality Dropdown (After Name) --}}
                <div>
                    <label for="nationality_type" class="block text-sm font-semibold text-charcoal mb-1.5">
                        Citizenship / Nationality <span class="text-red-500">*</span>
                    </label>
                    <select id="nationality_type" name="nationality_type" x-model="nationalityType" required
                            class="w-full pl-4 pr-10 py-2.5 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm transition-all">
                        <option value="local">Local</option>
                        <option value="international">International</option>
                    </select>
                </div>

                {{-- Parent IC (Local) --}}
                <div x-show="nationalityType === 'local'">
                    <label for="parent_ic_number" class="block text-sm font-semibold text-charcoal mb-1.5">
                        Your MyKad / IC Number <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input id="parent_ic_number" name="parent_ic_number" type="text"
                               x-model="parentIcRaw"
                               @input="onParentICInput($event)"
                               value="{{ old('parent_ic_number') }}"
                               maxlength="14"
                               placeholder="e.g. 123456-78-9012"
                               class="w-full px-4 py-2.5 pr-10 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm transition-all"
                               :class="parentIcRaw.length > 0 ? (parentIcValid ? 'border-emerald-400 bg-emerald-50/30' : 'border-red-300') : ''">
                        <div class="absolute right-3 top-1/2 -translate-y-1/2" x-show="parentIcRaw.length > 0">
                            <svg x-show="parentIcValid" class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <svg x-show="!parentIcValid" class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Passport Number (International) --}}
                <div x-show="nationalityType === 'international'" x-cloak>
                    <label for="passport_number" class="block text-sm font-semibold text-charcoal mb-1.5">
                        Passport / National ID Number <span class="text-red-500">*</span>
                    </label>
                    <input id="passport_number" name="passport_number" type="text"
                           x-model="passportNumber"
                           value="{{ old('passport_number') }}"
                           placeholder="e.g. A12345678"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm transition-all">
                </div>

                {{-- Relationship to child --}}
                <div>
                    <label for="relationship" class="block text-sm font-semibold text-charcoal mb-1.5">Relationship to Child <span class="text-red-500">*</span></label>
                    <select id="relationship" name="relationship" required
                            class="w-full pl-4 pr-10 py-2.5 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm transition-all">
                        <option value="">-- Select Relationship --</option>
                        @foreach($relationships as $rel)
                            <option value="{{ $rel }}" {{ old('relationship') === $rel ? 'selected' : '' }}>{{ $rel }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 2. Residential Address Section --}}
                <div class="border-t border-gray-100 pt-3 space-y-3">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Residential Address</p>
                        <p class="text-xs text-muted">City and state are automatically detected as you enter your 5-digit Malaysian postcode.</p>
                    </div>

                    <div>
                        <label for="address_line_1" class="block text-sm font-semibold text-charcoal mb-1.5">Address Line 1 <span class="text-red-500">*</span></label>
                        <input id="address_line_1" name="address_line_1" type="text" x-model="addressLine1" value="{{ old('address_line_1') }}" required
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm transition-all"
                               placeholder="e.g. No. 12, Jalan Melati 3, Taman Melati">
                    </div>

                    <div>
                        <label for="address_line_2" class="block text-sm font-semibold text-charcoal mb-1.5">Address Line 2 <span class="text-xs font-normal text-muted">(Optional)</span></label>
                        <input id="address_line_2" name="address_line_2" type="text" x-model="addressLine2" value="{{ old('address_line_2') }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm transition-all"
                               placeholder="e.g. Apartment, Suite, Block">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label for="postcode" class="block text-sm font-semibold text-charcoal mb-1.5">Postcode <span class="text-red-500">*</span></label>
                            <input id="postcode" name="postcode" type="text"
                                   x-model="postcode"
                                   @input="onPostcodeInput($event)"
                                   @keyup="onPostcodeInput($event)"
                                   @change="onPostcodeInput($event)"
                                   value="{{ old('postcode') }}"
                                   required
                                   maxlength="10"
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm transition-all"
                                   placeholder="e.g. 16310">
                        </div>

                        <div>
                            <label for="city" class="block text-sm font-semibold text-charcoal mb-1.5">City <span class="text-red-500">*</span></label>
                            <input id="city" name="city" type="text"
                                   x-model="city"
                                   value="{{ old('city') }}"
                                   required
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm transition-all"
                                   placeholder="e.g. Seri Kembangan">
                        </div>

                        <div>
                            <label for="state" class="block text-sm font-semibold text-charcoal mb-1.5">State <span class="text-red-500">*</span></label>
                            
                            {{-- Local State Dropdown --}}
                            <div x-show="nationalityType === 'local'">
                                <select id="state" name="state" x-model="state"
                                        class="w-full pl-4 pr-10 py-2.5 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm transition-all">
                                    <option value="">-- Select State --</option>
                                    @foreach($malaysianStates as $st)
                                        <option value="{{ $st }}" {{ old('state') === $st ? 'selected' : '' }}>{{ $st }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- International State Input --}}
                            <div x-show="nationalityType === 'international'" x-cloak>
                                <input type="text" name="state" x-model="state" value="{{ old('state') }}"
                                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm transition-all"
                                       placeholder="State / Province">
                            </div>
                        </div>
                    </div>

                    <div x-show="nationalityType === 'international'" x-cloak>
                        <label for="country" class="block text-sm font-semibold text-charcoal mb-1.5">Country <span class="text-red-500">*</span></label>
                        <input id="country" name="country" type="text" x-model="country" value="{{ old('country') }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm transition-all"
                               placeholder="e.g. Singapore, Indonesia, United Kingdom">
                    </div>
                </div>

                {{-- 3. Account Credentials --}}
                <div class="border-t border-gray-100 pt-3">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Account Credentials</p>
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-charcoal mb-1.5">Email Address <span class="text-red-500">*</span></label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm transition-all"
                           placeholder="your@email.com">
                </div>

                <div>
                    <label for="phone" class="block text-sm font-semibold text-charcoal mb-1.5">Phone / WhatsApp <span class="text-red-500">*</span></label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required autocomplete="tel"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm transition-all"
                           placeholder="+60 12-345 6789">
                </div>

                <div x-show="parentPasswordError" x-cloak class="p-3 bg-red-50 border border-red-200 text-red-600 rounded-xl text-xs font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span x-text="parentPasswordError"></span>
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-charcoal mb-1.5">Password <span class="text-red-500">*</span></label>
                    <div class="relative" x-data="{ show: false }">
                        <input id="password" name="password" :type="show ? 'text' : 'password'" required autocomplete="new-password"
                               x-model="parentPassword"
                               @input="if (parentPasswordError) validateParentPassword()"
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
                               x-model="parentPasswordConfirmation"
                               @input="if (parentPasswordError) validateParentPassword()"
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

                <button type="button" @click="nextStep()"
                        class="w-full py-3 px-6 rounded-xl bg-primary hover:bg-primary-700 text-white text-sm font-semibold shadow-sm transition-all flex items-center justify-center gap-2 cursor-pointer mt-2">
                    Continue to Child Details →
                </button>
            </div>

            {{-- STEP 2: Child Identity & Details --}}
            <div x-show="step === 2" class="card space-y-5">
                <div class="mb-2">
                    <h2 class="font-heading font-bold text-xl text-charcoal">Child Identity</h2>
                    <p class="text-sm text-muted mt-1">Use your child's IC to set up or link their account.</p>
                </div>

                {{-- Account Type Toggle --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Registration Type</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="child_has_account" value="yes"
                                   x-model="childHasAccount"
                                   class="sr-only peer">
                            <div class="p-3.5 rounded-2xl border-2 border-gray-200 text-left peer-checked:border-primary peer-checked:bg-primary-50/60 transition-all flex items-start gap-2.5">
                                <div class="w-5 h-5 rounded-full border-2 border-gray-300 flex items-center justify-center shrink-0 mt-0.5 bg-white" :class="childHasAccount === 'yes' ? 'border-primary' : ''">
                                    <div class="w-2.5 h-2.5 rounded-full bg-primary" x-show="childHasAccount === 'yes'"></div>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-charcoal">Link Existing Student</p>
                                    <p class="text-[11px] text-muted leading-tight mt-0.5">Child already has an account</p>
                                </div>
                            </div>
                        </label>

                        <label class="relative cursor-pointer">
                            <input type="radio" name="child_has_account" value="no"
                                   x-model="childHasAccount"
                                   class="sr-only peer">
                            <div class="p-3.5 rounded-2xl border-2 border-gray-200 text-left peer-checked:border-primary peer-checked:bg-primary-50/60 transition-all flex items-start gap-2.5">
                                <div class="w-2.5 h-2.5 rounded-full border-2 border-gray-300 flex items-center justify-center shrink-0 mt-0.5 bg-white" :class="childHasAccount === 'no' ? 'border-primary' : ''">
                                    <div class="w-2.5 h-2.5 rounded-full bg-primary" x-show="childHasAccount === 'no'"></div>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-charcoal">New Child Account</p>
                                    <p class="text-[11px] text-muted leading-tight mt-0.5">Register my child for the first time</p>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Child IC Number (both modes) --}}
                <div>
                    <label for="child_ic_number" class="block text-sm font-semibold text-charcoal mb-1.5">
                        Child's MyKad / IC Number <span class="text-red-500">*</span>
                        <span class="text-xs font-normal text-muted ml-1">(Format: XXXXXX-XX-XXXX)</span>
                    </label>
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <input id="child_ic_number" name="child_ic_number" type="text"
                                   x-model="childIcRaw"
                                   @input="onChildICInput($event)"
                                   value="{{ old('child_ic_number') }}"
                                   maxlength="14"
                                   placeholder="e.g. 123456-78-9012"
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm transition-all"
                                   :class="childIcRaw.length > 0 ? (childIcValid ? 'border-blue-300' : 'border-red-300') : ''">
                        </div>
                        {{-- Verify button (only shown for existing child) --}}
                        <button type="button" @click="verifyChildIc()"
                                x-show="childHasAccount === 'yes'"
                                :disabled="!childIcValid"
                                :class="childIcValid ? 'bg-primary hover:bg-primary-700' : 'bg-gray-200 cursor-not-allowed'"
                                class="px-4 py-2.5 rounded-xl text-white text-sm font-semibold transition-colors shrink-0">
                            Verify
                        </button>
                    </div>

                    {{-- Checking --}}
                    <div x-show="childIcStatus === 'checking'" class="flex items-center gap-2 text-sm text-muted mt-2">
                        <svg class="w-4 h-4 animate-spin text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Searching for student...
                    </div>

                    {{-- Found (existing child) --}}
                    <div x-show="childIcStatus === 'found'" class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl flex items-start gap-3 mt-2">
                        <div class="w-8 h-8 bg-emerald-500 rounded-lg flex items-center justify-center shrink-0 text-white font-bold text-xs">✓</div>
                        <div>
                            <p class="text-xs font-bold text-emerald-800">Student Found</p>
                            <p class="text-sm font-bold text-charcoal mt-0.5" x-text="childFoundName"></p>
                            <p class="text-xs text-muted" x-text="[childFoundLevel, childFoundSchool].filter(Boolean).join(' · ')"></p>
                        </div>
                    </div>

                    {{-- Not Found (existing child) --}}
                    <div x-show="childIcStatus === 'not_found'" class="p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-600 font-medium mt-2">
                        No student found with this IC number. Check the IC or use <strong>New Child Account</strong> to register them.
                    </div>
                </div>

                {{-- New Child: extra fields --}}
                <div x-show="childHasAccount === 'no'" class="space-y-4 pt-1 border-t border-gray-100">
                    <p class="text-xs text-muted pt-2">Date of birth and gender will be automatically derived from the IC number above.</p>

                    <div>
                        <label for="child_name" class="block text-sm font-semibold text-charcoal mb-1.5">Child's Full Name <span class="text-red-500">*</span></label>
                        <input id="child_name" name="child_name" type="text" value="{{ old('child_name') }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm transition-all"
                               placeholder="e.g. MUHAMMAD ADAM BIN YUNUS">
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
                                class="w-full pl-4 pr-10 py-2.5 rounded-xl border border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none text-sm transition-all">
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

                    <input type="hidden" name="fee_structure_id" :value="selectedFeeStructureId">

                    {{-- Package vs Custom Selector --}}
                    <div class="space-y-2.5 pt-2 border-t border-gray-100">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Tuition Plan &amp; Package</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <template x-for="fs in availableFeeStructures()" :key="fs.id">
                                <div @click="selectFeeStructure(fs)"
                                     :class="selectedFeeStructureId == fs.id ? 'border-primary ring-2 ring-primary/20 bg-primary-50/40 shadow-xs' : 'border-gray-200 hover:border-gray-300 bg-white'"
                                     class="p-3.5 rounded-2xl border cursor-pointer transition-all flex flex-col justify-between space-y-2">
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
                                        <h4 class="font-heading font-bold text-xs text-charcoal leading-tight" x-text="fs.name"></h4>
                                        <p class="text-[10px] text-muted mt-0.5 leading-snug line-clamp-2" x-text="fs.description"></p>
                                    </div>
                                    <div class="pt-2 border-t border-gray-100 flex items-center justify-between">
                                        <span class="font-black text-primary text-sm" x-text="'RM ' + Number(fs.amount).toFixed(2)"></span>
                                        <span class="text-[10px] font-bold uppercase text-gray-400" x-text="'/' + fs.frequency"></span>
                                    </div>
                                </div>
                            </template>

                            <div @click="selectFeeStructure(null)"
                                 :class="selectedPlanType === 'custom' ? 'border-primary ring-2 ring-primary/20 bg-primary-50/40 shadow-xs' : 'border-gray-200 hover:border-gray-300 bg-white'"
                                 class="p-3.5 rounded-2xl border cursor-pointer transition-all flex flex-col justify-between space-y-2">
                                <div>
                                    <div class="flex items-center justify-between gap-1 mb-1">
                                        <span class="px-2 py-0.5 rounded-md bg-gray-100 text-gray-600 text-[10px] font-bold uppercase tracking-wider">Custom</span>
                                        <span class="text-[10px] font-semibold text-gray-400">Flexible</span>
                                    </div>
                                    <h4 class="font-heading font-bold text-xs text-charcoal leading-tight">Pay Per Subject</h4>
                                    <p class="text-[10px] text-muted mt-0.5 leading-snug">Choose individual subjects at standard rates.</p>
                                </div>
                                <div class="pt-2 border-t border-gray-100 flex items-center justify-between">
                                    <span class="font-bold text-gray-700 text-xs">Standard Rates</span>
                                    <span class="text-[10px] font-semibold text-gray-400">Unbundled</span>
                                </div>
                            </div>
                        </div>

                        {{-- Package Guidance Callout --}}
                        <template x-if="selectedPlanType === 'package' && getCurrentFeeStructure()">
                            <div class="p-3 rounded-2xl border text-xs transition-all"
                                 :class="getCurrentFeeStructure().package_type === 'fixed_bundle' ? 'bg-amber-50/80 border-amber-200 text-amber-900' : (getCurrentFeeStructure().package_type === 'pick_n' ? 'bg-indigo-50/80 border-indigo-200 text-indigo-900' : 'bg-blue-50/80 border-blue-200 text-blue-900')">
                                <template x-if="getCurrentFeeStructure().package_type === 'fixed_bundle'">
                                    <div class="flex items-start gap-2">
                                        <span class="text-base shrink-0">📦</span>
                                        <div>
                                            <p class="font-bold">Pakej Subjek Tetap (Fixed Bundle):</p>
                                            <p class="mt-0.5 text-[11px] text-amber-800 leading-relaxed">
                                                Pakej ini telah menetapkan <strong x-text="(getCurrentFeeStructure().subject_names || []).length"></strong> subjek yang telah dipilih secara automatik.
                                            </p>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="getCurrentFeeStructure().package_type === 'pick_n'">
                                    <div class="flex items-center justify-between gap-2 flex-wrap">
                                        <div class="flex items-start gap-2">
                                            <span class="text-base shrink-0">🎯</span>
                                            <div>
                                                <p class="font-bold">Pakej Pilihan Pelajar (Pick-N):</p>
                                                <p class="mt-0.5 text-[11px] text-indigo-800 leading-relaxed">
                                                    Pilih mana-mana subjek sehingga had <strong x-text="getCurrentFeeStructure().maximum_subjects || 4"></strong> subjek.
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

                    {{-- Core Subjects --}}
                    <div class="space-y-1.5 pt-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Core Subjects
                                <span class="text-primary font-normal text-[10px]" x-show="selectedPlanType === 'package'">(Included in Plan)</span>
                            </label>
                            <span class="text-[11px] text-muted" x-text="coreSubjects().filter(s => selectedSubjects.includes(s.name)).length + '/' + coreSubjects().length + ' selected'"></span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto pr-1">
                            <template x-for="subject in coreSubjects()" :key="subject.name">
                                <label class="relative cursor-pointer">
                                    <input type="checkbox" name="subjects[]" :value="subject.name"
                                           :checked="selectedSubjects.includes(subject.name)"
                                           @change="toggleSubject(subject.name)"
                                           class="sr-only peer">
                                    <div class="p-2 rounded-xl border-2 transition-all flex items-center justify-between gap-1"
                                         :class="selectedSubjects.includes(subject.name)
                                             ? 'border-primary text-primary bg-primary-50 font-semibold shadow-xs'
                                             : 'border-gray-200 text-muted hover:border-gray-300'">
                                        <span class="text-xs truncate font-medium" x-text="subject.display_name || (subject.form_year ? `${subject.name} - ${subject.form_year}` : subject.name)"></span>
                                        <span class="text-[9px] px-1 py-0.5 rounded font-bold shrink-0"
                                              :class="selectedPlanType === 'package' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600'"
                                              x-text="selectedPlanType === 'package' ? 'Included' : ('RM ' + Number(subject.monthly_fee).toFixed(0))">
                                        </span>
                                    </div>
                                </label>
                            </template>
                        </div>
                    </div>

                    {{-- Electives --}}
                    <template x-if="electiveSubjects().length > 0">
                        <div class="space-y-1.5 pt-2 border-t border-gray-100">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Electives &amp; Add-ons</label>
                                <span x-show="selectedPlanType === 'package' && bundleDiscountPct > 0"
                                      class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold"
                                      x-text="bundleDiscountPct + '% Bundle Discount'"></span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto pr-1">
                                <template x-for="subject in electiveSubjects()" :key="subject.name">
                                    <label class="relative cursor-pointer">
                                        <input type="checkbox" name="subjects[]" :value="subject.name"
                                               :checked="selectedSubjects.includes(subject.name)"
                                               @change="toggleSubject(subject.name)"
                                               class="sr-only peer">
                                        <div class="p-2 rounded-xl border-2 transition-all flex items-center justify-between gap-1"
                                             :class="selectedSubjects.includes(subject.name)
                                                 ? 'border-primary text-primary bg-primary-50 font-semibold shadow-xs'
                                                 : 'border-gray-200 text-muted hover:border-gray-300'">
                                            <span class="text-xs truncate font-medium" x-text="subject.display_name || (subject.form_year ? `${subject.name} - ${subject.form_year}` : subject.name)"></span>
                                            <span class="text-[9px] px-1 py-0.5 rounded font-bold shrink-0 bg-gray-100 text-gray-700"
                                                  x-text="'RM ' + Number(selectedPlanType === 'package' ? subject.monthly_fee * (1 - bundleDiscountPct/100) : subject.monthly_fee).toFixed(0)">
                                            </span>
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Dynamic Tuition Summary Box --}}
                    <div class="bg-gray-50 border border-gray-200/80 rounded-2xl p-3.5 space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Plan:</span>
                            <span class="font-bold text-gray-900" x-text="calculatedPricing().packageName"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Subjects Enrolled:</span>
                            <span class="font-semibold text-gray-800" x-text="enrolledCount() + ' Subject(s)'"></span>
                        </div>
                        <template x-if="calculatedPricing().savings > 0">
                            <div class="flex items-center justify-between text-emerald-700 font-semibold bg-emerald-50 px-2 py-1 rounded-lg border border-emerald-200/60">
                                <span>Plan Savings:</span>
                                <span x-text="'- RM ' + Number(calculatedPricing().savings).toFixed(2)"></span>
                            </div>
                        </template>
                        <div class="pt-1.5 border-t border-gray-200 flex items-center justify-between">
                            <span class="font-bold text-gray-900">Total Monthly Tuition</span>
                            <span class="text-base font-black text-primary font-heading" x-text="'RM ' + Number(calculatedPricing().finalTotal).toFixed(2)"></span>
                        </div>
                        <p class="text-[10px] text-gray-400">Formal invoice with <span x-text="invoiceDueDays"></span>-day payment window issued upon signup. No upfront payment required.</p>
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" @click="prevStep()" class="flex-1 py-3 px-4 rounded-xl border border-gray-200 text-sm font-semibold text-muted hover:bg-gray-50 transition-colors flex items-center justify-center">← Back</button>
                    <button type="button" @click="nextStep()" class="flex-1 py-3 px-4 rounded-xl bg-primary hover:bg-primary-700 text-white text-sm font-semibold shadow-sm transition-colors flex items-center justify-center">Review & Submit →</button>
                </div>
            </div>

            {{-- STEP 3: Confirm & Submit --}}
            <div x-show="step === 3" class="card space-y-4">
                <div class="mb-2">
                    <h2 class="font-heading font-bold text-xl text-charcoal">Confirm & Submit</h2>
                    <p class="text-sm text-muted mt-1">Ready to create your account? Click below to complete registration.</p>
                </div>

                <div class="text-xs text-muted p-3 bg-blue-50 border border-blue-100 rounded-xl flex gap-2">
                    <svg class="w-4 h-4 text-blue-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Identity and address records are safely encrypted in compliance with PDPA guidelines.
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" @click="prevStep()" class="flex-1 py-3 px-4 rounded-xl border border-gray-200 text-sm font-semibold text-muted hover:bg-gray-50 transition-colors flex items-center justify-center">← Back</button>
                    <button type="submit" class="flex-1 py-3 px-4 rounded-xl bg-primary hover:bg-primary-700 text-white text-sm font-semibold shadow-sm transition-colors flex items-center justify-center">Create Parent Account ✓</button>
                </div>
            </div>
        </form>

        <p class="text-center text-sm text-muted mt-6">
            Already have an account?
            <a href="{{ route('login') }}" class="text-primary font-semibold hover:underline">Sign in</a>
        </p>
        <p class="text-center text-sm text-muted mt-1">
            Registering as a student?
            <a href="{{ route('register.student') }}" class="text-primary font-semibold hover:underline">Student Registration</a>
        </p>
    </div>
</body>
</html>
