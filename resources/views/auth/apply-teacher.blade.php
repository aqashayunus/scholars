<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Apply to Teach | Olympian Scholars</title>
    <meta name="description" content="Apply to become a teacher with Olympian Scholars and make a difference in students' lives.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface min-h-screen py-10 px-4"
      x-data="{
          step: 1,
          nextStep() { if (this.step < 4) this.step++; },
          prevStep() { if (this.step > 1) this.step--; },
      }">

    <div class="w-full max-w-xl mx-auto">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-flex flex-col items-center gap-2 group">
                <img src="{{ asset('images/logo.png') }}" alt="Olympian Scholars" class="h-16 w-auto object-contain transition-transform group-hover:scale-105">
            </a>
        </div>

        {{-- Header --}}
        <div class="text-center mb-6">
            <div class="inline-flex items-center gap-2 bg-emerald-50 text-emerald-700 text-xs font-semibold px-3 py-1.5 rounded-full mb-3">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Apply to Teach
            </div>
            <h1 class="font-heading font-bold text-2xl text-charcoal">Teach with Olympian Scholars</h1>
            <p class="text-sm text-muted mt-2">Submit your application and our team will be in touch within 3–5 business days.</p>
        </div>

        {{-- Progress --}}
        <div class="mb-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-muted">Step <span x-text="step"></span> of 4</span>
                <span class="text-xs text-muted"
                      x-text="['Personal Information', 'Teaching Information', 'Experience', 'Confirm & Submit'][step-1]"></span>
            </div>
            <div class="h-2 bg-emerald-100 rounded-full">
                <div class="h-2 bg-emerald-500 rounded-full transition-all duration-500"
                     :style="'width:' + (step / 4 * 100) + '%'"></div>
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

        <form method="POST" action="{{ route('register.teacher.submit') }}" class="space-y-5">
            @csrf

            {{-- STEP 1: Personal Information --}}
            <div x-show="step === 1" class="card space-y-4">
                <h2 class="font-heading font-bold text-xl text-charcoal">Personal Information</h2>

                <div>
                    <label for="full_name" class="block text-sm font-semibold text-charcoal mb-1.5">Full Name <span class="text-red-500">*</span></label>
                    <input id="full_name" name="full_name" type="text" value="{{ old('full_name') }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none text-sm transition-all"
                           placeholder="e.g. Dr. Ahmad bin Zulkifli">
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-charcoal mb-1.5">Email Address <span class="text-red-500">*</span></label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none text-sm transition-all"
                           placeholder="your@email.com">
                </div>

                <div>
                    <label for="phone" class="block text-sm font-semibold text-charcoal mb-1.5">Phone / WhatsApp <span class="text-red-500">*</span></label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none text-sm transition-all"
                           placeholder="+60 12-345 6789">
                </div>

                <div>
                    <label for="institution" class="block text-sm font-semibold text-charcoal mb-1.5">School / Institution (if applicable)</label>
                    <input id="institution" name="institution" type="text" value="{{ old('institution') }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none text-sm transition-all"
                           placeholder="e.g. SMK Taman Melawati">
                </div>

                <button type="button" @click="nextStep()"
                        class="w-full py-3 px-6 rounded-xl bg-primary hover:bg-primary-700 text-white text-sm font-semibold shadow-sm transition-all flex items-center justify-center gap-2 cursor-pointer mt-2">
                    Continue →
                </button>
            </div>

            {{-- STEP 2: Teaching Information --}}
            <div x-show="step === 2" class="card space-y-4">
                <h2 class="font-heading font-bold text-xl text-charcoal">Teaching Information</h2>

                <div>
                    <label for="preferred_subjects" class="block text-sm font-semibold text-charcoal mb-1.5">Subjects You Wish to Teach <span class="text-red-500">*</span></label>
                    <textarea id="preferred_subjects" name="preferred_subjects" rows="3" required
                              class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none text-sm transition-all resize-none"
                              placeholder="e.g. Mathematics, Physics, Additional Mathematics">{{ old('preferred_subjects') }}</textarea>
                    <p class="text-xs text-muted mt-1">List all subjects you are qualified to teach.</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-charcoal mb-2">Preferred Teaching Level</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach(['Primary School (Year 4–6)', 'Lower Secondary (Form 1–3)', 'Upper Secondary (Form 4–5)'] as $lvl)
                        <label class="relative cursor-pointer col-span-1">
                            <input type="checkbox" name="teaching_level[]" value="{{ $lvl }}"
                                   {{ in_array($lvl, old('teaching_level', [])) ? 'checked' : '' }}
                                   class="sr-only peer">
                            <span class="block text-center py-2.5 px-2 rounded-xl border-2 border-gray-200 text-xs font-semibold text-muted peer-checked:border-primary peer-checked:text-primary peer-checked:bg-primary-50 transition-all leading-snug">
                                {{ $lvl }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" @click="prevStep()" class="flex-1 py-3 px-4 rounded-xl border border-gray-200 text-sm font-semibold text-muted hover:bg-gray-50 transition-colors flex items-center justify-center">← Back</button>
                    <button type="button" @click="nextStep()" class="flex-1 py-3 px-4 rounded-xl bg-primary hover:bg-primary-700 text-white text-sm font-semibold shadow-sm transition-colors flex items-center justify-center">Continue →</button>
                </div>
            </div>

            {{-- STEP 3: Experience & Qualifications --}}
            <div x-show="step === 3" class="card space-y-4">
                <h2 class="font-heading font-bold text-xl text-charcoal">Experience & Qualifications</h2>

                <div>
                    <label for="qualification" class="block text-sm font-semibold text-charcoal mb-1.5">Highest Qualification <span class="text-red-500">*</span></label>
                    <input id="qualification" name="qualification" type="text" value="{{ old('qualification') }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none text-sm transition-all"
                           placeholder="e.g. Bachelor of Science in Mathematics, UPM">
                </div>

                <div>
                    <label for="experience_years" class="block text-sm font-semibold text-charcoal mb-1.5">Years of Teaching Experience <span class="text-red-500">*</span></label>
                    <select id="experience_years" name="experience_years" required
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none text-sm transition-all">
                        <option value="">-- Select --</option>
                        <option value="0" {{ old('experience_years') == '0' ? 'selected' : '' }}>Less than 1 year (Fresh Graduate)</option>
                        <option value="1" {{ old('experience_years') == '1' ? 'selected' : '' }}>1 year</option>
                        <option value="2" {{ old('experience_years') == '2' ? 'selected' : '' }}>2 years</option>
                        <option value="3" {{ old('experience_years') == '3' ? 'selected' : '' }}>3 years</option>
                        <option value="5" {{ old('experience_years') == '5' ? 'selected' : '' }}>5 years</option>
                        <option value="8" {{ old('experience_years') == '8' ? 'selected' : '' }}>5–10 years</option>
                        <option value="10" {{ old('experience_years') == '10' ? 'selected' : '' }}>10+ years</option>
                    </select>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" @click="prevStep()" class="flex-1 py-3 px-4 rounded-xl border border-gray-200 text-sm font-semibold text-muted hover:bg-gray-50 transition-colors flex items-center justify-center">← Back</button>
                    <button type="button" @click="nextStep()" class="flex-1 py-3 px-4 rounded-xl bg-primary hover:bg-primary-700 text-white text-sm font-semibold shadow-sm transition-colors flex items-center justify-center">Continue →</button>
                </div>
            </div>

            {{-- STEP 4: Confirm & Submit --}}
            <div x-show="step === 4" class="card space-y-4">
                <h2 class="font-heading font-bold text-xl text-charcoal">Confirm & Submit</h2>
                <p class="text-sm text-muted">Ready to submit your application? Click below to send it to our team.</p>

                <div class="text-xs text-muted p-3 bg-amber-50 border border-amber-100 rounded-xl flex gap-2">
                    <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Your application will be reviewed by the Olympian Scholars team. We will contact you at your email address once it has been processed. <strong>No login account will be created until your application is approved.</strong>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" @click="prevStep()" class="flex-1 py-3 px-4 rounded-xl border border-gray-200 text-sm font-semibold text-muted hover:bg-gray-50 transition-colors flex items-center justify-center">← Back</button>
                    <button type="submit" class="flex-1 py-3 px-4 rounded-xl bg-primary hover:bg-primary-700 text-white text-sm font-semibold shadow-sm transition-colors flex items-center justify-center">Submit Application ✓</button>
                </div>
            </div>
        </form>

        <p class="text-center text-sm text-muted mt-6">
            Already have a teacher account?
            <a href="{{ route('login') }}" class="text-primary font-semibold hover:underline">Sign in</a>
        </p>
    </div>
</body>
</html>
