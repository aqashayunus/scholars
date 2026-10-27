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
          step: {{ $errors->hasAny(['resume', 'certificate', 'transcript', 'additional_document']) ? 4 : ($errors->hasAny(['qualification', 'experience_years']) ? 3 : ($errors->hasAny(['preferred_subjects', 'teaching_level']) ? 2 : 1)) }},
          nextStep() { if (this.step < 4) this.step++; },
          prevStep() { if (this.step > 1) this.step--; },
      }">

    <div class="w-full max-w-xl mx-auto">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-flex flex-col items-center gap-2 group">
                <img src="{{ asset('images/logo-vertical.png') }}" alt="Olympian Scholars" class="h-24 w-auto object-contain transition-transform group-hover:scale-105">
            </a>
        </div>

        {{-- Header --}}
        <div class="text-center mb-6">
            <h1 class="font-heading font-bold text-2xl text-charcoal">Teach with Olympian Scholars</h1>
            <p class="text-sm text-muted mt-2">Submit your application and our team will be in touch within 3–5 business days.</p>
        </div>

        {{-- Progress --}}
        <div class="mb-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-muted">Step <span x-text="step"></span> of 4</span>
                <span class="text-xs text-muted"
                      x-text="['Personal Information', 'Teaching Information', 'Experience & Qualifications', 'Supporting Documents & Submit'][step-1]"></span>
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

        <form method="POST" action="{{ route('register.teacher.submit') }}" enctype="multipart/form-data" class="space-y-5">
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
                    <label for="ic_number" class="block text-sm font-semibold text-charcoal mb-1.5">IC / Passport Number <span class="text-red-500">*</span></label>
                    <input id="ic_number" name="ic_number" type="text" value="{{ old('ic_number') }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none text-sm transition-all"
                           placeholder="e.g. 950812-14-5678 or A12345678">
                    <p class="text-[11px] text-muted mt-1">Used for educator verification and initial account security.</p>
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
                            class="w-full pl-4 pr-10 py-2.5 rounded-xl border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none text-sm transition-all">
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

            {{-- STEP 4: Supporting Documents & Submit --}}
            <div x-show="step === 4" class="card space-y-4">
                <div>
                    <h2 class="font-heading font-bold text-xl text-charcoal">Supporting Documents &amp; Credentials</h2>
                    <p class="text-xs text-muted mt-1">Please attach your credentials and documents to verify your educator profile.</p>
                </div>

                {{-- Document 1: Resume / Curriculum Vitae (CV) (Required) --}}
                <div x-data="{
                    fileName: '',
                    fileSize: '',
                    hasFile: false,
                    onFileChange(e) {
                        const file = e.target.files[0];
                        if (file) {
                            this.fileName = file.name;
                            this.fileSize = file.size > 1048576 ? (file.size / 1048576).toFixed(2) + ' MB' : (file.size / 1024).toFixed(1) + ' KB';
                            this.hasFile = true;
                        } else {
                            this.clear();
                        }
                    },
                    clear() {
                        this.$refs.fileInput.value = '';
                        this.fileName = '';
                        this.fileSize = '';
                        this.hasFile = false;
                    }
                }" class="p-3.5 rounded-xl border border-gray-200 bg-gray-50/50 hover:bg-white transition-all">
                    <div class="mb-1.5">
                        <label class="block text-sm font-semibold text-charcoal">
                            1. Resume / Curriculum Vitae (CV) <span class="text-rose-500">*</span>
                        </label>
                        <p class="text-xs text-muted">Upload your updated resume or detailed CV.</p>
                    </div>

                    <div class="mt-2">
                        <label class="cursor-pointer block">
                            <input type="file"
                                   x-ref="fileInput"
                                   name="resume"
                                   required
                                   accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                                   @change="onFileChange($event)"
                                   class="sr-only">

                            <div x-show="!hasFile"
                                 class="border-2 border-dashed border-gray-200 hover:border-emerald-500 rounded-xl p-3 text-center bg-white transition-all group">
                                <div class="flex items-center justify-center gap-2 text-xs text-gray-600 group-hover:text-emerald-700 font-medium">
                                    <svg class="w-4 h-4 text-gray-400 group-hover:text-emerald-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    <span>Upload Resume or CV</span>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-0.5">Formats: PDF, DOC, DOCX (Max 10MB)</p>
                            </div>

                            <div x-show="hasFile"
                                 style="display: none;"
                                 class="flex items-center justify-between gap-2 p-2.5 rounded-xl bg-gray-50 border border-gray-200 text-xs">
                                <div class="flex items-center gap-2 min-w-0">
                                    <svg class="w-5 h-5 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <div class="truncate">
                                        <p class="font-medium text-gray-900 truncate" x-text="fileName"></p>
                                        <p class="text-[10px] text-gray-500" x-text="fileSize"></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="text-emerald-700 text-[11px] font-medium flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Attached
                                    </span>
                                    <button type="button" @click.stop="clear()" class="text-[11px] text-gray-400 hover:text-rose-600 p-1 font-semibold" title="Remove">✕</button>
                                </div>
                            </div>
                        </label>
                        @error('resume')
                            <p class="text-xs text-rose-600 font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Document 2: Highest Academic Qualification Certificate (Optional) --}}
                <div x-data="{
                    fileName: '',
                    fileSize: '',
                    hasFile: false,
                    onFileChange(e) {
                        const file = e.target.files[0];
                        if (file) {
                            this.fileName = file.name;
                            this.fileSize = file.size > 1048576 ? (file.size / 1048576).toFixed(2) + ' MB' : (file.size / 1024).toFixed(1) + ' KB';
                            this.hasFile = true;
                        } else {
                            this.clear();
                        }
                    },
                    clear() {
                        this.$refs.fileInput.value = '';
                        this.fileName = '';
                        this.fileSize = '';
                        this.hasFile = false;
                    }
                }" class="p-3.5 rounded-xl border border-gray-200 bg-gray-50/50 hover:bg-white transition-all">
                    <div class="mb-1.5">
                        <label class="block text-sm font-semibold text-charcoal">
                            2. Highest Academic Qualification Certificate
                        </label>
                        <p class="text-xs text-muted">Degree, Diploma, Master's, or highest completed qualification certificate.</p>
                    </div>

                    <div class="mt-2">
                        <label class="cursor-pointer block">
                            <input type="file"
                                   x-ref="fileInput"
                                   name="certificate"
                                   accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                                   @change="onFileChange($event)"
                                   class="sr-only">

                            <div x-show="!hasFile"
                                 class="border-2 border-dashed border-gray-200 hover:border-emerald-500 rounded-xl p-3 text-center bg-white transition-all group">
                                <div class="flex items-center justify-center gap-2 text-xs text-gray-600 group-hover:text-emerald-700 font-medium">
                                    <svg class="w-4 h-4 text-gray-400 group-hover:text-emerald-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    <span>Upload Qualification Certificate</span>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-0.5">Formats: PDF, JPG, PNG (Max 10MB)</p>
                            </div>

                            <div x-show="hasFile"
                                 style="display: none;"
                                 class="flex items-center justify-between gap-2 p-2.5 rounded-xl bg-gray-50 border border-gray-200 text-xs">
                                <div class="flex items-center gap-2 min-w-0">
                                    <svg class="w-5 h-5 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <div class="truncate">
                                        <p class="font-medium text-gray-900 truncate" x-text="fileName"></p>
                                        <p class="text-[10px] text-gray-500" x-text="fileSize"></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="text-emerald-700 text-[11px] font-medium flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Attached
                                    </span>
                                    <button type="button" @click.stop="clear()" class="text-[11px] text-gray-400 hover:text-rose-600 p-1 font-semibold" title="Remove">✕</button>
                                </div>
                            </div>
                        </label>
                        @error('certificate')
                            <p class="text-xs text-rose-600 font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Document 3: Academic Transcript (Optional) --}}
                <div x-data="{
                    fileName: '',
                    fileSize: '',
                    hasFile: false,
                    onFileChange(e) {
                        const file = e.target.files[0];
                        if (file) {
                            this.fileName = file.name;
                            this.fileSize = file.size > 1048576 ? (file.size / 1048576).toFixed(2) + ' MB' : (file.size / 1024).toFixed(1) + ' KB';
                            this.hasFile = true;
                        } else {
                            this.clear();
                        }
                    },
                    clear() {
                        this.$refs.fileInput.value = '';
                        this.fileName = '';
                        this.fileSize = '';
                        this.hasFile = false;
                    }
                }" class="p-3.5 rounded-xl border border-gray-200 bg-gray-50/50 hover:bg-white transition-all">
                    <div class="mb-1.5">
                        <label class="block text-sm font-semibold text-charcoal">
                            3. Academic Transcript
                        </label>
                        <p class="text-xs text-muted">Official university, college, or examination results transcript.</p>
                    </div>

                    <div class="mt-2">
                        <label class="cursor-pointer block">
                            <input type="file"
                                   x-ref="fileInput"
                                   name="transcript"
                                   accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                                   @change="onFileChange($event)"
                                   class="sr-only">

                            <div x-show="!hasFile"
                                 class="border-2 border-dashed border-gray-200 hover:border-emerald-500 rounded-xl p-3 text-center bg-white transition-all group">
                                <div class="flex items-center justify-center gap-2 text-xs text-gray-600 group-hover:text-emerald-700 font-medium">
                                    <svg class="w-4 h-4 text-gray-400 group-hover:text-emerald-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    <span>Upload Academic Transcript</span>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-0.5">Formats: PDF, JPG, PNG (Max 10MB)</p>
                            </div>

                            <div x-show="hasFile"
                                 style="display: none;"
                                 class="flex items-center justify-between gap-2 p-2.5 rounded-xl bg-gray-50 border border-gray-200 text-xs">
                                <div class="flex items-center gap-2 min-w-0">
                                    <svg class="w-5 h-5 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <div class="truncate">
                                        <p class="font-medium text-gray-900 truncate" x-text="fileName"></p>
                                        <p class="text-[10px] text-gray-500" x-text="fileSize"></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="text-emerald-700 text-[11px] font-medium flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Attached
                                    </span>
                                    <button type="button" @click.stop="clear()" class="text-[11px] text-gray-400 hover:text-rose-600 p-1 font-semibold" title="Remove">✕</button>
                                </div>
                            </div>
                        </label>
                        @error('transcript')
                            <p class="text-xs text-rose-600 font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Document 4: Additional Document (Optional) --}}
                <div x-data="{
                    fileName: '',
                    fileSize: '',
                    hasFile: false,
                    onFileChange(e) {
                        const file = e.target.files[0];
                        if (file) {
                            this.fileName = file.name;
                            this.fileSize = file.size > 1048576 ? (file.size / 1048576).toFixed(2) + ' MB' : (file.size / 1024).toFixed(1) + ' KB';
                            this.hasFile = true;
                        } else {
                            this.clear();
                        }
                    },
                    clear() {
                        this.$refs.fileInput.value = '';
                        this.fileName = '';
                        this.fileSize = '';
                        this.hasFile = false;
                    }
                }" class="p-3.5 rounded-xl border border-gray-200 bg-gray-50/50 hover:bg-white transition-all">
                    <div class="mb-1.5">
                        <label class="block text-sm font-semibold text-charcoal">
                            4. Additional Document
                        </label>
                        <p class="text-xs text-muted">Teaching certifications, licenses, recommendation letters, portfolio, or other supporting files.</p>
                    </div>

                    <div class="mt-2">
                        <label class="cursor-pointer block">
                            <input type="file"
                                   x-ref="fileInput"
                                   name="additional_document"
                                   accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/jpeg,image/png"
                                   @change="onFileChange($event)"
                                   class="sr-only">

                            <div x-show="!hasFile"
                                 class="border-2 border-dashed border-gray-200 hover:border-emerald-500 rounded-xl p-3 text-center bg-white transition-all group">
                                <div class="flex items-center justify-center gap-2 text-xs text-gray-600 group-hover:text-emerald-700 font-medium">
                                    <svg class="w-4 h-4 text-gray-400 group-hover:text-emerald-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    <span>Upload Additional Supporting Document</span>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-0.5">Formats: PDF, DOC, DOCX, JPG, PNG (Max 10MB)</p>
                            </div>

                            <div x-show="hasFile"
                                 style="display: none;"
                                 class="flex items-center justify-between gap-2 p-2.5 rounded-xl bg-gray-50 border border-gray-200 text-xs">
                                <div class="flex items-center gap-2 min-w-0">
                                    <svg class="w-5 h-5 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <div class="truncate">
                                        <p class="font-medium text-gray-900 truncate" x-text="fileName"></p>
                                        <p class="text-[10px] text-gray-500" x-text="fileSize"></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="text-emerald-700 text-[11px] font-medium flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Attached
                                    </span>
                                    <button type="button" @click.stop="clear()" class="text-[11px] text-gray-400 hover:text-rose-600 p-1 font-semibold" title="Remove">✕</button>
                                </div>
                            </div>
                        </label>
                        @error('additional_document')
                            <p class="text-xs text-rose-600 font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Verification & Review Notice --}}
                <p class="text-xs text-muted text-center mb-0">
                    Applications are reviewed before approval. Your account will be created once verified.
                </p>

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
