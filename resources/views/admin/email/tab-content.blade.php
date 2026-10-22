{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- TAB: ENTERPRISE EMAIL MANAGEMENT & EMAIL BLAST SYSTEM                      --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}

<div x-data="adminEmailManager()" x-init="initEmailManager()" class="space-y-6">

    {{-- ── EMAIL MANAGEMENT HUB HEADER & NAVIGATION ── --}}
    <div class="bg-white rounded-2xl border border-gray-200/80 p-4 shadow-xs space-y-3.5">
        {{-- Row 1: Hub Title & Action Buttons --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-bold text-gray-900 tracking-tight">Email Management System</h2>
                </div>
                <p class="text-xs text-gray-500 mt-0.5">Manage official communications, templates, automations, recipients, and deliverability settings</p>
            </div>

            {{-- Actions Buttons Group --}}
            <div class="flex items-center gap-2 shrink-0">
                <button type="button" @click="openQuickTestModal()" class="px-3.5 py-2 bg-slate-50 hover:bg-slate-100 hover:text-slate-900 border border-slate-200 text-slate-700 font-semibold text-xs rounded-xl shadow-2xs transition flex items-center gap-1.5 cursor-pointer active:scale-95">
                    <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    <span>Send Quick Test</span>
                </button>
                <button type="button" @click="openCampaignWizard()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>New Email Blast</span>
                </button>
            </div>
        </div>

        {{-- Row 2: Sub-Navigation Tabs (Neat, balanced spacing, no awkward wrap) --}}
        <div class="flex items-center gap-2 overflow-x-auto py-0.5" style="scrollbar-width: none; -ms-overflow-style: none;">
            {{-- Tab 1: Email Settings --}}
            <button type="button"
                    @click="switchSubTab('settings')"
                    :class="activeSubTab === 'settings' ? 'bg-blue-600 text-white shadow-xs font-semibold' : 'bg-slate-50 text-slate-600 hover:text-slate-900 hover:bg-slate-100 border border-slate-200/60 font-medium'"
                    class="px-4 py-2 rounded-xl text-xs transition flex items-center gap-2 cursor-pointer shrink-0 whitespace-nowrap active:scale-98">
                <span>Email Settings</span>
            </button>

            {{-- Tab 2: Email Templates --}}
            <button type="button"
                    @click="switchSubTab('templates')"
                    :class="activeSubTab === 'templates' ? 'bg-blue-600 text-white shadow-xs font-semibold' : 'bg-slate-50 text-slate-600 hover:text-slate-900 hover:bg-slate-100 border border-slate-200/60 font-medium'"
                    class="px-4 py-2 rounded-xl text-xs transition flex items-center gap-2 cursor-pointer shrink-0 whitespace-nowrap active:scale-98">
                <span>Email Templates</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                      :class="activeSubTab === 'templates' ? 'bg-white/25 text-white' : 'bg-slate-200 text-slate-700'">
                    {{ $emailTemplates->count() }}
                </span>
            </button>

            {{-- Tab 3: Email Blast / Campaigns --}}
            <button type="button"
                    @click="switchSubTab('campaigns')"
                    :class="activeSubTab === 'campaigns' ? 'bg-blue-600 text-white shadow-xs font-semibold' : 'bg-slate-50 text-slate-600 hover:text-slate-900 hover:bg-slate-100 border border-slate-200/60 font-medium'"
                    class="px-4 py-2 rounded-xl text-xs transition flex items-center gap-2 cursor-pointer shrink-0 whitespace-nowrap active:scale-98">
                <span>Email Blast / Campaigns</span>
                @if($emailStats['scheduled_campaigns'] > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-400 text-slate-900">{{ $emailStats['scheduled_campaigns'] }}</span>
                @endif
            </button>

            {{-- Tab 4: Automations --}}
            <button type="button"
                    @click="switchSubTab('automations')"
                    :class="activeSubTab === 'automations' ? 'bg-blue-600 text-white shadow-xs font-semibold' : 'bg-slate-50 text-slate-600 hover:text-slate-900 hover:bg-slate-100 border border-slate-200/60 font-medium'"
                    class="px-4 py-2 rounded-xl text-xs transition flex items-center gap-2 cursor-pointer shrink-0 whitespace-nowrap active:scale-98">
                <span>Automations</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                      :class="activeSubTab === 'automations' ? 'bg-white/25 text-white' : 'bg-emerald-100 text-emerald-800'">
                    {{ $emailStats['active_automations'] }}
                </span>
            </button>

            {{-- Tab 5: Recipients & Audience --}}
            <button type="button"
                    @click="switchSubTab('audience')"
                    :class="activeSubTab === 'audience' ? 'bg-blue-600 text-white shadow-xs font-semibold' : 'bg-slate-50 text-slate-600 hover:text-slate-900 hover:bg-slate-100 border border-slate-200/60 font-medium'"
                    class="px-4 py-2 rounded-xl text-xs transition flex items-center gap-2 cursor-pointer shrink-0 whitespace-nowrap active:scale-98">
                <span>Recipients &amp; Audience</span>
            </button>

            {{-- Tab 6: Email Logs --}}
            <button type="button"
                    @click="switchSubTab('logs')"
                    :class="activeSubTab === 'logs' ? 'bg-blue-600 text-white shadow-xs font-semibold' : 'bg-slate-50 text-slate-600 hover:text-slate-900 hover:bg-slate-100 border border-slate-200/60 font-medium'"
                    class="px-4 py-2 rounded-xl text-xs transition flex items-center gap-2 cursor-pointer shrink-0 whitespace-nowrap active:scale-98">
                <span>Email Logs</span>
                @if($emailStats['failed'] > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500 text-white">{{ $emailStats['failed'] }}</span>
                @endif
            </button>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- SUBTAB 2: EMAIL TEMPLATES                                             --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div x-show="activeSubTab === 'templates'" x-cloak class="space-y-6">
        
        {{-- Template Filter & Create Header Toolbar --}}
        <div class="bg-white rounded-2xl border border-gray-200/80 p-4 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 w-full md:w-auto">
                {{-- Category Filter Dropdown (without count) --}}
                <div class="relative min-w-[200px] sm:min-w-[220px]">
                    <select x-model="templateCategoryFilter"
                            class="w-full pl-3.5 pr-8 py-2 !bg-none bg-gray-50 hover:bg-gray-100/70 border border-gray-200 text-xs font-semibold text-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none cursor-pointer appearance-none transition">
                        <option value="all">All Categories</option>
                        @foreach($emailCategories as $catKey => $catLabel)
                            <option value="{{ $catKey }}">{{ $catLabel }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>

                {{-- Search Input --}}
                <div class="relative flex-1 sm:w-64">
                    <input type="text"
                           x-model="templateSearchQuery"
                           placeholder="Search templates..."
                           class="w-full pl-9 pr-3.5 py-2 bg-gray-50 border border-gray-200 text-xs rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none transition" />
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <button type="button" @click="openCreateTemplateModal()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center justify-center gap-1.5 shrink-0 cursor-pointer">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Create Template</span>
            </button>
        </div>

        {{-- Templates List Table --}}
        <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto admin-scrollbar">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-gray-100 text-gray-400 font-semibold uppercase text-[10px] bg-gray-50/50">
                            <th class="py-3 px-4">Template Name</th>
                            <th class="py-3 px-4">Category</th>
                            <th class="py-3 px-4">Subject</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    @foreach($emailTemplates as $tpl)
                        <tbody x-data="{ expanded: false, menuOpen: false }"
                               x-show="matchesTemplateFilter('{{ $tpl->category }}', {{ json_encode($tpl->name . ' ' . $tpl->subject . ' ' . $tpl->key) }})"
                               class="divide-y divide-gray-100 border-b border-gray-100 last:border-b-0">
                            
                            {{-- Primary Template Row --}}
                            <tr :class="expanded ? 'bg-blue-50/30' : 'hover:bg-gray-50/60'" class="transition">
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-gray-900 leading-snug">{{ $tpl->name }}</div>
                                    <div class="text-[10px] font-mono text-gray-400 mt-0.5">{{ $tpl->key }}</div>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider
                                        @if($tpl->category === 'payment') bg-emerald-50 text-emerald-700 border border-emerald-200
                                        @elseif($tpl->category === 'restriction') bg-rose-50 text-rose-700 border border-rose-200
                                        @elseif($tpl->category === 'class') bg-indigo-50 text-indigo-700 border border-indigo-200
                                        @elseif($tpl->category === 'academic') bg-purple-50 text-purple-700 border border-purple-200
                                        @elseif($tpl->category === 'renewal') bg-amber-50 text-amber-700 border border-amber-200
                                        @else bg-blue-50 text-blue-700 border border-blue-200 @endif">
                                        {{ $tpl->category_label }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-gray-700 max-w-xs md:max-w-md truncate" title="{{ $tpl->subject }}">
                                    <span class="font-medium text-gray-900">{{ $tpl->subject }}</span>
                                </td>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold
                                        {{ $tpl->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-gray-100 text-gray-600 border border-gray-200' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $tpl->status === 'active' ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                        {{ strtoupper($tpl->status) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5 justify-end">
                                        {{-- Dropdown Toggle for Details --}}
                                        <button type="button"
                                                @click="expanded = !expanded"
                                                :class="expanded ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                                                class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1 cursor-pointer"
                                                title="Toggle Details">
                                            <span>Details</span>
                                            <svg class="w-3.5 h-3.5 transition-transform duration-200"
                                                 :class="expanded ? 'rotate-180' : ''"
                                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>

                                        {{-- Edit Button --}}
                                        <button type="button"
                                                @click="editTemplateModal({{ json_encode($tpl) }})"
                                                class="px-2.5 py-1.5 text-xs font-semibold bg-white hover:bg-gray-100 text-gray-700 border border-gray-200 rounded-lg transition cursor-pointer">
                                            Edit
                                        </button>

                                        {{-- Actions Dropdown Menu --}}
                                        <div class="relative" @click.outside="menuOpen = false">
                                            <button type="button"
                                                    @click="menuOpen = !menuOpen"
                                                    class="p-1.5 text-gray-500 hover:text-gray-900 hover:bg-gray-100 rounded-lg border border-transparent hover:border-gray-200 transition cursor-pointer"
                                                    title="More Actions">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                                </svg>
                                            </button>

                                            <div x-show="menuOpen"
                                                 x-cloak
                                                 x-transition:enter="transition ease-out duration-100"
                                                 x-transition:enter-start="transform opacity-0 scale-95"
                                                 x-transition:enter-end="transform opacity-100 scale-100"
                                                 x-transition:leave="transition ease-in duration-75"
                                                 x-transition:leave-start="transform opacity-100 scale-100"
                                                 x-transition:leave-end="transform opacity-0 scale-95"
                                                 class="absolute right-0 mt-1 w-44 bg-white rounded-xl shadow-lg border border-gray-200 py-1 z-30 text-left">
                                                <button type="button"
                                                        @click="menuOpen = false; previewTemplateModal({{ json_encode($tpl) }})"
                                                        class="w-full px-3.5 py-2 text-xs text-gray-700 hover:bg-blue-50 hover:text-blue-700 flex items-center gap-2 cursor-pointer transition">
                                                    <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    <span>Preview Email</span>
                                                </button>
                                                <button type="button"
                                                        @click="menuOpen = false; testTemplateModal({{ json_encode($tpl) }})"
                                                        class="w-full px-3.5 py-2 text-xs text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 flex items-center gap-2 cursor-pointer transition">
                                                    <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                                    <span>Send Test Email</span>
                                                </button>
                                                <form method="POST" action="{{ route('admin.email.templates.duplicate', $tpl->id) }}" class="block">
                                                    @csrf
                                                    <button type="submit"
                                                            class="w-full px-3.5 py-2 text-xs text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 flex items-center gap-2 cursor-pointer transition">
                                                        <svg class="w-3.5 h-3.5 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                                        <span>Duplicate</span>
                                                    </button>
                                                </form>
                                                <div class="my-1 border-t border-gray-100"></div>
                                                <form method="POST" action="{{ route('admin.email.templates.toggle', $tpl->id) }}" class="block">
                                                    @csrf
                                                    <button type="submit"
                                                            class="w-full px-3.5 py-2 text-xs {{ $tpl->status === 'active' ? 'text-amber-700 hover:bg-amber-50' : 'text-emerald-700 hover:bg-emerald-50' }} flex items-center gap-2 cursor-pointer transition">
                                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                        <span>{{ $tpl->status === 'active' ? 'Disable Template' : 'Enable Template' }}</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            {{-- Dropdown Details Panel (Expanded) --}}
                            <tr x-show="expanded" x-cloak class="bg-slate-50/70">
                                <td colspan="5" class="p-5">
                                    <div class="bg-white rounded-xl border border-gray-200 p-4 space-y-3.5 shadow-2xs">
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-100 pb-2.5">
                                            <div class="text-xs">
                                                <span class="font-semibold text-gray-500 uppercase text-[10px] tracking-wider">Subject Line:</span>
                                                <span class="font-bold text-gray-900 ml-1">{{ $tpl->subject }}</span>
                                            </div>
                                            @if($tpl->cta_label && $tpl->cta_url)
                                                <div class="text-[11px] flex items-center gap-1.5 text-gray-600 bg-blue-50/60 border border-blue-100 px-2.5 py-1 rounded-lg">
                                                    <span class="font-semibold text-blue-700">CTA:</span>
                                                    <span>{{ $tpl->cta_label }}</span>
                                                    <span class="text-gray-400">&bull;</span>
                                                    <span class="font-mono text-blue-600 max-w-xs truncate">{{ $tpl->cta_url }}</span>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Body Text Preview --}}
                                        <div>
                                            <div class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                                                <span>Template Body Content:</span>
                                                <span class="text-[10px] font-normal text-gray-400">Plain text with variable tokens</span>
                                            </div>
                                            <div class="bg-gray-50/80 rounded-xl p-3.5 border border-gray-100 text-xs text-gray-700 leading-relaxed font-sans whitespace-pre-line max-h-56 overflow-y-auto admin-scrollbar">
{{ $tpl->body }}
                                            </div>
                                        </div>

                                        {{-- Quick Actions inside Details Dropdown --}}
                                        <div class="pt-2 flex flex-wrap items-center justify-between gap-2 text-xs">
                                            <div class="text-[11px] text-gray-400">
                                                <span>Last updated: {{ $tpl->updated_at?->diffForHumans() ?? 'N/A' }}</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <button type="button"
                                                        @click="previewTemplateModal({{ json_encode($tpl) }})"
                                                        class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg transition flex items-center gap-1.5 cursor-pointer">
                                                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    <span>Full HTML Preview</span>
                                                </button>
                                                <button type="button"
                                                        @click="testTemplateModal({{ json_encode($tpl) }})"
                                                        class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg transition flex items-center gap-1.5 cursor-pointer">
                                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                                    <span>Send Test</span>
                                                </button>
                                                <button type="button"
                                                        @click="editTemplateModal({{ json_encode($tpl) }})"
                                                        class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                    <span>Edit Template</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    @endforeach
                </table>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- SUBTAB 3: EMAIL BLAST & CAMPAIGNS                                     --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div x-show="activeSubTab === 'campaigns'" x-cloak class="space-y-6">

        {{-- Campaigns List Table --}}
        <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                <div>
                    <h3 class="font-semibold text-sm text-gray-900">Campaign History &amp; Scheduled Blasts</h3>
                    <p class="text-[11px] text-gray-400 font-normal">All bulk sending jobs, delivery percentages, and execution logs</p>
                </div>
                <button type="button" @click="openCampaignWizard()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Launch New Campaign</span>
                </button>
            </div>

            <div class="overflow-x-auto admin-scrollbar">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-gray-100 text-gray-400 font-medium uppercase text-[10px] bg-gray-50/50">
                            <th class="py-3 px-4">Campaign Name</th>
                            <th class="py-3 px-4">Audience Segment</th>
                            <th class="py-3 px-4">Subject</th>
                            <th class="py-3 px-4">Recipients</th>
                            <th class="py-3 px-4">Progress</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Scheduled / Sent At</th>
                            <th class="py-3 px-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($emailCampaigns as $c)
                            <tr class="hover:bg-gray-50/60 transition">
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-gray-900">{{ $c->name }}</div>
                                    <div class="text-[10px] text-gray-400">{{ ucfirst($c->category) }} &bull; By {{ $c->creator->name ?? 'Admin' }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-700">
                                        {{ $supportedAudiences[$c->audience_type]['label'] ?? ucfirst($c->audience_type) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-gray-700 max-w-xs truncate" title="{{ $c->subject }}">
                                    {{ $c->subject }}
                                </td>
                                <td class="py-3 px-4 font-mono font-medium text-gray-800">
                                    {{ number_format($c->total_recipients) }}
                                </td>
                                <td class="py-3 px-4 w-40">
                                    <div class="space-y-1">
                                        <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                            <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $c->progress_percent }}%"></div>
                                        </div>
                                        <div class="flex items-center justify-between text-[10px] text-gray-400 font-mono">
                                            <span>{{ $c->sent_count }} sent</span>
                                            <span>{{ $c->progress_percent }}%</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    @if($c->status === 'completed')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Completed</span>
                                    @elseif($c->status === 'processing')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200 animate-pulse">Processing</span>
                                    @elseif($c->status === 'scheduled')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">Scheduled</span>
                                    @elseif($c->status === 'cancelled')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-600">Cancelled</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700">Draft</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right text-gray-500 font-mono text-[11px]">
                                    @if($c->scheduled_at)
                                        {{ $c->scheduled_at->format('d M Y, h:i A') }}
                                    @elseif($c->completed_at)
                                        {{ $c->completed_at->format('d M Y, h:i A') }}
                                    @else
                                        {{ $c->created_at->format('d M Y, h:i A') }}
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <div class="inline-flex items-center gap-1.5">
                                        @if($c->isCancellable())
                                            <form method="POST" action="{{ route('admin.email.campaigns.cancel', $c->id) }}" onsubmit="return confirm('Are you sure you want to cancel this campaign?');">
                                                @csrf
                                                <button type="submit" class="p-1 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded transition" title="Cancel Scheduled Campaign">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-gray-400">
                                    No email blast campaigns created yet. Click "Launch New Campaign" above to begin.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- SUBTAB 4: AUTOMATED EMAILS                                            --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div x-show="activeSubTab === 'automations'" x-cloak class="space-y-6">

        <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="font-bold text-base text-gray-900">Event-Driven Email Automations</h3>
                <p class="text-xs text-gray-400 font-normal">System workflows that trigger transactional communications upon application, payment, class, or academic events</p>
            </div>
            <div class="text-xs text-gray-500 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Active Triggers: <strong>{{ $emailAutomations->where('is_enabled', true)->count() }} / {{ $emailAutomations->count() }}</strong></span>
            </div>
        </div>

        {{-- Automations Table --}}
        <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto admin-scrollbar">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-gray-100 text-gray-400 font-medium uppercase text-[10px] bg-gray-50/50">
                            <th class="py-3 px-4">Event Trigger &amp; Automation</th>
                            <th class="py-3 px-4">Category</th>
                            <th class="py-3 px-4">Assigned Template</th>
                            <th class="py-3 px-4">Telemetry (Success / Fail)</th>
                            <th class="py-3 px-4">Last Dispatched</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($emailAutomations as $auto)
                            <tr class="hover:bg-gray-50/60 transition">
                                <td class="py-3 px-4 max-w-sm">
                                    <div class="font-semibold text-gray-900">{{ $auto->name }}</div>
                                    <div class="text-[11px] text-gray-500 font-normal mt-0.5">{{ $auto->trigger_event }}</div>
                                    <div class="text-[10px] font-mono text-gray-400 mt-0.5">{{ $auto->key }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-blue-50 text-blue-700">
                                        {{ ucfirst($auto->category) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    @if($auto->template)
                                        <div class="font-medium text-gray-800">{{ $auto->template->name }}</div>
                                        <div class="text-[10px] text-gray-400 truncate max-w-xs">{{ $auto->template->subject }}</div>
                                    @else
                                        <span class="text-rose-500 font-medium">No template assigned</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 font-mono text-xs">
                                    <span class="text-emerald-600 font-semibold">{{ $auto->success_count }} sent</span>
                                    <span class="text-gray-300 mx-1">/</span>
                                    <span class="{{ $auto->failure_count > 0 ? 'text-rose-600 font-bold' : 'text-gray-400' }}">{{ $auto->failure_count }} failed</span>
                                </td>
                                <td class="py-3 px-4 text-gray-400 text-[11px]">
                                    {{ $auto->last_triggered_at ? $auto->last_triggered_at->diffForHumans() : 'Never' }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <form method="POST" action="{{ route('admin.email.automations.toggle', $auto->id) }}">
                                        @csrf
                                        <button type="submit"
                                                class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase transition cursor-pointer
                                                {{ $auto->is_enabled ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                            {{ $auto->is_enabled ? 'Active' : 'Disabled' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button @click="testAutomationModal({{ json_encode($auto) }})" class="p-1 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded transition" title="Test Fire Trigger">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </button>
                                        <button @click="editAutomationModal({{ json_encode($auto) }})" class="p-1 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded transition" title="Assign Template">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- SUBTAB 5: RECIPIENTS & AUDIENCE EXPLORER                              --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div x-show="activeSubTab === 'audience'" x-cloak class="space-y-6">

        <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold text-base text-gray-900">Audience Explorer &amp; Segmentation</h3>
                    <p class="text-xs text-gray-400 font-normal">Inspect recipient cohorts with parent-child linkage and active address resolution</p>
                </div>
                <div class="px-3 py-1.5 bg-blue-50 text-blue-700 rounded-xl text-xs font-semibold flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>Enforced Privacy: BCC Isolated Envelopes</span>
                </div>
            </div>

            {{-- Segment Selector Buttons --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                @foreach($supportedAudiences as $audKey => $audInfo)
                    <button @click="inspectAudience('{{ $audKey }}')"
                            :class="selectedAudienceKey === '{{ $audKey }}' ? 'ring-2 ring-blue-600 bg-blue-50/50 border-blue-300' : 'bg-white hover:bg-gray-50 border-gray-200'"
                            class="p-3.5 rounded-xl border text-left transition cursor-pointer flex flex-col justify-between">
                        <div>
                            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ $audInfo['category'] }}</div>
                            <div class="text-xs font-bold text-gray-900 mt-1">{{ $audInfo['label'] }}</div>
                            <div class="text-[11px] text-gray-500 font-normal mt-0.5 line-clamp-2">{{ $audInfo['description'] }}</div>
                        </div>
                        <div class="mt-3 text-[11px] font-semibold text-blue-600 flex items-center justify-between">
                            <span>Inspect &rarr;</span>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Live Sample Recipients Table --}}
        <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                <div class="flex items-center gap-3">
                    <h3 class="font-semibold text-sm text-gray-900" x-text="'Audience Segment: ' + (audienceLabels[selectedAudienceKey] || selectedAudienceKey)"></h3>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-600 text-white" x-text="audiencePreviewCount + ' Valid Recipients'"></span>
                </div>
                <div x-show="isAudienceLoading" class="text-xs text-blue-600 flex items-center gap-1.5">
                    <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <span>Calculating audience...</span>
                </div>
            </div>

            <div class="overflow-x-auto admin-scrollbar">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-gray-100 text-gray-400 font-medium uppercase text-[10px] bg-gray-50/50">
                            <th class="py-3 px-4">Recipient Name</th>
                            <th class="py-3 px-4">Verified Destination Email</th>
                            <th class="py-3 px-4">Account Type</th>
                            <th class="py-3 px-4 text-right">Suppression Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="r in audienceSample" :key="r.email">
                            <tr class="hover:bg-gray-50/60 transition">
                                <td class="py-3 px-4 font-medium text-gray-900" x-text="r.name"></td>
                                <td class="py-3 px-4 font-mono text-gray-600" x-text="r.email"></td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-700" x-text="r.type"></span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-700">Eligible</span>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="audienceSample.length === 0 && !isAudienceLoading">
                            <td colspan="4" class="py-8 text-center text-gray-400">
                                No recipients found matching this specific filter.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- SUBTAB 6: EMAIL LOGS & AUDIT TRAIL                                    --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div x-show="activeSubTab === 'logs'" x-cloak class="space-y-6">

        {{-- Filter & Export Header --}}
        <div class="bg-white rounded-2xl border border-gray-200/80 p-4 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                {{-- Status Pills --}}
                <div class="inline-flex rounded-xl bg-gray-100 p-1 text-xs font-semibold text-gray-500">
                    <button @click="logFilterStatus = 'all'"
                            :class="logFilterStatus === 'all' ? 'bg-white text-gray-900 shadow-xs' : 'hover:text-gray-900'"
                            class="px-3 py-1 rounded-lg transition cursor-pointer">
                        All ({{ $emailLogs->count() }})
                    </button>
                    <button @click="logFilterStatus = 'sent'"
                            :class="logFilterStatus === 'sent' ? 'bg-white text-emerald-700 shadow-xs' : 'hover:text-gray-900'"
                            class="px-3 py-1 rounded-lg transition cursor-pointer">
                        Sent ({{ $emailLogs->whereIn('status', ['sent', 'delivered'])->count() }})
                    </button>
                    <button @click="logFilterStatus = 'queued'"
                            :class="logFilterStatus === 'queued' ? 'bg-white text-amber-700 shadow-xs' : 'hover:text-gray-900'"
                            class="px-3 py-1 rounded-lg transition cursor-pointer">
                        Queued ({{ $emailLogs->where('status', 'queued')->count() }})
                    </button>
                    <button @click="logFilterStatus = 'failed'"
                            :class="logFilterStatus === 'failed' ? 'bg-white text-rose-700 shadow-xs' : 'hover:text-gray-900'"
                            class="px-3 py-1 rounded-lg transition cursor-pointer">
                        Failed ({{ $emailLogs->where('status', 'failed')->count() }})
                    </button>
                </div>

                {{-- Category Selector --}}
                <select x-model="logFilterCategory" class="pl-3.5 pr-8 py-1.5 bg-gray-50 border border-gray-200 text-xs rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none min-w-[150px]">
                    <option value="all">All Categories</option>
                    @foreach($emailCategories as $catKey => $catLabel)
                        <option value="{{ $catKey }}">{{ $catLabel }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <input type="text"
                       x-model="logSearchQuery"
                       placeholder="Search recipient, subject..."
                       class="px-3.5 py-1.5 bg-gray-50 border border-gray-200 text-xs rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none w-48 sm:w-64" />

                <a :href="'{{ route('admin.email.logs.export-csv') }}?status=' + (logFilterStatus !== 'all' ? logFilterStatus : '') + '&category=' + (logFilterCategory !== 'all' ? logFilterCategory : '') + '&search=' + encodeURIComponent(logSearchQuery)"
                   class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white font-medium text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 shrink-0 cursor-pointer">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Export CSV</span>
                </a>
            </div>
        </div>

        {{-- Logs Table --}}
        <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto admin-scrollbar">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-gray-100 text-gray-400 font-medium uppercase text-[10px] bg-gray-50/50">
                            <th class="py-3 px-4">Log Ref</th>
                            <th class="py-3 px-4">Recipient</th>
                            <th class="py-3 px-4">Category</th>
                            <th class="py-3 px-4">Subject</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Attempts</th>
                            <th class="py-3 px-4 text-right">Timestamp</th>
                            <th class="py-3 px-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($emailLogs as $log)
                            <tr x-show="matchesLogFilter('{{ $log->status }}', '{{ $log->category }}', {{ json_encode($log->recipient_email . ' ' . $log->recipient_name . ' ' . $log->subject) }})"
                                class="hover:bg-gray-50/60 transition">
                                <td class="py-3 px-4 font-mono text-gray-400 text-[11px]">
                                    LOG-{{ str_pad($log->id, 5, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-medium text-gray-900">{{ $log->recipient_name ?: 'Recipient' }}</div>
                                    <div class="text-[11px] text-gray-400 font-mono">{{ $log->recipient_email }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-blue-50 text-blue-700">
                                        {{ ucfirst($log->category) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-gray-800 max-w-xs truncate" title="{{ $log->subject }}">
                                    {{ $log->subject }}
                                </td>
                                <td class="py-3 px-4">
                                    @if($log->status === 'sent' || $log->status === 'delivered')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-700 flex items-center gap-1 w-max">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span>Sent</span>
                                        </span>
                                    @elseif($log->status === 'queued')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-50 text-amber-700 flex items-center gap-1 w-max">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            <span>Queued</span>
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-rose-50 text-rose-700 flex items-center gap-1 w-max" title="{{ $log->error_message }}">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            <span>Failed</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 font-mono text-[11px] text-gray-500">
                                    {{ $log->attempts }}x
                                </td>
                                <td class="py-3 px-4 text-right text-gray-400 font-mono text-[11px]">
                                    {{ $log->created_at ? $log->created_at->format('d M, H:i:s') : 'N/A' }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button @click="viewLogDetails({{ json_encode($log) }})" class="p-1 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded transition" title="Inspect Log Details">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>
                                        @if($log->status === 'failed')
                                            <form method="POST" action="{{ route('admin.email.logs.retry', $log->id) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="p-1 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded transition" title="Retry Delivery">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- SUBTAB 7: EMAIL SETTINGS & BRANDING BANNER                            --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div x-show="activeSubTab === 'settings'" x-cloak class="space-y-6">

        <form method="POST" action="{{ route('admin.email.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Left: Sender Identity & Footer (2 Cols) --}}
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-5">
                        <div class="border-b border-gray-100 pb-3">
                            <h3 class="font-bold text-base text-gray-900">Institutional Sender Identity</h3>
                            <p class="text-xs text-gray-400 font-normal">Official names, addresses, and return paths stamped on outgoing communications</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Sender Display Name</label>
                                <input type="text" name="sender_display_name" value="{{ $emailSettings['sender_display_name'] }}" required
                                       class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 text-xs rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                                <p class="text-[10px] text-gray-400 mt-1">Appears as the 'From' name in recipients' email inboxes</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Outgoing Sender Address</label>
                                <input type="email" name="sender_email_address" value="{{ $emailSettings['sender_email_address'] }}" required
                                       class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 text-xs rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                                <p class="text-[10px] text-gray-400 mt-1">Domain-verified outgoing email address</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Reply-To Address</label>
                                <input type="email" name="reply_to_email" value="{{ $emailSettings['reply_to_email'] }}"
                                       class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 text-xs rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                                <p class="text-[10px] text-gray-400 mt-1">Where direct parent replies will be routed</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Official Support Email</label>
                                <input type="email" name="official_support_email" value="{{ $emailSettings['official_support_email'] }}" required
                                       class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 text-xs rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                                <p class="text-[10px] text-gray-400 mt-1">Appears in footer for customer helpdesk queries</p>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Official Website URL</label>
                                <input type="url" name="official_website_url" value="{{ $emailSettings['official_website_url'] }}" required
                                       class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 text-xs rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Email Signature &amp; Legal Footer</label>
                                <textarea name="official_email_footer" rows="2" required
                                          class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 text-xs rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">{{ $emailSettings['official_email_footer'] }}</textarea>
                                <p class="text-[10px] text-gray-400 mt-1">Institutional footnote rendered at the bottom of all HTML and plain-text emails</p>
                            </div>
                        </div>

                        <div class="pt-2 flex justify-end">
                            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-xs transition cursor-pointer">
                                Save Identity Settings
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Right: Official Banner & Transport Info (1 Col) --}}
                <div class="space-y-6">
                    {{-- Official Banner Card --}}
                    <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                        <div class="border-b border-gray-100 pb-3">
                            <h3 class="font-bold text-sm text-gray-900">Official Email Header Banner</h3>
                            <p class="text-xs text-gray-400 font-normal">Responsive 4:1 aspect ratio header rendered on all official emails</p>
                        </div>

                        {{-- Current Banner Preview --}}
                        <div class="space-y-2">
                            <label class="block text-xs font-medium text-gray-500">Live Active Banner</label>
                            <div class="rounded-xl overflow-hidden border border-gray-200 bg-slate-900 shadow-2xs">
                                <img src="{{ $emailSettings['email_banner_url'] }}" alt="Active Banner Preview" class="w-full h-auto object-cover block" />
                            </div>
                        </div>

                        {{-- Public Banner URL Option --}}
                        <div class="space-y-1.5 pt-2 border-t border-gray-100">
                            <label class="block text-xs font-semibold text-gray-700">Custom Public Banner URL (Recommended for External Deliverability)</label>
                            <input type="url" name="email_banner_url" value="{{ \App\Models\Setting::get('email_banner_url', '') }}"
                                   placeholder="https://example.com/images/email-banner.png"
                                   class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 text-xs rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                            <p class="text-[10px] text-gray-400">Publicly accessible HTTPS image URL (e.g. on CDN, live website, or S3). Avoids attachment bloat and ensures immediate rendering in Gmail and Outlook without spam penalties.</p>
                        </div>

                        {{-- Upload Replacement Banner --}}
                        <div class="space-y-2 pt-2 border-t border-gray-100">
                            <label class="block text-xs font-semibold text-gray-700">Or Upload Image File from Computer</label>
                            <input type="file" name="banner_image" accept="image/png,image/jpeg,image/webp"
                                   class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                            <div class="text-[11px] text-gray-400 space-y-0.5">
                                <div>&bull; Recommended size: 1200 &times; 300 px (4:1 ratio)</div>
                                <div>&bull; Formats: PNG, JPG, WEBP (Max 3MB)</div>
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full py-2 bg-slate-800 hover:bg-slate-900 text-white font-medium text-xs rounded-xl shadow-xs transition cursor-pointer">
                                Update Official Banner
                            </button>
                        </div>
                    </div>

                    {{-- Mail Transport Status --}}
                    <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-xs space-y-3">
                        <div class="flex items-center justify-between">
                            <h4 class="font-bold text-xs text-gray-900 uppercase tracking-wider">Mail Transport Status</h4>
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        </div>

                        <div class="space-y-2 text-xs font-mono text-gray-600 bg-gray-50 p-3 rounded-xl border border-gray-100">
                            <div class="flex justify-between">
                                <span class="text-gray-400">Driver:</span>
                                <span class="font-semibold text-gray-800">{{ strtoupper($emailSettings['mail_mailer']) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">SMTP Host:</span>
                                <span class="font-semibold text-gray-800">{{ $emailSettings['mail_host'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">Port / Encryption:</span>
                                <span class="font-semibold text-gray-800">{{ $emailSettings['mail_port'] }} ({{ strtoupper($emailSettings['mail_encryption']) }})</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">Queue Transport:</span>
                                <span class="font-semibold text-emerald-600">database (async)</span>
                            </div>
                        </div>

                        <p class="text-[11px] text-gray-400 leading-relaxed">
                            Passwords and API credentials are kept securely in environment configuration and are never exposed in browser views.
                        </p>
                    </div>
                </div>

            </div>
        </form>

    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- MODALS: TEMPLATE EDITOR, PREVIEW, CAMPAIGN WIZARD, LOG DETAILS        --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}

    {{-- 1. Create/Edit Template Modal --}}
    <div x-show="showTemplateModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="showTemplateModal = false"></div>
        <div class="min-h-screen px-4 text-center flex items-center justify-center p-0">
            <div class="inline-block bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all my-8 max-w-2xl w-full border border-gray-200">
                <form :action="templateFormAction" method="POST">
                    @csrf
                    <input type="hidden" name="_method" :value="isEditingTemplate ? 'PUT' : 'POST'">
                    
                    <div class="px-6 pt-6 pb-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                        <div>
                            <h3 class="text-base font-bold text-gray-900" x-text="isEditingTemplate ? 'Edit Email Template' : 'Create New Email Template'"></h3>
                            <p class="text-xs text-gray-400 font-normal">Define institutional subject, plain-text body, and dynamic placeholders</p>
                        </div>
                        <button type="button" @click="showTemplateModal = false" class="text-gray-400 hover:text-gray-600 text-lg font-bold">&times;</button>
                    </div>

                    <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto admin-scrollbar">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Template Name</label>
                                <input type="text" name="name" x-model="templateFormData.name" required
                                       placeholder="e.g. Term 1 Welcome Notification"
                                       class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 text-xs rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Category</label>
                                <select name="category" x-model="templateFormData.category" required
                                        class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 text-xs rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                    @foreach($emailCategories as $ckey => $clabel)
                                        <option value="{{ $ckey }}">{{ $clabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Email Subject Line</label>
                            <input type="text" name="subject" x-model="templateFormData.subject" required
                                   placeholder="e.g. Official Update for &#123;&#123;student_name&#125;&#125; — Olympia Scholars"
                                   class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 text-xs rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                        </div>

                        {{-- Variable Picker Drawer Button --}}
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-semibold text-gray-700">Plain-Text Email Body</label>
                                <button type="button" @click="showVariablePicker = !showVariablePicker"
                                        class="text-xs text-blue-600 hover:text-blue-700 font-semibold flex items-center gap-1 cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                    <span>Insert Dynamic Variable</span>
                                </button>
                            </div>

                            {{-- Variable Picker Tray --}}
                            <div x-show="showVariablePicker" class="p-3 bg-blue-50/70 border border-blue-200 rounded-xl space-y-2">
                                <div class="text-[11px] font-bold text-blue-900">Click a variable to insert into body:</div>
                                <div class="flex flex-wrap gap-1.5 max-h-32 overflow-y-auto admin-scrollbar">
                                    @foreach($availableVariables as $vKey => $vInfo)
                                        <button type="button"
                                                @click="insertVariable('{{ $vKey }}')"
                                                class="px-2 py-0.5 bg-white hover:bg-blue-600 hover:text-white text-blue-700 border border-blue-300 rounded text-[11px] font-mono transition"
                                                title="{{ $vInfo['label'] }} ({{ $vInfo['category'] }})">
                                            &#123;&#123; {{ $vKey }} &#125;&#125;
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <textarea id="templateBodyTextarea" name="body" x-model="templateFormData.body" rows="9" required
                                      class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 text-xs rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none font-sans leading-relaxed"></textarea>
                            <p class="text-[10px] text-gray-400">Preserve paragraphs with blank lines. Bullet lists with * or - are formatted automatically.</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-gray-100">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Optional CTA Button Label</label>
                                <input type="text" name="cta_label" x-model="templateFormData.cta_label"
                                       placeholder="e.g. View Invoice Now"
                                       class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 text-xs rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Optional CTA Button URL</label>
                                <input type="text" name="cta_url" x-model="templateFormData.cta_url"
                                       placeholder="e.g. &#123;&#123;portal_url&#125;&#125; or https://..."
                                       class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 text-xs rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <button type="button" @click="testRenderPreview(templateFormData.subject, templateFormData.body, templateFormData.cta_label, templateFormData.cta_url)"
                                class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>Live Preview</span>
                        </button>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="showTemplateModal = false" class="px-4 py-2 border border-gray-200 hover:bg-gray-100 text-gray-700 font-medium text-xs rounded-xl transition">
                                Cancel
                            </button>
                            <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-xs transition">
                                Save Template
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 2. Live Email Preview Modal (Responsive IFrame-like rendering) --}}
    <div x-show="showPreviewModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="showPreviewModal = false"></div>
        <div class="min-h-screen px-4 text-center flex items-center justify-center p-0">
            <div class="inline-block bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all my-8 max-w-3xl w-full border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-red-400"></span>
                        <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                        <span class="w-3 h-3 rounded-full bg-emerald-400"></span>
                        <span class="text-xs font-bold text-gray-700 ml-2">Email Client Preview (Desktop &amp; Mobile Compatible)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="previewMode = 'html'" :class="previewMode === 'html' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'" class="px-2.5 py-1 text-[11px] font-semibold rounded-lg transition cursor-pointer">HTML</button>
                        <button @click="previewMode = 'text'" :class="previewMode === 'text' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'" class="px-2.5 py-1 text-[11px] font-semibold rounded-lg transition cursor-pointer">Plain-Text</button>
                        <button type="button" @click="showPreviewModal = false" class="text-gray-400 hover:text-gray-600 text-lg font-bold ml-2">&times;</button>
                    </div>
                </div>

                <div class="p-6 bg-[#f1f5f9] max-h-[75vh] overflow-y-auto admin-scrollbar">
                    <div x-show="previewMode === 'html'" class="rounded-xl overflow-hidden shadow-xs">
                        <div x-html="previewHtml"></div>
                    </div>
                    <div x-show="previewMode === 'text'" class="bg-white p-6 rounded-xl font-mono text-xs text-gray-800 whitespace-pre-wrap border border-gray-200" x-text="previewText"></div>
                </div>

                <div class="px-6 py-3 border-t border-gray-100 bg-white flex items-center justify-between text-xs text-gray-500">
                    <span>Subject: <strong class="text-gray-800" x-text="previewSubject"></strong></span>
                    <button type="button" @click="showPreviewModal = false" class="px-4 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium">Close Preview</button>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Quick Test Email Sender Modal --}}
    <div x-show="showTestModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="showTestModal = false"></div>
        <div class="min-h-screen px-4 text-center flex items-center justify-center p-0">
            <div class="inline-block bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all my-8 max-w-md w-full border border-gray-200">
                <form method="POST" action="{{ route('admin.email.send-test') }}">
                    @csrf
                    <input type="hidden" name="subject" :value="testEmailData.subject">
                    <input type="hidden" name="body" :value="testEmailData.body">
                    <input type="hidden" name="cta_label" :value="testEmailData.cta_label">
                    <input type="hidden" name="cta_url" :value="testEmailData.cta_url">

                    <div class="px-6 pt-6 pb-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Send Test Email</h3>
                            <p class="text-xs text-gray-400 font-normal">Deliver a live test message with sample data</p>
                        </div>
                        <button type="button" @click="showTestModal = false" class="text-gray-400 hover:text-gray-600 text-lg font-bold">&times;</button>
                    </div>

                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Destination Email Address</label>
                            <input type="email" name="test_email" required
                                   value="{{ auth()->user()->email ?? '' }}"
                                   placeholder="admin@example.com"
                                   class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 text-xs rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                            <p class="text-[10px] text-gray-400 mt-1">An actual email with official banner and signature will be sent immediately.</p>
                        </div>

                        <div class="bg-blue-50/60 border border-blue-200 rounded-xl p-3 text-xs space-y-1">
                            <div class="font-semibold text-blue-900">Preview Subject:</div>
                            <div class="text-gray-700 font-medium truncate" x-text="testEmailData.subject"></div>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex items-center justify-end gap-2">
                        <button type="button" @click="showTestModal = false" class="px-4 py-2 border border-gray-200 hover:bg-gray-100 text-gray-700 font-medium text-xs rounded-xl transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-xs transition">
                            Send Now
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 4. 5-Step Campaign Wizard Modal --}}
    <div x-show="showCampaignModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="showCampaignModal = false"></div>
        <div class="min-h-screen px-4 text-center flex items-center justify-center p-0">
            <div class="inline-block bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all my-8 max-w-3xl w-full border border-gray-200">
                <form method="POST" action="{{ route('admin.email.campaigns.store') }}" @submit="confirmBlastSubmit($event)">
                    @csrf

                    {{-- Wizard Header & Step Indicator --}}
                    <div class="px-6 pt-6 pb-4 border-b border-gray-100 bg-gray-50/50">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-blue-100 text-blue-800">Campaign Builder</span>
                                <h3 class="text-base font-bold text-gray-900 mt-0.5">5-Step Email Blast Wizard</h3>
                            </div>
                            <button type="button" @click="showCampaignModal = false" class="text-gray-400 hover:text-gray-600 text-lg font-bold">&times;</button>
                        </div>

                        {{-- Step Pills --}}
                        <div class="grid grid-cols-5 gap-2 text-center text-xs font-medium">
                            <div :class="wizardStep >= 1 ? 'text-blue-600 font-bold border-b-2 border-blue-600 pb-1' : 'text-gray-400 pb-1'">1. Info</div>
                            <div :class="wizardStep >= 2 ? 'text-blue-600 font-bold border-b-2 border-blue-600 pb-1' : 'text-gray-400 pb-1'">2. Audience</div>
                            <div :class="wizardStep >= 3 ? 'text-blue-600 font-bold border-b-2 border-blue-600 pb-1' : 'text-gray-400 pb-1'">3. Content</div>
                            <div :class="wizardStep >= 4 ? 'text-blue-600 font-bold border-b-2 border-blue-600 pb-1' : 'text-gray-400 pb-1'">4. Delivery</div>
                            <div :class="wizardStep >= 5 ? 'text-blue-600 font-bold border-b-2 border-blue-600 pb-1' : 'text-gray-400 pb-1'">5. Review</div>
                        </div>
                    </div>

                    {{-- Step Content Container --}}
                    <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto admin-scrollbar">

                        {{-- STEP 1: Campaign Information --}}
                        <div x-show="wizardStep === 1" class="space-y-4">
                            <h4 class="font-bold text-sm text-gray-900">Step 1: Campaign Information</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Campaign Title</label>
                                    <input type="text" name="name" x-model="campaignData.name" required
                                           placeholder="e.g. Term 2 Examination Circular"
                                           class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 text-xs rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Campaign Category</label>
                                    <select name="category" x-model="campaignData.category" required
                                            class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 text-xs rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                        @foreach($emailCategories as $ckey => $clabel)
                                            <option value="{{ $ckey }}">{{ $clabel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Internal Description</label>
                                <textarea name="description" x-model="campaignData.description" rows="2"
                                          placeholder="Internal notes regarding this communication blast..."
                                          class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 text-xs rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Base Template (Optional)</label>
                                <select name="template_id" x-model="campaignData.template_id" @change="applyBaseTemplate($event.target.value)"
                                        class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 text-xs rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                    <option value="">Start with blank content</option>
                                    @foreach($emailTemplates as $t)
                                        <option value="{{ $t->id }}" data-subject="{{ $t->subject }}" data-body="{{ $t->body }}" data-cta-label="{{ $t->cta_label }}" data-cta-url="{{ $t->cta_url }}">{{ $t->name }} ({{ $t->category_label }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- STEP 2: Audience Selection --}}
                        <div x-show="wizardStep === 2" class="space-y-4">
                            <h4 class="font-bold text-sm text-gray-900">Step 2: Audience Selection &amp; Targeting</h4>
                            
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Select Target Audience</label>
                                <select name="audience_type" x-model="campaignData.audience_type" @change="calculateCampaignAudience()" required
                                        class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 text-xs rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                    @foreach($supportedAudiences as $audKey => $audInfo)
                                        <option value="{{ $audKey }}">{{ $audInfo['label'] }} — {{ $audInfo['description'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Dynamic Sub-filters based on audience type --}}
                            <div x-show="campaignData.audience_type === 'students_by_programme'" class="p-3 bg-gray-50 rounded-xl border border-gray-200 space-y-2">
                                <label class="block text-xs font-semibold text-gray-700">Programme / Level Filter</label>
                                <select name="programme_filter" x-model="campaignData.programme_filter" @change="calculateCampaignAudience()"
                                        class="w-full px-3 py-1.5 bg-white border border-gray-200 text-xs rounded-lg">
                                    <option value="">All Programmes</option>
                                    <option value="Primary">Primary (Standard 1 - 6)</option>
                                    <option value="Secondary">Secondary (Form 1 - 5)</option>
                                    <option value="Kindergarten">Kindergarten</option>
                                </select>
                            </div>

                            <div x-show="campaignData.audience_type === 'students_by_subject'" class="p-3 bg-gray-50 rounded-xl border border-gray-200 space-y-2">
                                <label class="block text-xs font-semibold text-gray-700">Filter by Subject</label>
                                <select name="subject_filter" x-model="campaignData.subject_filter" @change="calculateCampaignAudience()"
                                        class="w-full px-3 py-1.5 bg-white border border-gray-200 text-xs rounded-lg">
                                    <option value="">Select Enrolled Subject</option>
                                    @foreach($allSubjects as $sub)
                                        <option value="{{ $sub->id }}">{{ $sub->display_name ?? $sub->name }} ({{ $sub->category }})</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Live Recipient Count Badge --}}
                            <div class="bg-blue-50/70 border border-blue-200 rounded-2xl p-4 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    </div>
                                    <div>
                                        <div class="text-xs text-blue-900 font-bold">Target Recipient Estimate</div>
                                        <div class="text-[11px] text-blue-700">Suppression list checked &amp; deduplicated</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-extrabold text-blue-900" x-text="campaignAudienceCount + ' Recipients'"></div>
                                </div>
                            </div>
                        </div>

                        {{-- STEP 3: Content --}}
                        <div x-show="wizardStep === 3" class="space-y-4">
                            <h4 class="font-bold text-sm text-gray-900">Step 3: Email Subject &amp; Plain-Text Content</h4>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Email Subject</label>
                                <input type="text" name="subject" x-model="campaignData.subject" required
                                       placeholder="e.g. Official Announcement: End-of-Term Schedule"
                                       class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 text-xs rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Email Body (Plain Text)</label>
                                <textarea name="body" x-model="campaignData.body" rows="8" required
                                          placeholder="Dear &#123;&#123;parent_name&#125;&#125;,&#10;&#10;We would like to announce..."
                                          class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 text-xs rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none font-sans leading-relaxed"></textarea>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">CTA Button Label</label>
                                    <input type="text" name="cta_label" x-model="campaignData.cta_label"
                                           placeholder="e.g. Open Portal"
                                           class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 text-xs rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">CTA Button URL</label>
                                    <input type="text" name="cta_url" x-model="campaignData.cta_url"
                                           placeholder="e.g. &#123;&#123;portal_url&#125;&#125;"
                                           class="w-full px-3.5 py-2 bg-gray-50 border border-gray-200 text-xs rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                                </div>
                            </div>
                        </div>

                        {{-- STEP 4: Delivery Options --}}
                        <div x-show="wizardStep === 4" class="space-y-4">
                            <h4 class="font-bold text-sm text-gray-900">Step 4: Delivery Options &amp; Scheduling</h4>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <label class="p-4 rounded-2xl border text-left cursor-pointer transition flex flex-col justify-between"
                                       :class="campaignData.delivery_type === 'immediate' ? 'ring-2 ring-blue-600 bg-blue-50/50 border-blue-300' : 'bg-white border-gray-200'">
                                    <div>
                                        <input type="radio" name="delivery_type" value="immediate" x-model="campaignData.delivery_type" class="sr-only">
                                        <div class="font-bold text-xs text-gray-900">Send Immediately</div>
                                        <div class="text-[11px] text-gray-500 mt-1">Dispatches to database queue as soon as confirmed</div>
                                    </div>
                                    <div class="mt-3 text-[10px] font-semibold text-blue-600">Immediate Batch</div>
                                </label>

                                <label class="p-4 rounded-2xl border text-left cursor-pointer transition flex flex-col justify-between"
                                       :class="campaignData.delivery_type === 'scheduled' ? 'ring-2 ring-blue-600 bg-blue-50/50 border-blue-300' : 'bg-white border-gray-200'">
                                    <div>
                                        <input type="radio" name="delivery_type" value="scheduled" x-model="campaignData.delivery_type" class="sr-only">
                                        <div class="font-bold text-xs text-gray-900">Schedule Delivery</div>
                                        <div class="text-[11px] text-gray-500 mt-1">Set date and time (Asia/Kuala_Lumpur)</div>
                                    </div>
                                    <div class="mt-3 text-[10px] font-semibold text-blue-600">Time-based</div>
                                </label>

                                <label class="p-4 rounded-2xl border text-left cursor-pointer transition flex flex-col justify-between"
                                       :class="campaignData.delivery_type === 'draft' ? 'ring-2 ring-blue-600 bg-blue-50/50 border-blue-300' : 'bg-white border-gray-200'">
                                    <div>
                                        <input type="radio" name="delivery_type" value="draft" x-model="campaignData.delivery_type" class="sr-only">
                                        <div class="font-bold text-xs text-gray-900">Save as Draft</div>
                                        <div class="text-[11px] text-gray-500 mt-1">Store campaign configuration for later dispatch</div>
                                    </div>
                                    <div class="mt-3 text-[10px] font-semibold text-blue-600">Draft Only</div>
                                </label>
                            </div>

                            <div x-show="campaignData.delivery_type === 'scheduled'" class="p-4 bg-gray-50 rounded-2xl border border-gray-200 space-y-2">
                                <label class="block text-xs font-semibold text-gray-700">Scheduled Date &amp; Time (Malaysia Time: Asia/Kuala_Lumpur)</label>
                                <input type="datetime-local" name="scheduled_at" x-model="campaignData.scheduled_at"
                                       class="w-full px-3.5 py-2 bg-white border border-gray-200 text-xs rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                            </div>
                        </div>

                        {{-- STEP 5: Final Review --}}
                        <div x-show="wizardStep === 5" class="space-y-4">
                            <h4 class="font-bold text-sm text-gray-900">Step 5: Final Review &amp; Launch Confirmation</h4>
                            
                            <div class="bg-gray-50 rounded-2xl border border-gray-200 p-5 space-y-3 text-xs">
                                <div class="flex justify-between border-b border-gray-200/60 pb-2">
                                    <span class="text-gray-500">Campaign Name:</span>
                                    <span class="font-semibold text-gray-900" x-text="campaignData.name"></span>
                                </div>
                                <div class="flex justify-between border-b border-gray-200/60 pb-2">
                                    <span class="text-gray-500">Audience Segment:</span>
                                    <span class="font-semibold text-blue-700" x-text="audienceLabels[campaignData.audience_type] || campaignData.audience_type"></span>
                                </div>
                                <div class="flex justify-between border-b border-gray-200/60 pb-2">
                                    <span class="text-gray-500">Recipient Count:</span>
                                    <span class="font-mono font-bold text-gray-900" x-text="campaignAudienceCount + ' Recipients'"></span>
                                </div>
                                <div class="flex justify-between border-b border-gray-200/60 pb-2">
                                    <span class="text-gray-500">Subject:</span>
                                    <span class="font-semibold text-gray-900 truncate max-w-sm" x-text="campaignData.subject"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Delivery Mode:</span>
                                    <span class="font-bold text-emerald-700 uppercase" x-text="campaignData.delivery_type"></span>
                                </div>
                            </div>

                            <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 leading-relaxed">
                                <strong>Institutional Confirmation:</strong> Submitting will register this campaign for official delivery. Recipients will receive this message with the official Olympia Scholars banner.
                            </div>
                        </div>

                    </div>

                    {{-- Wizard Step Navigation Controls --}}
                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <button type="button" @click="wizardPrev()" x-show="wizardStep > 1" class="px-4 py-2 border border-gray-200 hover:bg-gray-100 text-gray-700 font-medium text-xs rounded-xl transition">
                            &larr; Previous Step
                        </button>
                        <div x-show="wizardStep === 1"></div>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="wizardNext()" x-show="wizardStep < 5" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-xs transition">
                                Next Step &rarr;
                            </button>
                            <button type="submit" x-show="wizardStep === 5" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition">
                                Launch Campaign Blast
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 5. Email Log Details Modal --}}
    <div x-show="showLogDetailsModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="showLogDetailsModal = false"></div>
        <div class="min-h-screen px-4 text-center flex items-center justify-center p-0">
            <div class="inline-block bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all my-8 max-w-lg w-full border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <h3 class="text-base font-bold text-gray-900">Email Dispatch Audit Record</h3>
                    <button type="button" @click="showLogDetailsModal = false" class="text-gray-400 hover:text-gray-600 text-lg font-bold">&times;</button>
                </div>
                <div class="p-6 space-y-3 text-xs" x-show="selectedLog">
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Log ID:</span>
                            <span class="font-mono font-semibold" x-text="'LOG-' + selectedLog.id"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Recipient Email:</span>
                            <span class="font-mono font-semibold text-blue-600" x-text="selectedLog.recipient_email"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Recipient Name:</span>
                            <span class="font-semibold text-gray-800" x-text="selectedLog.recipient_name || 'N/A'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Delivery Status:</span>
                            <span class="font-bold uppercase" :class="selectedLog.status === 'sent' || selectedLog.status === 'delivered' ? 'text-emerald-600' : (selectedLog.status === 'queued' ? 'text-amber-600' : 'text-rose-600')" x-text="selectedLog.status"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Delivery Attempts:</span>
                            <span class="font-mono" x-text="selectedLog.attempts + 'x'"></span>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-gray-500 font-semibold">Subject Line:</label>
                        <div class="p-2.5 bg-gray-50 rounded-lg text-gray-800 font-medium" x-text="selectedLog.subject"></div>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-gray-500 font-semibold">Content Preview:</label>
                        <div class="p-3 bg-gray-50 rounded-lg text-gray-700 whitespace-pre-wrap font-sans leading-relaxed" x-text="selectedLog.body_preview"></div>
                    </div>

                    <div x-show="selectedLog.error_message" class="p-3 bg-rose-50 border border-rose-200 rounded-lg text-rose-800 space-y-1">
                        <div class="font-bold">Provider Error:</div>
                        <div class="font-mono text-[11px]" x-text="selectedLog.error_message"></div>
                    </div>
                </div>
                <div class="px-6 py-3 border-t border-gray-100 bg-gray-50 flex justify-end">
                    <button type="button" @click="showLogDetailsModal = false" class="px-4 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg text-xs">Close</button>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ── ALPINE.JS LOGIC FOR EMAIL MANAGEMENT HUB ── --}}
<script>
function adminEmailManager() {
    return {
        activeSubTab: 'settings',
        templateCategoryFilter: 'all',
        templateSearchQuery: '',
        logFilterStatus: 'all',
        logFilterCategory: 'all',
        logSearchQuery: '',
        
        // Modals state
        showTemplateModal: false,
        isEditingTemplate: false,
        templateFormAction: '{{ route('admin.email.templates.store') }}',
        templateFormData: { id: null, name: '', category: 'greetings', subject: '', body: '', cta_label: '', cta_url: '' },
        showVariablePicker: false,

        showPreviewModal: false,
        previewMode: 'html',
        previewSubject: '',
        previewHtml: '',
        previewText: '',

        showTestModal: false,
        testEmailData: { subject: '', body: '', cta_label: '', cta_url: '' },

        showCampaignModal: false,
        wizardStep: 1,
        campaignData: { name: '', category: 'general', description: '', template_id: '', audience_type: 'all_parents', subject: '', body: '', cta_label: '', cta_url: '', delivery_type: 'immediate', scheduled_at: '', programme_filter: '', subject_filter: '', class_filter: '' },
        campaignAudienceCount: 0,

        selectedAudienceKey: 'all_parents',
        audiencePreviewCount: 0,
        audienceSample: [],
        isAudienceLoading: false,

        showLogDetailsModal: false,
        selectedLog: null,

        audienceLabels: @json(collect($supportedAudiences)->mapWithKeys(fn($v, $k) => [$k => $v['label']])),

        initEmailManager() {
            // Check hash on load
            const hash = window.location.hash.replace('#', '');
            if (hash.startsWith('emailsettings-')) {
                const sub = hash.replace('emailsettings-', '');
                this.activeSubTab = sub;
            } else if (hash === 'emailsettings') {
                this.activeSubTab = 'settings';
            } else {
                this.activeSubTab = 'settings';
            }

            // Sync with browser hash changes
            window.addEventListener('hashchange', () => {
                const h = window.location.hash.replace('#', '');
                if (h.startsWith('emailsettings-')) {
                    this.activeSubTab = h.replace('emailsettings-', '');
                } else if (h === 'emailsettings') {
                    this.activeSubTab = 'settings';
                }
            });

            this.inspectAudience(this.selectedAudienceKey);
        },

        switchSubTab(tab) {
            this.activeSubTab = tab;
            history.replaceState(null, '', '#emailsettings-' + tab);
            try {
                localStorage.setItem('adminActiveEmailSubTab', tab);
            } catch(e) {}
        },

        matchesTemplateFilter(category, text) {
            if (this.templateCategoryFilter !== 'all' && category !== this.templateCategoryFilter) {
                return false;
            }
            if (!this.templateSearchQuery) return true;
            return text.toLowerCase().includes(this.templateSearchQuery.toLowerCase());
        },

        matchesLogFilter(status, category, text) {
            if (this.logFilterStatus !== 'all') {
                if (this.logFilterStatus === 'sent' && !(status === 'sent' || status === 'delivered')) return false;
                if (this.logFilterStatus === 'queued' && status !== 'queued') return false;
                if (this.logFilterStatus === 'failed' && status !== 'failed') return false;
            }
            if (this.logFilterCategory !== 'all' && category !== this.logFilterCategory) {
                return false;
            }
            if (!this.logSearchQuery) return true;
            return text.toLowerCase().includes(this.logSearchQuery.toLowerCase());
        },

        openCreateTemplateModal() {
            this.isEditingTemplate = false;
            this.templateFormAction = '{{ route('admin.email.templates.store') }}';
            this.templateFormData = { id: null, name: '', category: 'greetings', subject: '', body: '', cta_label: '', cta_url: '' };
            this.showTemplateModal = true;
        },

        editTemplateModal(tpl) {
            this.isEditingTemplate = true;
            this.templateFormAction = '{{ url('admin/email-management/templates') }}/' + tpl.id;
            this.templateFormData = {
                id: tpl.id,
                name: tpl.name,
                category: tpl.category,
                subject: tpl.subject,
                body: tpl.body,
                cta_label: tpl.cta_label || '',
                cta_url: tpl.cta_url || '',
            };
            this.showTemplateModal = true;
        },

        insertVariable(vKey) {
            const textarea = document.getElementById('templateBodyTextarea');
            const insertText = '{{' + vKey + '}}';
            if (textarea) {
                const start = textarea.selectionStart;
                const end = textarea.selectionEnd;
                const val = this.templateFormData.body;
                this.templateFormData.body = val.substring(0, start) + insertText + val.substring(end);
            } else {
                this.templateFormData.body += ' ' + insertText;
            }
            this.showVariablePicker = false;
        },

        testRenderPreview(subject, body, ctaLabel, ctaUrl) {
            fetch('{{ route('admin.email.template.preview') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    subject: subject,
                    body: body,
                    cta_label: ctaLabel,
                    cta_url: ctaUrl
                })
            })
            .then(res => res.json())
            .then(data => {
                this.previewSubject = data.subject;
                this.previewHtml = data.html;
                this.previewText = data.plain_text;
                this.previewMode = 'html';
                this.showPreviewModal = true;
            })
            .catch(err => alert('Preview generation failed: ' + err));
        },

        previewTemplateModal(tpl) {
            this.testRenderPreview(tpl.subject, tpl.body, tpl.cta_label, tpl.cta_url);
        },

        testTemplateModal(tpl) {
            this.testEmailData = {
                subject: tpl.subject,
                body: tpl.body,
                cta_label: tpl.cta_label || '',
                cta_url: tpl.cta_url || '',
            };
            this.showTestModal = true;
        },

        openQuickTestModal() {
            this.testEmailData = {
                subject: 'Olympia Scholars Verification Test Message',
                body: 'Dear @{{parent_name}},\n\nGreetings from Olympia Scholars.\n\nThis is an official verification test dispatch confirming that your email transport settings and official banner rendering are operating correctly.\n\nThank you,\nOlympia Scholars Administration',
                cta_label: 'Visit Parent Portal',
                cta_url: '@{{portal_url}}',
            };
            this.showTestModal = true;
        },

        openCampaignWizard() {
            this.wizardStep = 1;
            this.campaignData = {
                name: '',
                category: 'general',
                description: '',
                template_id: '',
                audience_type: 'all_parents',
                subject: '',
                body: '',
                cta_label: '',
                cta_url: '',
                delivery_type: 'immediate',
                scheduled_at: '',
                programme_filter: '',
                subject_filter: '',
                class_filter: ''
            };
            this.calculateCampaignAudience();
            this.showCampaignModal = true;
        },

        applyBaseTemplate(tplId) {
            if (!tplId) return;
            const opt = document.querySelector(`select[name="template_id"] option[value="${tplId}"]`);
            if (opt) {
                this.campaignData.subject = opt.getAttribute('data-subject') || '';
                this.campaignData.body = opt.getAttribute('data-body') || '';
                this.campaignData.cta_label = opt.getAttribute('data-cta-label') || '';
                this.campaignData.cta_url = opt.getAttribute('data-cta-url') || '';
            }
        },

        wizardNext() {
            if (this.wizardStep === 1 && !this.campaignData.name) {
                alert('Please enter a campaign name to proceed.');
                return;
            }
            if (this.wizardStep === 3 && (!this.campaignData.subject || !this.campaignData.body)) {
                alert('Please provide email subject and body content.');
                return;
            }
            if (this.wizardStep < 5) this.wizardStep++;
        },

        wizardPrev() {
            if (this.wizardStep > 1) this.wizardStep--;
        },

        confirmBlastSubmit(e) {
            if (this.campaignData.delivery_type === 'immediate') {
                if (!confirm(`Are you sure you want to dispatch this email blast immediately to ${this.campaignAudienceCount} recipients?`)) {
                    e.preventDefault();
                }
            }
        },

        calculateCampaignAudience() {
            fetch('{{ route('admin.email.audience.preview') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    audience_type: this.campaignData.audience_type,
                    filters: {
                        programme: this.campaignData.programme_filter,
                        subject_id: this.campaignData.subject_filter,
                        class_id: this.campaignData.class_filter
                    }
                })
            })
            .then(res => res.json())
            .then(data => {
                this.campaignAudienceCount = data.count;
            });
        },

        inspectAudience(audKey) {
            this.selectedAudienceKey = audKey;
            this.isAudienceLoading = true;
            fetch('{{ route('admin.email.audience.preview') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ audience_type: audKey })
            })
            .then(res => res.json())
            .then(data => {
                this.audiencePreviewCount = data.count;
                this.audienceSample = data.sample || [];
                this.isAudienceLoading = false;
            })
            .catch(() => { this.isAudienceLoading = false; });
        },

        testAutomationModal(auto) {
            const adminEmail = prompt(`Test Fire '${auto.name}':\nEnter destination email for test trigger:`, '{{ auth()->user()?->email ?? 'admin@olympiascholars.my' }}');
            if (adminEmail) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ url('admin/email-management/automations') }}/' + auto.id + '/test';
                form.innerHTML = `
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="test_email" value="${adminEmail}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        },

        editAutomationModal(auto) {
            const tplId = prompt(`Select template ID for automation '${auto.name}':`, auto.email_template_id || '');
            if (tplId) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ url('admin/email-management/automations') }}/' + auto.id;
                form.innerHTML = `
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="email_template_id" value="${tplId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        },

        viewLogDetails(log) {
            this.selectedLog = log;
            this.showLogDetailsModal = true;
        },

        initCharts() {
            // Chart 1: 7-day Sending Trends
            const ctxTrends = document.getElementById('emailTrendsChart');
            if (ctxTrends && !ctxTrends._chartInst) {
                ctxTrends._chartInst = new Chart(ctxTrends, {
                    type: 'line',
                    data: {
                        labels: @json($emailTrendsLabels),
                        datasets: [
                            {
                                label: 'Sent & Delivered',
                                data: @json($emailTrendsSent),
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                fill: true,
                                tension: 0.35,
                                pointRadius: 4,
                            },
                            {
                                label: 'Failed',
                                data: @json($emailTrendsFailed),
                                borderColor: '#f43f5e',
                                backgroundColor: 'rgba(244, 63, 94, 0.1)',
                                fill: true,
                                tension: 0.35,
                                pointRadius: 4,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } }
                        },
                        scales: {
                            y: { beginAtZero: true, ticks: { precision: 0 } }
                        }
                    }
                });
            }

            // Chart 2: Category Volume Doughnut
            const ctxCat = document.getElementById('emailCategoryChart');
            if (ctxCat && !ctxCat._chartInst) {
                ctxCat._chartInst = new Chart(ctxCat, {
                    type: 'doughnut',
                    data: {
                        labels: @json($emailCategoryLabels),
                        datasets: [{
                            data: @json($emailCategoryData),
                            backgroundColor: ['#3b82f6', '#10b981', '#f43f5e', '#6366f1', '#a855f7', '#f59e0b', '#06b6d4', '#64748b'],
                            borderWidth: 2,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        cutout: '68%',
                    }
                });
            }
        }
    };
}
</script>
