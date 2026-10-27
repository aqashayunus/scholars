<x-teacher-layout>
    <x-slot name="title">My Profile &amp; Documents</x-slot>

    @php
        $user = $user ?? auth()->user();
        $avatarUrl = $user->avatar ? asset('storage/' . $user->avatar) : null;
        $activeTab = $activeTab ?? request('tab', session('active_tab', 'documents'));
    @endphp

    <div x-data="{ 
        tab: '{{ $activeTab }}',
        avatarPreview: '{{ $avatarUrl }}',
        removeAvatar: false,
        uploadModalOpen: false,
        activeDocType: '',
        activeDocTitle: '',
        activeDocAccept: '',
        activeDocMimes: '',

        openUploadModal(type, title, accept, mimes) {
            this.activeDocType = type;
            this.activeDocTitle = title;
            this.activeDocAccept = accept;
            this.activeDocMimes = mimes;
            this.uploadModalOpen = true;
        },
        closeUploadModal() {
            this.uploadModalOpen = false;
        },
        previewAvatar(event) {
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
            if (this.$refs.avatarInput) {
                this.$refs.avatarInput.value = '';
            }
        }
    }" class="space-y-6 max-w-6xl mx-auto pb-12">

        {{-- ── TOP NOTIFICATIONS / FLASH ALERTS ── --}}
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-xs sm:text-sm font-semibold flex items-center justify-between shadow-xs transition-all">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 cursor-pointer p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-800 text-xs sm:text-sm font-medium shadow-xs">
                <div class="flex items-center gap-2 font-bold mb-1.5 text-rose-900">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Please review and fix the following:</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 text-xs text-rose-700 pl-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ── 1. EDUCATOR HERO PROFILE BANNER ── --}}
        <div class="relative bg-gradient-to-r from-primary-950 via-primary-900 to-blue-900 rounded-3xl p-6 sm:p-8 text-white shadow-card overflow-hidden">
            {{-- Decorative Background Watermark --}}
            <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none select-none">
                <svg class="w-72 h-72 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2L1 21h22L12 2zm0 3.99L19.53 19H4.47L12 5.99zM11 16h2v2h-2zm0-6h2v4h-2z"/>
                </svg>
            </div>

            <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div class="flex flex-col sm:flex-row items-center sm:items-start md:items-center gap-5 text-center sm:text-left">
                    {{-- Avatar --}}
                    <div class="relative group shrink-0">
                        <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-3xl border-4 border-white/20 overflow-hidden bg-primary-800 shadow-xl flex items-center justify-center">
                            @if($avatarUrl)
                                <img src="{{ $avatarUrl }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-2xl sm:text-3xl font-black text-white uppercase tracking-wider">
                                    {{ strtoupper(substr($user->name ?? 'T', 0, 2)) }}
                                </span>
                            @endif
                        </div>
                        <button type="button" @click="tab = 'profile'" title="Change profile picture"
                                class="absolute -bottom-1 -right-1 p-2 bg-white text-primary-950 rounded-2xl shadow-md hover:scale-105 transition-transform cursor-pointer border border-gray-100">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Info --}}
                    <div>
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5 mb-1.5">
                            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white">{{ $user->name }}</h1>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                {{ ucfirst($teacher->application_status ?? 'Active') }} Teacher
                            </span>
                        </div>
                        <p class="text-xs sm:text-sm text-primary-200 font-medium">
                            {{ $teacher->qualification ?? 'Certified Educator' }} · <span class="text-white font-semibold">{{ $teacher->specialization ?? 'General Subjects' }}</span>
                        </p>
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-4 mt-3 text-xs text-primary-300">
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                {{ $user->email }}
                            </span>
                            @if($user->phone)
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    {{ $user->phone }}
                                </span>
                            @endif
                            <span class="inline-flex items-center gap-1 text-amber-300 font-semibold">
                                ★ {{ number_format($teacher->rating ?? 5.0, 1) }} Rating
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Quick Credential Summary Pills --}}
                <div class="w-full md:w-auto flex flex-row md:flex-col gap-2.5 justify-center md:items-end">
                    <div class="flex-1 md:flex-initial bg-white/10 backdrop-blur-md rounded-2xl px-4 py-2.5 border border-white/15 text-center md:text-right">
                        <span class="text-[10px] uppercase font-bold text-primary-200 tracking-wider block">Documents Status</span>
                        <div class="flex items-center justify-center md:justify-end gap-1.5 mt-0.5">
                            <span class="text-sm font-black text-white">{{ $uploadedCount }} of 4</span>
                            <span class="text-xs {{ $uploadedCount >= 3 ? 'text-emerald-300' : 'text-amber-300' }}">({{ $uploadedCount >= 3 ? 'Complete' : 'In Progress' }})</span>
                        </div>
                    </div>
                    <div class="flex-1 md:flex-initial bg-white/10 backdrop-blur-md rounded-2xl px-4 py-2.5 border border-white/15 text-center md:text-right">
                        <span class="text-[10px] uppercase font-bold text-primary-200 tracking-wider block">Experience</span>
                        <span class="text-sm font-black text-white">{{ $teacher->experience_years ?? 3 }} Years</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── 2. TABBED NAVIGATION MENU ── --}}
        <div class="flex flex-wrap items-center gap-2 border-b border-gray-200/80 pb-2">
            <button type="button" @click="tab = 'documents'"
                    :class="tab === 'documents' ? 'bg-primary-950 text-white font-bold shadow-sm' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 font-medium'"
                    class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm flex items-center gap-2 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Credentials &amp; Documents</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black"
                      :class="tab === 'documents' ? 'bg-primary-800 text-white' : 'bg-gray-100 text-gray-700'">
                    {{ $uploadedCount }}/4
                </span>
            </button>

            <button type="button" @click="tab = 'profile'"
                    :class="tab === 'profile' ? 'bg-primary-950 text-white font-bold shadow-sm' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 font-medium'"
                    class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm flex items-center gap-2 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span>Personal Information</span>
            </button>

            <button type="button" @click="tab = 'professional'"
                    :class="tab === 'professional' ? 'bg-primary-950 text-white font-bold shadow-sm' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 font-medium'"
                    class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm flex items-center gap-2 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                <span>Teaching &amp; Curriculum</span>
            </button>

            <button type="button" @click="tab = 'security'"
                    :class="tab === 'security' ? 'bg-primary-950 text-white font-bold shadow-sm' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 font-medium'"
                    class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm flex items-center gap-2 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <span>Password &amp; Security</span>
            </button>
        </div>

        {{-- ─────────────────────────────────────────────────────────── --}}
        {{-- TAB 1: CREDENTIALS & DOCUMENTS MANAGEMENT (CORE REQUIREMENT) --}}
        {{-- ─────────────────────────────────────────────────────────── --}}
        <div x-show="tab === 'documents'" x-cloak class="space-y-6">

            {{-- Informational Banner --}}
            <div class="bg-blue-50/70 border border-blue-200/80 rounded-3xl p-5 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-2xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-sm mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-gray-900">Official Teaching Credentials &amp; Documentation</h2>
                        <p class="text-xs text-gray-600 mt-1 leading-relaxed max-w-2xl">
                            Keep your professional resumes, academic qualification certificates, and transcripts up-to-date.
                            Uploaded documents are reviewed by Aster Edu administrators and verified for Malaysian MOE / Cambridge syllabus educator standards.
                        </p>
                    </div>
                </div>
                <div class="shrink-0 flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-blue-200 text-xs font-bold text-blue-900 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Secure Server Storage
                    </span>
                </div>
            </div>

            {{-- 4 Document Cards Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                @foreach($documents as $key => $doc)
                    @php
                        $isUploaded = !empty($doc['path']);
                        $isOnDisk = $doc['exists_on_disk'];
                    @endphp
                    <div class="bg-white rounded-3xl p-5 sm:p-6 border {{ $isUploaded ? 'border-gray-200/90' : 'border-dashed border-gray-300' }} shadow-card hover:shadow-card-hover transition-all flex flex-col justify-between">
                        <div>
                            {{-- Header --}}
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-2xl {{ $isUploaded ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-500 border border-gray-200' }} flex items-center justify-center shrink-0">
                                        @if($doc['type'] === 'resume')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        @elseif($doc['type'] === 'certificate')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                                        @elseif($doc['type'] === 'transcript')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h3 class="text-sm font-bold text-gray-900 leading-snug">{{ $doc['title'] }}</h3>
                                            @if($doc['required'])
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-rose-50 text-rose-700 border border-rose-200">Required</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-medium text-gray-500 bg-gray-100">Optional</span>
                                            @endif
                                        </div>
                                        <p class="text-[11px] text-gray-500 mt-0.5 line-clamp-2 leading-relaxed">{{ $doc['description'] }}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- File Status & Details Box --}}
                            @if($isUploaded)
                                <div class="bg-gray-50/80 rounded-2xl p-3.5 border border-gray-200/70 mb-4 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span class="w-8 h-8 rounded-xl bg-white text-gray-600 border border-gray-200 flex items-center justify-center font-black text-[10px] uppercase shrink-0">
                                            {{ $doc['extension'] ?? 'FILE' }}
                                        </span>
                                        <div class="min-w-0">
                                            <p class="text-xs font-semibold text-gray-900 truncate" title="{{ $doc['filename'] }}">
                                                {{ $doc['filename'] }}
                                            </p>
                                            <div class="flex items-center gap-2 mt-0.5 text-[10px] text-gray-500">
                                                @if($doc['filesize'])
                                                    <span>{{ $doc['filesize'] }}</span>
                                                    <span>•</span>
                                                @endif
                                                <span class="text-emerald-700 font-bold inline-flex items-center gap-1">
                                                    <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                    Uploaded &amp; Available
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="bg-amber-50/50 rounded-2xl p-3.5 border border-amber-200/70 mb-4 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2 text-xs text-amber-800">
                                        <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        <span class="font-medium">No document uploaded yet</span>
                                    </div>
                                    <span class="text-[10px] text-gray-400 font-mono">{{ $doc['mimes_label'] }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- Action Buttons --}}
                        <div class="pt-3 border-t border-gray-100 flex flex-wrap items-center justify-between gap-2">
                            @if($isUploaded)
                                <div class="flex items-center gap-1.5">
                                    {{-- Preview in Browser --}}
                                    <a href="{{ route('teacher.profile.documents.view', ['type' => $doc['type']]) }}"
                                       target="_blank" rel="noopener noreferrer"
                                       class="px-3 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 text-xs font-semibold inline-flex items-center gap-1.5 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span>Preview</span>
                                    </a>

                                    {{-- Download File --}}
                                    <a href="{{ route('teacher.profile.documents.download', ['type' => $doc['type']]) }}"
                                       class="px-3 py-1.5 rounded-xl bg-gray-50 hover:bg-gray-100 text-gray-700 border border-gray-200 text-xs font-medium inline-flex items-center gap-1.5 transition">
                                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        <span>Download</span>
                                    </a>
                                </div>

                                <div class="flex items-center gap-1.5">
                                    {{-- Replace File --}}
                                    <button type="button"
                                            @click="openUploadModal('{{ $doc['type'] }}', '{{ e($doc['title']) }}', '{{ $doc['accept'] }}', '{{ $doc['mimes_label'] }}')"
                                            class="px-3 py-1.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-semibold inline-flex items-center gap-1 transition cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        <span>Replace</span>
                                    </button>

                                    {{-- Delete Optional File --}}
                                    @if(!$doc['required'])
                                        <form method="POST" action="{{ route('teacher.profile.documents.delete', ['type' => $doc['type']]) }}"
                                              onsubmit="return confirm('Remove this document from your profile? You can re-upload at any time.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-xl hover:bg-rose-50 text-gray-400 hover:text-rose-600 transition cursor-pointer" title="Remove document">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @else
                                {{-- Upload First Time --}}
                                <div class="w-full flex items-center justify-between gap-3">
                                    <span class="text-[11px] text-gray-400 font-medium">Formats: {{ $doc['mimes_label'] }}</span>
                                    <button type="button"
                                            @click="openUploadModal('{{ $doc['type'] }}', '{{ e($doc['title']) }}', '{{ $doc['accept'] }}', '{{ $doc['mimes_label'] }}')"
                                            class="px-4 py-2 rounded-xl bg-primary-950 hover:bg-primary-900 text-white text-xs font-bold inline-flex items-center gap-1.5 transition shadow-xs cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        <span>Upload File</span>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Document Upload / Replace Modal --}}
            <div x-show="uploadModalOpen" x-cloak
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4"
                 @click.self="closeUploadModal()">
                <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-gray-100 flex flex-col space-y-5 animate-scale-up">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900" x-text="'Upload ' + activeDocTitle"></h3>
                                <p class="text-[11px] text-gray-400">Accepted: <span x-text="activeDocMimes"></span></p>
                            </div>
                        </div>
                        <button type="button" @click="closeUploadModal()" class="text-gray-400 hover:text-gray-600 text-lg leading-none p-1 cursor-pointer">
                            &times;
                        </button>
                    </div>

                    <form method="POST" action="{{ route('teacher.profile.documents.upload') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <input type="hidden" name="document_type" :value="activeDocType">

                        {{-- File Input Box --}}
                        <div class="border-2 border-dashed border-gray-200 hover:border-blue-400 rounded-2xl p-6 text-center transition-colors bg-gray-50/50">
                            <svg class="w-10 h-10 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            <label class="block text-xs font-bold text-gray-800 cursor-pointer mb-1">
                                <span>Choose a file from your computer</span>
                                <input type="file" name="file" required :accept="activeDocAccept" class="hidden"
                                       @change="$refs.chosenFileName.textContent = $event.target.files[0] ? $event.target.files[0].name : ''">
                            </label>
                            <p class="text-[11px] text-gray-500">or click to browse your folders</p>
                            <p x-ref="chosenFileName" class="text-xs font-bold text-blue-600 mt-2 truncate"></p>
                        </div>

                        <div class="text-[11px] text-gray-500 bg-blue-50/50 rounded-xl p-3 border border-blue-100 flex items-start gap-2">
                            <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Documents are securely processed and accessible by Aster Edu administrative reviewers. Maximum file size is 10MB per document.</span>
                        </div>

                        <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-gray-100">
                            <button type="button" @click="closeUploadModal()" class="px-4 py-2 border border-gray-300 rounded-xl text-xs font-semibold text-gray-700 hover:bg-gray-100 transition cursor-pointer">
                                Cancel
                            </button>
                            <button type="submit" class="px-5 py-2 bg-primary-950 hover:bg-primary-900 text-white rounded-xl text-xs font-bold transition shadow-sm cursor-pointer">
                                Upload &amp; Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>

        {{-- ─────────────────────────────────────────────────────────── --}}
        {{-- TAB 2: PERSONAL INFORMATION & AVATAR --}}
        {{-- ─────────────────────────────────────────────────────────── --}}
        <div x-show="tab === 'profile'" x-cloak class="space-y-6">
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-200/80 shadow-card">
                <form method="POST" action="{{ route('teacher.profile.update') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="tab" value="profile">
                    <input type="hidden" name="remove_avatar" :value="removeAvatar ? '1' : '0'">

                    {{-- Section 1: Avatar Upload --}}
                    <div>
                        <h2 class="text-sm font-bold text-gray-900 mb-1">Profile Photo</h2>
                        <p class="text-xs text-gray-500 mb-4">Your photo is displayed on student course pages and the public educator directory.</p>

                        <div class="flex items-center gap-5">
                            <div class="relative w-20 h-20 rounded-3xl overflow-hidden bg-gray-100 border-2 border-gray-200 flex items-center justify-center shrink-0 shadow-inner">
                                <template x-if="avatarPreview">
                                    <img :src="avatarPreview" alt="Avatar preview" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!avatarPreview">
                                    <span class="text-xl font-black text-gray-400 uppercase">
                                        {{ strtoupper(substr($user->name ?? 'T', 0, 2)) }}
                                    </span>
                                </template>
                            </div>

                            <div class="space-y-2">
                                <div class="flex items-center gap-2.5">
                                    <label class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-xl text-xs font-semibold cursor-pointer transition shadow-2xs">
                                        <span>Choose Photo</span>
                                        <input type="file" name="avatar" accept="image/png,image/jpeg,image/webp" class="hidden" x-ref="avatarInput" @change="previewAvatar($event)">
                                    </label>
                                    <button type="button" @click="clearAvatar()" x-show="avatarPreview"
                                            class="px-3 py-2 text-rose-600 hover:bg-rose-50 rounded-xl text-xs font-semibold transition cursor-pointer">
                                        Remove Photo
                                    </button>
                                </div>
                                <p class="text-[11px] text-gray-400">JPG, PNG, or WebP. Maximum file size 2MB.</p>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-6">
                        <h2 class="text-sm font-bold text-gray-900 mb-4">Personal Details</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Full Name (Official) *</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-medium">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Email Address</label>
                                <input type="email" value="{{ $user->email }}" disabled
                                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-xs text-gray-500 font-medium cursor-not-allowed" title="Email is managed by Aster Edu administration">
                                <span class="text-[10px] text-gray-400 mt-1 block">Primary login account identifier (managed by admin)</span>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Contact Phone Number</label>
                                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+60 12-345 6789"
                                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-medium">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Highest Qualification *</label>
                                <input type="text" name="qualification" value="{{ old('qualification', $teacher->qualification) }}" required
                                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-medium">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Teaching Experience (Years) *</label>
                                <input type="number" name="experience_years" min="0" max="50" value="{{ old('experience_years', $teacher->experience_years ?? 3) }}" required
                                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-medium">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Specialization / Subject Focus</label>
                                <input type="text" name="specialization" value="{{ old('specialization', $teacher->specialization) }}" placeholder="e.g. Additional Mathematics, Physics"
                                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-medium">
                            </div>
                        </div>
                    </div>

                    {{-- Residential Address --}}
                    <div class="border-t border-gray-100 pt-6">
                        <h2 class="text-sm font-bold text-gray-900 mb-4">Mailing &amp; Contact Address</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Address Line 1</label>
                                <input type="text" name="address_line_1" value="{{ old('address_line_1', $user->address_line_1) }}" placeholder="Street name / building"
                                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-medium">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Address Line 2 (Optional)</label>
                                <input type="text" name="address_line_2" value="{{ old('address_line_2', $user->address_line_2) }}" placeholder="Unit, Floor, Apartment"
                                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-medium">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">City</label>
                                <input type="text" name="city" value="{{ old('city', $user->city) }}" placeholder="e.g. Petaling Jaya"
                                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-medium">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Postcode</label>
                                <input type="text" name="postcode" value="{{ old('postcode', $user->postcode) }}" placeholder="47400"
                                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-medium">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">State</label>
                                <input type="text" name="state" value="{{ old('state', $user->state) }}" placeholder="e.g. Selangor"
                                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-medium">
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-5 flex items-center justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-primary-950 hover:bg-primary-900 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                            Save Personal Details
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ─────────────────────────────────────────────────────────── --}}
        {{-- TAB 3: TEACHING DETAILS & ASSIGNED CURRICULUM --}}
        {{-- ─────────────────────────────────────────────────────────── --}}
        <div x-show="tab === 'professional'" x-cloak class="space-y-6">
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-200/80 shadow-card">
                <form method="POST" action="{{ route('teacher.profile.update') }}" class="space-y-6">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="tab" value="professional">
                    <input type="hidden" name="name" value="{{ $user->name }}">
                    <input type="hidden" name="phone" value="{{ $user->phone }}">
                    <input type="hidden" name="address_line_1" value="{{ $user->address_line_1 }}">
                    <input type="hidden" name="address_line_2" value="{{ $user->address_line_2 }}">
                    <input type="hidden" name="city" value="{{ $user->city }}">
                    <input type="hidden" name="state" value="{{ $user->state }}">
                    <input type="hidden" name="postcode" value="{{ $user->postcode }}">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Highest Qualification *</label>
                            <input type="text" name="qualification" value="{{ old('qualification', $teacher->qualification) }}" required
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-medium">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Experience in Teaching (Years) *</label>
                            <input type="number" name="experience_years" min="0" max="50" value="{{ old('experience_years', $teacher->experience_years ?? 3) }}" required
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-medium">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Specialization Focus</label>
                            <input type="text" name="specialization" value="{{ old('specialization', $teacher->specialization) }}" placeholder="e.g. Pure Science (Physics, Chemistry, Add Maths)"
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-medium">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Teaching Style / Philosophy</label>
                            <input type="text" name="teaching_style" value="{{ old('teaching_style', $teacher->teaching_style) }}" placeholder="e.g. Interactive problem solving, exam technique mastery, Socratic questioning"
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-medium">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">Educator Bio &amp; Background</label>
                            <textarea name="bio" rows="4" placeholder="Brief overview of your teaching passion, certifications, and educational accomplishments..."
                                      class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-medium leading-relaxed">{{ old('bio', $teacher->bio) }}</textarea>
                            <span class="text-[10px] text-gray-400 mt-1 block">Displayed on your public teacher showcase profile and to prospective students.</span>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-5 flex items-center justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-primary-950 hover:bg-primary-900 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                            Update Teaching Information
                        </button>
                    </div>
                </form>
            </div>

            {{-- Assigned Subjects Overview --}}
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-200/80 shadow-card">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm font-bold text-gray-900">Assigned Curriculum Subjects</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Syllabus streams assigned to you by Aster Edu administration</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 font-bold text-[11px]">
                        {{ $teacher->subjects->count() }} Subjects Assigned
                    </span>
                </div>

                @if($teacher->subjects->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($teacher->subjects as $sub)
                            <div class="p-3.5 rounded-2xl bg-gray-50/70 border border-gray-200/80 flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-bold text-gray-900">{{ $sub->display_name ?? $sub->name }}</p>
                                    <p class="text-[10px] text-gray-500 mt-0.5">{{ $sub->stage ?? ucfirst($sub->education_type) }} · Form {{ $sub->form_year ?? '—' }}</p>
                                </div>
                                <span class="px-2 py-0.5 rounded-md bg-white border border-gray-200 text-[10px] font-mono text-gray-600">
                                    {{ $sub->code ?? 'Active' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6 bg-gray-50/50 rounded-2xl border border-dashed border-gray-200">
                        <p class="text-xs text-gray-500 font-medium">No curriculum subjects have been assigned to your profile yet.</p>
                        <p class="text-[11px] text-gray-400 mt-1">Please reach out to the administrator to assign teaching streams.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- ─────────────────────────────────────────────────────────── --}}
        {{-- TAB 4: PASSWORD & SECURITY --}}
        {{-- ─────────────────────────────────────────────────────────── --}}
        <div x-show="tab === 'security'" x-cloak class="space-y-6">
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-200/80 shadow-card max-w-xl">
                <div class="mb-5">
                    <h2 class="text-sm font-bold text-gray-900">Change Account Password</h2>
                    <p class="text-xs text-gray-500 mt-1">Ensure your teacher account uses a strong, unique password.</p>
                </div>

                <form method="POST" action="{{ route('teacher.profile.password') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Current Password *</label>
                        <input type="password" name="current_password" required autocomplete="current-password"
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">New Password *</label>
                        <input type="password" name="password" required autocomplete="new-password"
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-medium">
                        <span class="text-[10px] text-gray-400 mt-1 block">Must be at least 8 characters with numbers and mixed case.</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Confirm New Password *</label>
                        <input type="password" name="password_confirmation" required autocomplete="new-password"
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-medium">
                    </div>

                    <div class="pt-2 flex items-center justify-end">
                        <button type="submit" class="px-5 py-2.5 bg-primary-950 hover:bg-primary-900 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-teacher-layout>
