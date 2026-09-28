@php
    $me       = auth()->user();
    $myId     = $me->id;
    $myRole   = $me->role;
    $myName   = $me->name;
    $isAdmin  = $me->isAdmin();
    $isParent = $me->isParent();

    $urls = [
        'unread'          => route('chat.unread-count'),
        'contacts'        => route('chat.contacts'),
        'directory'       => route('chat.directory'),
        'findOrCreate'    => route('chat.conversations.find-or-create'),
        'messagesBase'    => url('chat/conversations'),
        'clearAll'        => route('chat.conversations.clear-all'),
        'monitorChildren' => $isParent ? route('chat.monitor.children') : null,
        'monitorConvBase' => $isParent ? url('chat/monitor/children') : null,
        'adminContacts'   => $isAdmin  ? route('chat.admin.contacts') : null,
        'adminConvBase'   => $isAdmin  ? url('chat/admin/conversations') : null,
        'adminUserContext'=> $isAdmin  ? url('chat/admin/user-context') : null,
        'csrf'            => csrf_token(),
    ];
@endphp

{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
{{-- OLYMPIA SCHOLARS FLOATING CHATBOX WIDGET                                    --}}
{{-- Fully responsive, role-aware, zero-emoji, blue-tick verified architecture   --}}
{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
<div id="chat-widget"
     x-data="OlympiaChatWidget()"
     x-init="init()"
     class="fixed bottom-6 right-6 z-[9999] font-sans select-none print:hidden">

    {{-- ── CHAT POPUP WINDOW ─────────────────────────────────────────────── --}}
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0 scale-90 translate-y-6"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-90 translate-y-6"
         class="absolute bottom-16 right-0 w-[400px] max-w-[calc(100vw-2rem)] bg-white rounded-3xl shadow-[0_12px_48px_rgba(30,64,175,0.22)] border border-slate-100 flex flex-col overflow-hidden"
         style="height: 590px;">

        {{-- ── CLEAR ALL CHATS CONFIRMATION MODAL ── --}}
        <div x-show="showClearAllModal" x-cloak
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="absolute inset-0 bg-slate-900/60 backdrop-blur-[2px] flex items-center justify-center p-4 z-50">
            <div @click.away="if (!clearingAll) showClearAllModal = false"
                 class="bg-white rounded-3xl p-5 max-w-[280px] w-full text-center shadow-2xl border border-slate-100 transform transition-all">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto mb-3 shadow-inner">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <h4 class="text-sm font-bold text-slate-800 mb-1">Clear all chats?</h4>
                <p class="text-[11px] text-slate-500 mb-4 leading-relaxed">
                    This will remove all conversation blocks and message history. This action cannot be undone.
                </p>
                <div class="flex items-center gap-2">
                    <button @click="showClearAllModal = false"
                            :disabled="clearingAll"
                            class="flex-1 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-full transition-colors">
                        Cancel
                    </button>
                    <button @click="confirmClearAllChats()"
                            :disabled="clearingAll"
                            class="flex-1 py-2 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 disabled:opacity-50 rounded-full transition-colors flex items-center justify-center gap-1.5 shadow-md shadow-rose-600/20">
                        <svg x-show="clearingAll" class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                        <span x-text="clearingAll ? 'Clearing...' : 'Clear All'"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ── DELETE SINGLE CONVERSATION CONFIRMATION MODAL ── --}}
        <div x-show="showDeleteConvModal" x-cloak
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="absolute inset-0 bg-slate-900/60 backdrop-blur-[2px] flex items-center justify-center p-4 z-50">
            <div @click.away="if (!deletingConv) showDeleteConvModal = false"
                 class="bg-white rounded-3xl p-5 max-w-[280px] w-full text-center shadow-2xl border border-slate-100 transform transition-all">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto mb-3 shadow-inner">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <h4 class="text-sm font-bold text-slate-800 mb-1">Delete chat?</h4>
                <p class="text-[11px] text-slate-500 mb-4 leading-relaxed">
                    Delete <span class="font-semibold text-slate-700" x-text="targetDeleteContact?.contact_name || 'this conversation'"></span>? The chat and all messages will be removed.
                </p>
                <div class="flex items-center gap-2">
                    <button @click="showDeleteConvModal = false"
                            :disabled="deletingConv"
                            class="flex-1 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-full transition-colors">
                        Cancel
                    </button>
                    <button @click="confirmDeleteConversation()"
                            :disabled="deletingConv"
                            class="flex-1 py-2 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 disabled:opacity-50 rounded-full transition-colors flex items-center justify-center gap-1.5 shadow-md shadow-rose-600/20">
                        <svg x-show="deletingConv" class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                        <span x-text="deletingConv ? 'Deleting...' : 'Delete'"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ── CLEAR CONVERSATION MESSAGES MODAL ── --}}
        <div x-show="showClearModal" x-cloak
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="absolute inset-0 bg-slate-900/60 backdrop-blur-[2px] flex items-center justify-center p-4 z-50">
            <div @click.away="if (!clearingChat) showClearModal = false"
                 class="bg-white rounded-3xl p-5 max-w-[270px] w-full text-center shadow-2xl border border-slate-100 transform transition-all">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto mb-3 shadow-inner">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <h4 class="text-sm font-bold text-slate-800 mb-1">Clear messages?</h4>
                <p class="text-[11px] text-slate-500 mb-4 leading-relaxed">
                    Messages in this conversation will be permanently removed.
                </p>
                <div class="flex items-center gap-2">
                    <button @click="showClearModal = false"
                            :disabled="clearingChat"
                            class="flex-1 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-full transition-colors">
                        Cancel
                    </button>
                    <button @click="confirmClearChat()"
                            :disabled="clearingChat"
                            class="flex-1 py-2 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 disabled:opacity-50 rounded-full transition-colors flex items-center justify-center gap-1.5 shadow-md shadow-rose-600/20">
                        <svg x-show="clearingChat" class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                        <span x-text="clearingChat ? 'Clearing...' : 'Clear'"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- VIEW 1: CONTACTS & CONVERSATIONS                                    --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        <div x-show="view === 'contacts'" class="flex flex-col h-full bg-slate-50/50">

            {{-- Header --}}
            <div class="@if($isAdmin) bg-slate-900 border-b border-slate-800 @else bg-gradient-to-r from-primary to-primary-700 @endif px-4 py-3.5 flex items-center justify-between flex-shrink-0 text-white">
                <div class="flex items-center gap-3">
                    @if($isAdmin)
                        {{-- Admin: Professional Shield Badge --}}
                        <div class="w-9 h-9 rounded-xl bg-blue-600/30 border border-blue-400/40 flex items-center justify-center text-blue-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-heading font-bold text-sm tracking-wide text-white">Support & Dispatch</p>
                            <p class="text-[10px] text-slate-400 font-medium">Olympia Administrative Console</p>
                        </div>
                    @else
                        {{-- Student / Teacher / Parent Header --}}
                        <div class="w-9 h-9 rounded-2xl bg-white/15 backdrop-blur-md flex items-center justify-center text-white border border-white/20">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-heading font-bold text-sm tracking-wide text-white">Olympia Messages</p>
                            <p class="text-[10px] text-blue-100/80 font-medium capitalize">{{ $myRole }} • {{ $myName }}</p>
                        </div>
                    @endif
                </div>

                {{-- Action Controls --}}
                <div class="flex items-center gap-1">
                    {{-- Start New Chat (Compose) --}}
                    <button @click="openDirectoryView()"
                            title="New Conversation"
                            class="p-1.5 text-white/80 hover:text-white rounded-xl hover:bg-white/15 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </button>

                    {{-- Refresh --}}
                    <button @click="loadContacts()"
                            title="Refresh List"
                            :class="loadingContacts ? 'animate-spin' : ''"
                            class="p-1.5 text-white/80 hover:text-white rounded-xl hover:bg-white/15 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </button>

                    {{-- Clear All Chats (Clears messages and chat blocks) --}}
                    <button @click="promptClearAllChats()"
                            title="Clear All Chats"
                            class="p-1.5 text-white/80 hover:text-red-300 hover:bg-white/15 rounded-xl transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>

                    {{-- Close Widget --}}
                    <button @click="open = false"
                            title="Close"
                            class="p-1.5 text-white/80 hover:text-white rounded-xl hover:bg-white/15 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- ── PARENT TABS: My Chats / Monitor Child ── --}}
            @if($isParent)
            <div class="flex border-b border-slate-200/80 bg-white flex-shrink-0 text-xs font-semibold">
                <button @click="activeTab = 'direct'; loadContacts()"
                        :class="activeTab === 'direct' ? 'border-b-2 border-primary text-primary bg-blue-50/40' : 'text-slate-500 hover:text-slate-700'"
                        class="flex-1 py-2.5 transition-colors flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                    My Chats
                </button>
                <button @click="activeTab = 'monitor'; loadMonitorChildren()"
                        :class="activeTab === 'monitor' ? 'border-b-2 border-primary text-primary bg-blue-50/40' : 'text-slate-500 hover:text-slate-700'"
                        class="flex-1 py-2.5 transition-colors flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Monitor Child
                </button>
            </div>
            @endif

            {{-- ── ADMIN SYSTEMATIC CONTROLS ── --}}
            @if($isAdmin)
            <div class="p-2.5 bg-slate-900 border-b border-slate-800 space-y-2 flex-shrink-0">
                {{-- Role Filter Pills --}}
                <div class="flex items-center gap-1 text-[11px] font-medium overflow-x-auto pb-0.5">
                    <button @click="adminRoleFilter = ''; loadContacts()"
                            :class="adminRoleFilter === '' ? 'bg-primary text-white shadow-sm' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'"
                            class="px-2.5 py-1 rounded-lg transition-all">All</button>
                    <button @click="adminRoleFilter = 'student'; loadContacts()"
                            :class="adminRoleFilter === 'student' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'"
                            class="px-2.5 py-1 rounded-lg transition-all">Students</button>
                    <button @click="adminRoleFilter = 'teacher'; loadContacts()"
                            :class="adminRoleFilter === 'teacher' ? 'bg-primary text-white shadow-sm' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'"
                            class="px-2.5 py-1 rounded-lg transition-all">Teachers</button>
                    <button @click="adminRoleFilter = 'parent'; loadContacts()"
                            :class="adminRoleFilter === 'parent' ? 'bg-amber-600 text-white shadow-sm' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'"
                            class="px-2.5 py-1 rounded-lg transition-all">Parents</button>
                    <button @click="adminRoleFilter = 'support'; loadContacts()"
                            :class="adminRoleFilter === 'support' ? 'bg-rose-600 text-white shadow-sm' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'"
                            class="px-2.5 py-1 rounded-lg transition-all">Support</button>
                </div>

                {{-- Search & Status Filter Row --}}
                <div class="flex items-center gap-2">
                    <div class="flex-1 relative">
                        <svg class="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input x-model="adminSearch" @input.debounce.300ms="loadContacts()"
                               type="text" placeholder="Search conversations..."
                               class="w-full pl-8 pr-3 py-1.5 text-xs bg-slate-800 border border-slate-700 rounded-xl text-slate-100 placeholder-slate-400 focus:outline-none focus:border-primary">
                    </div>
                    <select x-model="adminStatusFilter" @change="loadContacts()"
                            class="text-xs bg-slate-800 border border-slate-700 text-slate-300 rounded-xl px-2 py-1.5 focus:outline-none focus:border-primary">
                        <option value="">All Status</option>
                        <option value="open">Open</option>
                        <option value="pending">Pending</option>
                        <option value="resolved">Resolved</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
            </div>
            @endif

            {{-- ── SEARCH BAR FOR STANDARD USERS ── --}}
            @if(!$isAdmin)
            <div x-show="activeTab === 'direct'" class="p-2.5 border-b border-slate-100 bg-white flex-shrink-0">
                <div class="relative">
                    <svg class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input x-model="contactSearch"
                           type="text" placeholder="Filter conversations..."
                           class="w-full pl-9 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:border-primary focus:bg-white transition-all">
                </div>
            </div>
            @endif

            {{-- ── CONVERSATION LIST STREAM ── --}}
            <div x-show="activeTab === 'direct' || activeTab === 'admin'" class="flex-1 overflow-y-auto divide-y divide-slate-100">

                {{-- Loading Spinner --}}
                <div x-show="loadingContacts" class="flex items-center justify-center py-12">
                    <svg class="w-6 h-6 animate-spin text-primary" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                </div>

                {{-- Empty State --}}
                <div x-show="!loadingContacts && filteredContacts.length === 0" class="flex flex-col items-center justify-center py-14 text-center px-6">
                    <div class="w-14 h-14 bg-blue-50 text-primary rounded-3xl flex items-center justify-center mb-3 shadow-inner">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-semibold text-slate-800 mb-1">No Active Conversations</p>
                    <p class="text-xs text-slate-400 mb-4 max-w-[240px]">Start a conversation with enrolled teachers or school support.</p>
                    <button @click="openDirectoryView()"
                            class="px-4 py-2 bg-primary text-white text-xs font-semibold rounded-full shadow-md hover:bg-primary-700 transition-all flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Start New Chat
                    </button>
                </div>

                {{-- List Items --}}
                <template x-for="c in filteredContacts" :key="c.conversation_id || c.contact_id">
                    <div class="relative group/block flex items-center hover:bg-white transition-colors">
                        <button @click="openConversation(c)"
                                class="w-full flex items-center gap-3 px-4 py-3 text-left min-w-0 pr-10">

                            {{-- Role-Tailored Initials Avatar --}}
                            <div class="relative flex-shrink-0">
                                <div class="w-10 h-10 rounded-2xl flex items-center justify-center text-white text-xs font-bold shadow-sm transition-transform group-hover/block:scale-105"
                                     :class="{
                                        'bg-primary':     c.contact_role === 'teacher',
                                        'bg-emerald-600': c.contact_role === 'student',
                                        'bg-amber-600':   c.contact_role === 'parent',
                                        'bg-slate-800':   c.contact_role === 'admin',
                                        'bg-blue-600':    !c.contact_role || c.contact_role === 'support',
                                     }"
                                     x-text="c.contact_initials || '?'"></div>

                                {{-- Unread Badge --}}
                                <span x-show="(c.unread || 0) > 0"
                                      x-text="c.unread"
                                      class="absolute -top-1 -right-1 min-w-[18px] h-4 bg-red-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center px-1 shadow-sm ring-2 ring-white"></span>
                            </div>

                            {{-- Contact Details --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-0.5">
                                    <span class="text-xs font-semibold text-slate-800 truncate" x-text="c.contact_name"></span>
                                    <span class="text-[9px] text-slate-400 font-medium ml-1 flex-shrink-0" x-text="c.last_time || ''"></span>
                                </div>

                                {{-- Context chip (Course / Role) --}}
                                <div class="flex items-center gap-1.5 mb-0.5">
                                    <template x-if="c.contact_role">
                                        <span class="text-[9px] px-1.5 py-0.5 rounded font-semibold uppercase tracking-wider"
                                              :class="{
                                                'bg-blue-100 text-blue-700': c.contact_role === 'teacher',
                                                'bg-emerald-100 text-emerald-700': c.contact_role === 'student',
                                                'bg-amber-100 text-amber-700': c.contact_role === 'parent',
                                                'bg-slate-100 text-slate-700': c.contact_role === 'admin',
                                              }"
                                              x-text="c.contact_role"></span>
                                    </template>

                                    <template x-if="c.context_label">
                                        <span class="text-[9px] text-slate-500 truncate" x-text="c.context_label"></span>
                                    </template>

                                    {{-- Status badge for admin/support --}}
                                    <template x-if="c.status && c.status !== 'open'">
                                        <span class="text-[8px] px-1.5 py-0.2 rounded font-bold uppercase ml-auto"
                                              :class="{
                                                'bg-amber-100 text-amber-700': c.status === 'pending',
                                                'bg-emerald-100 text-emerald-700': c.status === 'resolved',
                                                'bg-slate-200 text-slate-600': c.status === 'closed',
                                              }"
                                              x-text="c.status"></span>
                                    </template>
                                </div>

                                <p class="text-[11px] text-slate-400 truncate" x-text="c.last_message || 'No messages yet'"></p>
                            </div>
                        </button>

                        {{-- Individual Chat Block Delete Button (visible on hover) --}}
                        <template x-if="c.conversation_id">
                            <button @click.stop="promptDeleteConversation(c)"
                                    title="Delete chat"
                                    class="absolute right-2.5 top-1/2 -translate-y-1/2 p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl opacity-0 group-hover/block:opacity-100 focus:opacity-100 transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </template>
                    </div>
                </template>
            </div>

            {{-- ── PARENT: MONITOR CHILD TAB ── --}}
            @if($isParent)
            <div x-show="activeTab === 'monitor'" class="flex-1 overflow-y-auto">

                {{-- Step 1: Select Child --}}
                <div x-show="!monitorSelectedChild" class="p-4 space-y-3">
                    <div class="flex items-center gap-2 text-xs font-semibold text-slate-700">
                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        Select Child to Monitor
                    </div>

                    <div x-show="loadingMonitor" class="flex justify-center py-6">
                        <svg class="w-5 h-5 animate-spin text-primary" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                    </div>

                    <div class="space-y-2">
                        <template x-for="child in monitorChildren" :key="child.student_id">
                            <button @click="selectMonitorChild(child)"
                                    class="w-full flex items-center gap-3 p-3.5 bg-white hover:bg-blue-50/50 border border-slate-200/80 hover:border-primary/40 rounded-2xl transition-all shadow-sm group">
                                <div class="w-10 h-10 rounded-2xl bg-emerald-600 flex items-center justify-center text-white text-xs font-bold"
                                     x-text="child.initials"></div>
                                <div class="text-left flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-slate-800 truncate" x-text="child.name"></p>
                                    <p class="text-[10px] text-slate-400" x-text="child.level || 'Student'"></p>
                                </div>
                                <svg class="w-4 h-4 text-slate-300 group-hover:text-primary group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Step 2: Child's Conversation List --}}
                <div x-show="monitorSelectedChild" class="flex flex-col h-full">
                    {{-- Selected Child Header --}}
                    <div class="flex items-center gap-2 px-3 py-2.5 border-b border-amber-200/60 bg-amber-50/70 flex-shrink-0">
                        <button @click="monitorSelectedChild = null; monitorConversations = []"
                                title="Back to children list"
                                class="p-1 text-amber-700 hover:text-amber-900 rounded-lg hover:bg-amber-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                        <div class="w-7 h-7 rounded-xl bg-emerald-600 flex items-center justify-center text-white text-[10px] font-bold" x-text="monitorSelectedChild?.initials"></div>
                        <div>
                            <p class="text-xs font-bold text-amber-900" x-text="monitorSelectedChild?.name"></p>
                            <p class="text-[9px] text-amber-700 font-medium flex items-center gap-1">
                                <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                Parental Audit Stream &bull; Read Only
                            </p>
                        </div>
                    </div>

                    {{-- Child's chats --}}
                    <div class="flex-1 overflow-y-auto divide-y divide-slate-100">
                        <template x-for="c in monitorConversations" :key="c.conversation_id">
                            <button @click="openMonitorConversation(c)"
                                    class="w-full flex items-center gap-3 px-4 py-3 hover:bg-white text-left transition-colors">
                                <div class="w-10 h-10 bg-primary rounded-2xl flex items-center justify-center text-white text-xs font-bold"
                                     x-text="c.contact_initials || '?'"></div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <p class="text-xs font-semibold text-slate-800 truncate" x-text="c.contact_name"></p>
                                        <span class="text-[9px] text-slate-400" x-text="c.last_time || ''"></span>
                                    </div>
                                    <p class="text-[11px] text-slate-400 truncate mt-0.5" x-text="c.last_message || 'No messages yet'"></p>
                                </div>
                            </button>
                        </template>

                        <div x-show="monitorConversations.length === 0 && !loadingMonitor" class="text-center py-12 px-6">
                            <p class="text-xs font-semibold text-slate-600">No conversations found</p>
                            <p class="text-[10px] text-slate-400 mt-1">This child has not started any conversations yet.</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- VIEW 2: NEW CHAT DIRECTORY (Start conversation)                    --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        <div x-show="view === 'directory'" class="flex flex-col h-full bg-slate-50/50">
            {{-- Header --}}
            <div class="@if($isAdmin) bg-slate-900 border-b border-slate-800 @else bg-gradient-to-r from-primary to-primary-700 @endif px-4 py-3.5 flex items-center gap-3 text-white flex-shrink-0">
                <button @click="view = 'contacts'" class="p-1 hover:bg-white/15 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <div>
                    <p class="font-heading font-bold text-sm tracking-wide">New Conversation</p>
                    <p class="text-[10px] text-blue-100/80">Select an authorized contact</p>
                </div>
            </div>

            {{-- Directory Search --}}
            <div class="p-2.5 border-b border-slate-100 bg-white flex-shrink-0">
                <div class="relative">
                    <svg class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input x-model="directorySearch"
                           type="text" placeholder="Search by name..."
                           class="w-full pl-9 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:border-primary focus:bg-white transition-all">
                </div>
            </div>

            {{-- Directory Contact List --}}
            <div class="flex-1 overflow-y-auto divide-y divide-slate-100">
                <div x-show="loadingDirectory" class="flex justify-center py-10">
                    <svg class="w-6 h-6 animate-spin text-primary" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                </div>

                <template x-for="item in filteredDirectory" :key="item.contact_id">
                    <button @click="startNewConversationWith(item)"
                            class="w-full flex items-center gap-3 px-4 py-3 hover:bg-white text-left transition-colors group">
                        <div class="w-10 h-10 rounded-2xl flex items-center justify-center text-white text-xs font-bold shadow-sm"
                             :class="{
                                'bg-primary':     item.contact_role === 'teacher',
                                'bg-emerald-600': item.contact_role === 'student',
                                'bg-amber-600':   item.contact_role === 'parent',
                                'bg-slate-800':   item.contact_role === 'admin',
                             }"
                             x-text="item.contact_initials || '?'"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-slate-800 truncate" x-text="item.contact_name"></p>
                            <p class="text-[10px] text-slate-400 truncate" x-text="item.context_label || item.contact_role"></p>
                        </div>
                        <svg class="w-4 h-4 text-slate-300 group-hover:text-primary transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </template>

                <div x-show="!loadingDirectory && filteredDirectory.length === 0" class="text-center py-10 text-xs text-slate-400">
                    No contacts found matching your search.
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- VIEW 3: ACTIVE CONVERSATION                                         --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        <div x-show="view === 'chat'" class="flex flex-col h-full bg-slate-50 relative">

            {{-- Chat Header --}}
            <div class="@if($isAdmin) bg-slate-900 border-b border-slate-800 @else bg-gradient-to-r from-primary to-primary-700 @endif px-3.5 py-3 flex items-center gap-2.5 flex-shrink-0 text-white shadow-sm">
                {{-- Back to Contacts --}}
                <button @click="backToContacts()" class="p-1 hover:bg-white/15 rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>

                {{-- Contact Avatar --}}
                <div class="w-8 h-8 rounded-xl flex items-center justify-center text-white text-xs font-bold flex-shrink-0 shadow-inner"
                     :class="{
                        'bg-white/20':     !activeContact?.contact_role || activeContact?.contact_role === 'teacher',
                        'bg-emerald-500': activeContact?.contact_role === 'student',
                        'bg-amber-500':   activeContact?.contact_role === 'parent',
                        'bg-slate-700':   activeContact?.contact_role === 'admin',
                     }"
                     x-text="activeContact?.contact_initials || '?'"></div>

                {{-- Contact Info --}}
                <div class="flex-1 min-w-0">
                    <p class="text-white text-xs font-bold truncate leading-tight" x-text="activeContact?.contact_name || 'Conversation'"></p>
                    <p class="text-[10px] text-blue-100/80 truncate capitalize">
                        <span x-text="activeContact?.contact_role || 'User'"></span>
                        <template x-if="activeContact?.context_label">
                            <span x-text="' &bull; ' + activeContact.context_label"></span>
                        </template>
                    </p>
                </div>

                {{-- Admin Status Dropdown --}}
                @if($isAdmin)
                <div x-show="!monitoringMode" class="flex items-center gap-1">
                    <select x-model="activeConvStatus"
                            @change="updateConvStatus($event.target.value)"
                            class="text-[10px] font-semibold bg-slate-800 text-slate-200 border border-slate-700 rounded-lg px-2 py-1 focus:outline-none focus:border-primary">
                        <option value="open">Open</option>
                        <option value="pending">Pending</option>
                        <option value="resolved">Resolved</option>
                        <option value="closed">Closed</option>
                    </select>

                    {{-- Admin Info Drawer Toggle --}}
                    <button @click="toggleAdminDrawer()"
                            title="Inspect User Details"
                            class="p-1 text-slate-300 hover:text-white rounded-lg hover:bg-slate-800 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </button>
                </div>
                @endif

                {{-- Options Menu (Clear Chat, etc.) --}}
                <div x-show="!monitoringMode" class="relative flex items-center" x-data="{ optionsOpen: false }">
                    <button @click="optionsOpen = !optionsOpen"
                            title="Conversation options"
                            class="p-1 hover:bg-white/15 rounded-xl transition-colors text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                        </svg>
                    </button>

                    <div x-show="optionsOpen"
                         @click.away="optionsOpen = false"
                         x-cloak
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="absolute right-0 top-full mt-1.5 w-44 bg-white rounded-2xl shadow-xl border border-slate-100 py-1 z-50 text-slate-700 overflow-hidden divide-y divide-slate-100">
                        <button @click="optionsOpen = false; promptClearChat()"
                                class="w-full px-3 py-2 text-left text-xs font-medium text-slate-700 hover:bg-slate-50 flex items-center gap-2 transition-colors">
                            <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Clear Messages
                        </button>
                        <button @click="optionsOpen = false; promptDeleteConversation(activeContact)"
                                class="w-full px-3 py-2 text-left text-xs font-semibold text-rose-600 hover:bg-rose-50 flex items-center gap-2 transition-colors">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete Chat
                        </button>
                    </div>
                </div>

                {{-- Close Button --}}
                <button @click="open = false" class="p-1 hover:bg-white/15 rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- ── ADMIN EXPANDABLE USER CONTEXT DRAWER (FOOLPROOF & SYSTEMATIC) ── --}}
            @if($isAdmin)
            <div x-show="showAdminDrawer" x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="bg-slate-900 border-b border-slate-800 p-3 text-slate-200 text-xs space-y-2 flex-shrink-0">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-[11px] text-blue-400 uppercase tracking-wider">User Dossier</span>
                    <button @click="showAdminDrawer = false" class="text-slate-400 hover:text-white text-[10px]">Close</button>
                </div>

                <div x-show="loadingAdminContext" class="py-2 text-center text-slate-400 text-xs">Loading context...</div>

                <div x-show="!loadingAdminContext && adminUserContext" class="space-y-1.5 text-[11px]">
                    <div class="grid grid-cols-2 gap-2 bg-slate-800/80 p-2 rounded-xl">
                        <div>
                            <span class="text-slate-400 text-[10px] block">Email</span>
                            <span class="font-medium truncate block text-slate-200" x-text="adminUserContext?.email || 'N/A'"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[10px] block">Role / Active</span>
                            <span class="font-medium capitalize text-slate-200" x-text="(adminUserContext?.role || '') + (adminUserContext?.active ? ' (Active)' : ' (Inactive)')"></span>
                        </div>
                    </div>

                    {{-- Student Context Details --}}
                    <template x-if="adminUserContext?.student_details">
                        <div class="bg-slate-800/80 p-2 rounded-xl space-y-1">
                            <div class="flex justify-between text-[10px] text-slate-400">
                                <span>Level: <b class="text-slate-200" x-text="adminUserContext.student_details.level"></b></span>
                                <span>School: <b class="text-slate-200" x-text="adminUserContext.student_details.school"></b></span>
                            </div>
                            <div class="pt-1">
                                <span class="text-[9px] uppercase font-bold text-slate-400 block mb-1">Enrolled Courses</span>
                                <div class="space-y-1 max-h-20 overflow-y-auto">
                                    <template x-for="c in adminUserContext.student_details.courses" :key="c.title">
                                        <div class="flex justify-between text-[10px] bg-slate-900/60 px-2 py-1 rounded">
                                            <span class="truncate text-slate-300" x-text="c.title"></span>
                                            <span class="text-blue-400 font-semibold" x-text="c.progress"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            @endif

            {{-- ── PARENT MONITORING READ-ONLY BANNER ── --}}
            <div x-show="monitoringMode"
                 class="bg-amber-50 border-b border-amber-200/80 px-3.5 py-2.5 flex items-center gap-2.5 flex-shrink-0 text-amber-900">
                <div class="w-6 h-6 rounded-lg bg-amber-100 flex items-center justify-center text-amber-600 flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[11px] font-bold leading-tight">
                        Auditing: <span x-text="monitorChildName"></span> &bull; <span x-text="activeContact?.contact_name"></span>
                    </p>
                    <p class="text-[10px] text-amber-700 leading-tight">Parental Monitoring Mode (Read-Only). Every action is logged for child safety.</p>
                </div>
            </div>

            {{-- ── LOAD EARLIER MESSAGES TRIGGER ── --}}
            <div x-show="hasOlderMessages" class="py-2 text-center flex-shrink-0">
                <button @click="loadOlderMessages()"
                        :disabled="loadingOlder"
                        class="text-[10px] font-semibold text-primary hover:text-primary-700 transition-colors inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white border border-slate-200 shadow-sm">
                    <svg x-show="loadingOlder" class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                    <span x-text="loadingOlder ? 'Loading older...' : 'Load earlier messages'"></span>
                </button>
            </div>

            {{-- ── MESSAGE FEED CONTAINER ── --}}
            <div id="chat-msg-area" class="flex-1 overflow-y-auto px-3.5 py-3 space-y-3 min-h-0">

                {{-- Initial Spinner --}}
                <div x-show="loadingMessages" class="flex items-center justify-center py-12">
                    <svg class="w-6 h-6 animate-spin text-primary" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                </div>

                {{-- Empty Chat placeholder --}}
                <div x-show="!loadingMessages && messages.length === 0" class="flex flex-col items-center justify-center py-16 text-center text-slate-400">
                    <div class="w-12 h-12 bg-blue-50 text-primary rounded-2xl flex items-center justify-center mb-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <p class="text-xs font-semibold text-slate-600">Start the conversation</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">Send a message below to connect.</p>
                </div>

                {{-- Messages container (managed by Alpine/JS for delta updates & auto-scroll) --}}
                <div id="chat-messages-inner" class="space-y-2.5"></div>

                {{-- Typing Indicator --}}
                <div x-show="typingText" class="flex items-end gap-2 pt-1">
                    <div class="w-7 h-7 rounded-xl bg-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-600 flex-shrink-0"
                         x-text="activeContact?.contact_initials || '?'"></div>
                    <div class="bg-white border border-slate-200 rounded-2xl rounded-tl-sm px-3 py-2 shadow-sm">
                        <div class="flex items-center gap-1">
                            <div class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                            <div class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                            <div class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── FOOTER: INPUT BAR (Standard Chat) ── --}}
            <div x-show="!monitoringMode" class="p-3 bg-white border-t border-slate-200/80 flex-shrink-0">
                <div class="flex items-center gap-2">

                    {{-- Attachment Button --}}
                    <label title="Attach File (Image, PDF, Document)"
                           class="w-[38px] h-[38px] rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center cursor-pointer transition-colors flex-shrink-0">
                        <input type="file"
                               @change="handleFileUpload($event)"
                               accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx,.xlsx"
                               class="hidden">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                        </svg>
                    </label>

                    {{-- Message Textarea Capsule --}}
                    <div class="flex-1 relative flex items-center">
                        <textarea id="chat-input"
                                  x-model="inputText"
                                  @keydown="handleKeydown($event)"
                                  @input="onTyping()"
                                  placeholder="Type your message..."
                                  rows="1"
                                  class="w-full resize-none block min-h-[38px] rounded-full border border-slate-200 bg-slate-50/70 px-4 py-2 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-primary focus:bg-white focus:ring-2 focus:ring-primary/20 max-h-24 transition-all leading-normal"></textarea>
                    </div>

                    {{-- Send Button --}}
                    <button @click="sendMessage()"
                            :disabled="!inputText.trim() || sendingMessage"
                            title="Send message (Enter)"
                            class="w-[38px] h-[38px] rounded-full bg-primary hover:bg-primary-700 disabled:bg-slate-200 disabled:cursor-not-allowed text-white flex items-center justify-center shadow-md transition-all flex-shrink-0 focus:outline-none">
                        <svg x-show="!sendingMessage" class="w-4 h-4 -translate-x-px translate-y-px text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m22 2-7 20-4-9-9-4Z"/>
                            <path d="M22 2 11 13"/>
                        </svg>
                        <svg x-show="sendingMessage" class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- ── FOOTER: LOCKED NOTICE (Parent Monitoring Mode) ── --}}
            <div x-show="monitoringMode"
                 class="p-3 bg-amber-50 border-t border-amber-200/80 flex items-center gap-2.5 text-amber-800 flex-shrink-0">
                <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <p class="text-[11px] font-medium leading-tight">
                    Direct messaging is disabled in Parental Monitoring Mode.
                </p>
            </div>
        </div>
    </div>

    {{-- ── FLOATING ACTION BUTTON (FAB) ──────────────────────────────────── --}}
    <button @click="toggleWidget()"
            id="chat-fab"
            title="Open Chatbox"
            class="@if($isAdmin) bg-slate-900 hover:bg-slate-800 text-white @else bg-primary hover:bg-primary-700 text-white @endif relative w-14 h-14 rounded-full shadow-[0_8px_30px_rgba(30,64,175,0.35)] flex items-center justify-center transition-all duration-200 hover:scale-105 active:scale-95 focus:outline-none">

        {{-- Chat Bubble Icon (Closed) --}}
        <svg x-show="!open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>

        {{-- Close Icon (Open) --}}
        <svg x-show="open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>

        {{-- Global Unread Badge --}}
        <span x-show="globalUnread > 0 && !open"
              x-text="globalUnread > 99 ? '99+' : globalUnread"
              class="absolute -top-1 -right-1 min-w-[20px] h-5 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center px-1.5 shadow-md ring-2 ring-white animate-pulse"></span>
    </button>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
{{-- ALPINE.JS CONTROLLER & CLIENT POLING ENGINE                                 --}}
{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
<script>
window.__CHAT_CONFIG__ = @json($urls);
window.__MY_ID__       = {{ $myId }};
window.__MY_ROLE__     = '{{ $myRole }}';
window.__IS_ADMIN__    = {{ $isAdmin ? 'true' : 'false' }};
window.__IS_PARENT__   = {{ $isParent ? 'true' : 'false' }};

function OlympiaChatWidget() {
    return {
        // Navigation & views
        open: false,
        view: 'contacts', // 'contacts' | 'directory' | 'chat'
        activeTab: 'direct', // 'direct' | 'monitor'

        // Contacts
        contacts: [],
        loadingContacts: false,
        contactSearch: '',

        // Directory (New Chat)
        directory: [],
        loadingDirectory: false,
        directorySearch: '',

        // Active chat state
        activeContact: null,
        activeConversationId: null,
        activeConvStatus: 'open',
        messages: [],
        nextCursor: 0,
        hasOlderMessages: false,
        loadingMessages: false,
        loadingOlder: false,
        sendingMessage: false,
        inputText: '',
        showClearModal: false,
        clearingChat: false,
        showClearAllModal: false,
        clearingAll: false,
        showDeleteConvModal: false,
        deletingConv: false,
        targetDeleteContact: null,

        // Blue tick & typing
        typingText: false,
        lastTypingSent: 0,

        // Parental Monitoring
        monitoringMode: false,
        monitorChildren: [],
        monitorSelectedChild: null,
        monitorConversations: [],
        monitorChildName: '',
        loadingMonitor: false,

        // Admin functionality
        adminRoleFilter: '',
        adminStatusFilter: '',
        adminSearch: '',
        showAdminDrawer: false,
        adminUserContext: null,
        loadingAdminContext: false,

        // Badge & polling
        globalUnread: 0,
        pollTimer: null,
        unreadTimer: null,

        // ── INIT ─────────────────────────────────────────────────────────────
        init() {
            this.loadContacts();
            this.startUnreadPolling();

            // Background pause on tab hide
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    this.stopPolling();
                } else {
                    if (this.view === 'chat' && this.activeConversationId) {
                        this.startPolling();
                    }
                    this.startUnreadPolling();
                }
            });
        },

        toggleWidget() {
            this.open = !this.open;
            if (this.open && this.contacts.length === 0) {
                this.loadContacts();
            }
        },

        // ── FILTERED COMPUTED PROPERTIES ────────────────────────────────────
        get filteredContacts() {
            if (!this.contactSearch.trim()) return this.contacts;
            const q = this.contactSearch.toLowerCase();
            return this.contacts.filter(c =>
                (c.contact_name && c.contact_name.toLowerCase().includes(q)) ||
                (c.last_message && c.last_message.toLowerCase().includes(q))
            );
        },

        get filteredDirectory() {
            if (!this.directorySearch.trim()) return this.directory;
            const q = this.directorySearch.toLowerCase();
            return this.directory.filter(d =>
                (d.contact_name && d.contact_name.toLowerCase().includes(q)) ||
                (d.context_label && d.context_label.toLowerCase().includes(q))
            );
        },

        // ── LOAD CONTACTS ───────────────────────────────────────────────────
        loadContacts() {
            this.loadingContacts = true;
            const cfg = window.__CHAT_CONFIG__;
            let url = cfg.contacts;

            if (window.__IS_ADMIN__) {
                const params = new URLSearchParams();
                if (this.adminSearch) params.set('search', this.adminSearch);
                if (this.adminStatusFilter) params.set('status', this.adminStatusFilter);
                if (this.adminRoleFilter) params.set('role', this.adminRoleFilter);
                url = cfg.adminContacts + '?' + params.toString();
            }

            fetch(url, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': cfg.csrf }
            })
            .then(r => r.json())
            .then(data => {
                this.contacts = Array.isArray(data) ? data : [];
            })
            .catch(() => {})
            .finally(() => { this.loadingContacts = false; });
        },

        // ── DIRECTORY & NEW CHAT ─────────────────────────────────────────────
        openDirectoryView() {
            this.view = 'directory';
            this.directorySearch = '';
            this.loadingDirectory = true;

            fetch(window.__CHAT_CONFIG__.directory, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': window.__CHAT_CONFIG__.csrf }
            })
            .then(r => r.json())
            .then(data => {
                this.directory = Array.isArray(data) ? data : [];
            })
            .catch(() => {})
            .finally(() => { this.loadingDirectory = false; });
        },

        startNewConversationWith(contact) {
            this.activeContact = contact;
            fetch(window.__CHAT_CONFIG__.findOrCreate, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.__CHAT_CONFIG__.csrf,
                },
                body: JSON.stringify({ contact_id: contact.contact_id || contact.id })
            })
            .then(r => r.json())
            .then(data => {
                if (data.conversation_id) {
                    contact.conversation_id = data.conversation_id;
                    this.openConversation(contact);
                }
            })
            .catch(() => {});
        },

        // ── OPEN CONVERSATION ────────────────────────────────────────────────
        openConversation(contact) {
            this.monitoringMode       = false;
            this.activeContact        = contact;
            this.view                 = 'chat';
            this.messages             = [];
            this.nextCursor           = 0;
            this.hasOlderMessages     = false;
            this.inputText            = '';
            this.showClearModal       = false;
            this.clearingChat         = false;
            this.showAdminDrawer      = false;
            this.adminUserContext     = null;
            this.activeConvStatus     = contact.status || 'open';
            this.activeConversationId = contact.conversation_id;

            this.loadMessages(true);
            this.startPolling();

            // Clear unread badge locally for this contact
            if (contact) contact.unread = 0;
        },

        backToContacts() {
            this.stopPolling();
            this.view = 'contacts';
            this.activeConversationId = null;
            this.showAdminDrawer = false;
            this.loadContacts();
        },

        // ── LOAD MESSAGES (DELTA POLLING) ───────────────────────────────────
        loadMessages(initial = false) {
            if (!this.activeConversationId) return;
            this.loadingMessages = initial;

            const since = initial ? 0 : this.nextCursor;
            const url   = `${window.__CHAT_CONFIG__.messagesBase}/${this.activeConversationId}/messages?since_id=${since}`;

            fetch(url, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': window.__CHAT_CONFIG__.csrf }
            })
            .then(r => r.json())
            .then(data => {
                if (!data.messages) return;

                if (initial) {
                    this.renderMessages(data.messages, true);
                    this.hasOlderMessages = data.has_more;
                } else if (data.messages.length > 0) {
                    this.appendMessages(data.messages);
                }

                if (data.next_cursor > this.nextCursor) {
                    this.nextCursor = data.next_cursor;
                }

                // If messages arrived, blue ticks might have updated for sent messages
                this.updateBlueTicks(data.messages, data.other_last_read_id || 0);
            })
            .catch(() => {})
            .finally(() => { this.loadingMessages = false; });
        },

        // ── LOAD OLDER MESSAGES ──────────────────────────────────────────────
        loadOlderMessages() {
            if (!this.activeConversationId || this.loadingOlder) return;
            this.loadingOlder = true;

            const firstId = this.messages[0]?.id ?? 0;
            const url     = `${window.__CHAT_CONFIG__.messagesBase}/${this.activeConversationId}/messages/older?before_id=${firstId}`;

            const area = document.getElementById('chat-msg-area');
            const prevScrollHeight = area ? area.scrollHeight : 0;

            fetch(url, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': window.__CHAT_CONFIG__.csrf }
            })
            .then(r => r.json())
            .then(data => {
                if (!data.messages || data.messages.length === 0) {
                    this.hasOlderMessages = false;
                    return;
                }
                this.messages = [...data.messages, ...this.messages];
                this.renderMessages(this.messages, false);
                this.hasOlderMessages = data.has_more;

                // Retain scroll position
                if (area) {
                    this.$nextTick(() => {
                        area.scrollTop = area.scrollHeight - prevScrollHeight;
                    });
                }
            })
            .catch(() => {})
            .finally(() => { this.loadingOlder = false; });
        },

        // ── RENDER DOM MESSAGES ──────────────────────────────────────────────
        renderMessages(msgs, scrollToBottom = false) {
            const container = document.getElementById('chat-messages-inner');
            if (!container) return;
            this.messages = msgs;
            container.innerHTML = '';

            let lastDate = null;
            msgs.forEach(m => {
                if (m.date !== lastDate) {
                    const sep = document.createElement('div');
                    sep.className = 'flex items-center gap-2 my-2.5';
                    sep.innerHTML = `
                        <div class="flex-1 h-px bg-slate-200"></div>
                        <span class="text-[9px] uppercase tracking-wider text-slate-400 font-semibold px-2">${this.formatDate(m.date)}</span>
                        <div class="flex-1 h-px bg-slate-200"></div>`;
                    container.appendChild(sep);
                    lastDate = m.date;
                }
                container.appendChild(this.buildMessageEl(m));
            });

            if (scrollToBottom) {
                this.scrollToBottom();
            }
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const area = document.getElementById('chat-msg-area');
                if (area) area.scrollTop = area.scrollHeight;
            });
            setTimeout(() => {
                const area = document.getElementById('chat-msg-area');
                if (area) area.scrollTop = area.scrollHeight;
            }, 60);
        },

        appendMessages(newMsgs) {
            const container = document.getElementById('chat-messages-inner');
            if (!container) return;
            const area = document.getElementById('chat-msg-area');
            const wasAtBottom = area ? (area.scrollHeight - area.scrollTop - area.clientHeight < 120) : true;

            newMsgs.forEach(m => {
                container.appendChild(this.buildMessageEl(m));
                this.messages.push(m);
            });

            if (wasAtBottom) {
                this.scrollToBottom();
            }
        },

        updateBlueTicks(incomingMsgs, otherLastReadId = 0) {
            this.messages.forEach(m => {
                if (m.mine) {
                    const isNowRead = m.tick_status === 'read' || (otherLastReadId && m.id <= otherLastReadId);
                    if (isNowRead && m.tick_status !== 'read') {
                        m.tick_status = 'read';
                    }
                    const el = document.querySelector(`[data-msg-id="${m.id}"] .tick-holder`);
                    if (el) {
                        el.innerHTML = this.renderTick(m.tick_status);
                    }
                }
            });
        },

        // ── BUILD MESSAGE ELEMENT ────────────────────────────────────────────
        buildMessageEl(m) {
            const wrap = document.createElement('div');
            wrap.dataset.msgId = m.id;
            wrap.className = `flex ${m.mine ? 'justify-end' : 'justify-start'} items-end gap-2 mb-1.5`;

            if (m.deleted) {
                wrap.innerHTML = `<span class="text-[10px] italic text-slate-400">Message was deleted</span>`;
                return wrap;
            }

            const timeHtml = `<span class="text-[9px] font-medium leading-none ${m.mine ? 'text-blue-100/90' : 'text-slate-400'}">${this.escHtml(m.time)}</span>`;
            const tickHtml = m.mine ? `<span class="tick-holder inline-flex items-center ml-1">${this.renderTick(m.tick_status)}</span>` : '';

            // Handle Attachments
            let attachmentHtml = '';
            if (m.attachments && m.attachments.length > 0) {
                m.attachments.forEach(a => {
                    if (a.is_image) {
                        attachmentHtml += `
                            <a href="${a.url}" target="_blank" class="block mb-1.5 rounded-xl overflow-hidden border border-white/20">
                                <img src="${a.url}" class="max-w-[220px] max-h-[160px] object-cover rounded-xl" alt="Attachment">
                            </a>`;
                    } else {
                        attachmentHtml += `
                            <a href="${a.url}" target="_blank" class="flex items-center gap-2 p-2 rounded-xl mb-1.5 ${m.mine ? 'bg-white/15 hover:bg-white/25 text-white' : 'bg-slate-200/80 hover:bg-slate-300 text-slate-800'} transition-colors">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <div class="min-w-0 flex-1">
                                    <p class="text-[11px] font-semibold truncate leading-tight">${this.escHtml(a.original_name)}</p>
                                    <p class="text-[9px] opacity-80 leading-tight">${a.human_size}</p>
                                </div>
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            </a>`;
                    }
                });
            }

            if (m.mine) {
                // Sent Message (Olympia Royal Blue Gradient)
                wrap.innerHTML = `
                    <div class="flex flex-col items-end max-w-[80%]">
                        <div class="bg-gradient-to-r from-primary to-primary-700 text-white rounded-3xl rounded-tr-sm px-3.5 py-2.5 text-xs leading-relaxed shadow-sm">
                            ${attachmentHtml}
                            ${m.body && m.type !== 'attachment' ? `<p class="break-words">${this.escHtml(m.body)}</p>` : ''}
                            <div class="flex items-center justify-end gap-1 mt-1">
                                ${timeHtml}
                                ${tickHtml}
                            </div>
                        </div>
                    </div>`;
            } else {
                // Received Message (Clean Card)
                wrap.innerHTML = `
                    <div class="w-7 h-7 rounded-xl bg-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-600 flex-shrink-0" title="${this.escHtml(m.sender_name || '')}">
                        ${this.escHtml(m.sender_initials || '?')}
                    </div>
                    <div class="max-w-[80%] flex flex-col items-start">
                        <div class="bg-white border border-slate-200/90 text-slate-800 rounded-3xl rounded-tl-sm px-3.5 py-2.5 text-xs leading-relaxed shadow-sm">
                            ${attachmentHtml}
                            ${m.body && m.type !== 'attachment' ? `<p class="break-words">${this.escHtml(m.body)}</p>` : ''}
                            <div class="flex items-center justify-end mt-1">
                                ${timeHtml}
                            </div>
                        </div>
                    </div>`;
            }

            return wrap;
        },

        // ── BLUE TICK RENDERER (WHATSAPP AUTHENTIC DOUBLE TICKS) ───────────
        renderTick(status) {
            if (!status) return '';

            // Double Blue Ticks (Read — WhatsApp vibrant sky blue double check)
            if (status === 'read') {
                return `
                    <svg class="w-4 h-3.5 inline-block text-sky-400 drop-shadow-sm" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6 7 17l-5-5"/>
                        <path d="m22 10-7.5 7.5L13 16"/>
                    </svg>`;
            }

            // Double Gray Ticks (Delivered — WhatsApp double check)
            if (status === 'delivered') {
                return `
                    <svg class="w-4 h-3.5 inline-block text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6 7 17l-5-5"/>
                        <path d="m22 10-7.5 7.5L13 16"/>
                    </svg>`;
            }

            // Single Gray Tick (Sent — WhatsApp single check)
            return `
                <svg class="w-3.5 h-3.5 inline-block text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 6 9 17l-5-5"/>
                </svg>`;
        },

        // ── CLEAR CHAT ──────────────────────────────────────────────────────
        promptClearChat() {
            this.showClearModal = true;
        },

        confirmClearChat() {
            if (!this.activeConversationId || this.clearingChat) return;
            this.clearingChat = true;

            fetch(`${window.__CHAT_CONFIG__.messagesBase}/${this.activeConversationId}/clear`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.__CHAT_CONFIG__.csrf,
                }
            })
            .then(r => r.json())
            .then(res => {
                if (res.ok) {
                    this.messages = [];
                    this.nextCursor = 0;
                    this.hasOlderMessages = false;
                    const container = document.getElementById('chat-messages-inner');
                    if (container) container.innerHTML = '';

                    // Update contact list preview if in local cache
                    const c = this.contacts.find(x => x.conversation_id === this.activeConversationId);
                    if (c) {
                        c.last_message = 'No messages yet';
                        c.last_time = '';
                        c.unread = 0;
                    }
                    this.showClearModal = false;
                }
            })
            .catch(() => {
                alert('Failed to clear chat. Please try again.');
            })
            .finally(() => {
                this.clearingChat = false;
            });
        },

        // ── CLEAR ALL CHATS (CHAT BLOCKS & MESSAGES) ────────────────────────
        promptClearAllChats() {
            this.showClearAllModal = true;
        },

        confirmClearAllChats() {
            if (this.clearingAll) return;
            this.clearingAll = true;

            fetch(window.__CHAT_CONFIG__.clearAll, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.__CHAT_CONFIG__.csrf,
                }
            })
            .then(r => r.json())
            .then(res => {
                if (res.ok) {
                    this.contacts = [];
                    if (this.view === 'chat') {
                        this.backToContacts();
                    }
                    this.fetchGlobalUnread();
                    this.showClearAllModal = false;
                }
            })
            .catch(() => {
                alert('Failed to clear all chats. Please try again.');
            })
            .finally(() => {
                this.clearingAll = false;
            });
        },

        // ── DELETE SINGLE CONVERSATION BLOCK ────────────────────────────────
        promptDeleteConversation(contact) {
            if (!contact || !contact.conversation_id) return;
            this.targetDeleteContact = contact;
            this.showDeleteConvModal = true;
        },

        confirmDeleteConversation() {
            if (!this.targetDeleteContact || this.deletingConv) return;
            const convId = this.targetDeleteContact.conversation_id;
            this.deletingConv = true;

            fetch(`${window.__CHAT_CONFIG__.messagesBase}/${convId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.__CHAT_CONFIG__.csrf,
                }
            })
            .then(r => r.json())
            .then(res => {
                if (res.ok) {
                    this.contacts = this.contacts.filter(c => c.conversation_id !== convId);
                    if (this.view === 'chat' && this.activeConversationId === convId) {
                        this.backToContacts();
                    }
                    this.fetchGlobalUnread();
                    this.showDeleteConvModal = false;
                    this.targetDeleteContact = null;
                }
            })
            .catch(() => {
                alert('Failed to delete conversation. Please try again.');
            })
            .finally(() => {
                this.deletingConv = false;
            });
        },

        // ── KEYBOARD SHORTCUTS ─────────────────────────────────────────────
        handleKeydown(e) {
            if (e.key === 'Enter') {
                if (!e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            }
        },

        // ── SEND MESSAGE ─────────────────────────────────────────────────────
        sendMessage() {
            const body = this.inputText.trim();
            if (!body || !this.activeConversationId || this.sendingMessage) return;

            this.sendingMessage = true;
            this.inputText = '';

            const inputEl = document.getElementById('chat-input');
            if (inputEl) {
                inputEl.style.height = '';
            }

            fetch(`${window.__CHAT_CONFIG__.messagesBase}/${this.activeConversationId}/messages`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.__CHAT_CONFIG__.csrf,
                },
                body: JSON.stringify({ body })
            })
            .then(r => r.json())
            .then(m => {
                if (!m.id) return;
                const container = document.getElementById('chat-messages-inner');
                const area = document.getElementById('chat-msg-area');
                if (container) container.appendChild(this.buildMessageEl(m));
                this.messages.push(m);
                if (m.id > this.nextCursor) this.nextCursor = m.id;
                if (area) area.scrollTop = area.scrollHeight;
            })
            .catch(() => { this.inputText = body; })
            .finally(() => { this.sendingMessage = false; });
        },

        // ── ATTACHMENT UPLOAD ────────────────────────────────────────────────
        handleFileUpload(event) {
            const file = event.target.files[0];
            if (!file || !this.activeConversationId) return;

            const formData = new FormData();
            formData.append('file', file);

            this.sendingMessage = true;

            fetch(`${window.__CHAT_CONFIG__.messagesBase}/${this.activeConversationId}/attachments`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.__CHAT_CONFIG__.csrf,
                },
                body: formData
            })
            .then(r => r.json())
            .then(m => {
                if (!m.id) return;
                const container = document.getElementById('chat-messages-inner');
                const area = document.getElementById('chat-msg-area');
                if (container) container.appendChild(this.buildMessageEl(m));
                this.messages.push(m);
                if (m.id > this.nextCursor) this.nextCursor = m.id;
                if (area) area.scrollTop = area.scrollHeight;
            })
            .catch(() => {
                alert('Failed to upload file. Maximum size is 10MB (PDF, Images, Word, Excel).');
            })
            .finally(() => {
                this.sendingMessage = false;
                event.target.value = '';
            });
        },

        // ── TYPING INDICATORS ────────────────────────────────────────────────
        onTyping() {
            const ta = document.getElementById('chat-input');
            if (ta) {
                ta.style.height = 'auto';
                if (ta.scrollHeight > 38) {
                    ta.style.height = Math.min(ta.scrollHeight, 96) + 'px';
                }
            }
            if (!this.activeConversationId) return;
            const now = Date.now();
            if (now - this.lastTypingSent > 3000) {
                this.lastTypingSent = now;
                fetch(`${window.__CHAT_CONFIG__.messagesBase}/${this.activeConversationId}/typing`, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': window.__CHAT_CONFIG__.csrf }
                }).catch(() => {});
            }
        },

        pollTyping() {
            if (!this.activeConversationId) return;
            fetch(`${window.__CHAT_CONFIG__.messagesBase}/${this.activeConversationId}/typing`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': window.__CHAT_CONFIG__.csrf }
            })
            .then(r => r.json())
            .then(d => {
                this.typingText = d.typing && d.typing.length > 0;
            })
            .catch(() => {});
        },

        // ── POLLING ENGINE ───────────────────────────────────────────────────
        startPolling() {
            this.stopPolling();
            this.pollTimer = setInterval(() => {
                if (!document.hidden && this.view === 'chat') {
                    if (this.monitoringMode) {
                        this.loadMonitorMessages(false);
                    } else {
                        this.loadMessages(false);
                        this.pollTyping();
                    }
                }
            }, 3000);
        },

        stopPolling() {
            if (this.pollTimer) {
                clearInterval(this.pollTimer);
                this.pollTimer = null;
            }
        },

        startUnreadPolling() {
            if (this.unreadTimer) clearInterval(this.unreadTimer);
            this.fetchGlobalUnread();
            this.unreadTimer = setInterval(() => {
                if (!document.hidden) this.fetchGlobalUnread();
            }, 15000);
        },

        fetchGlobalUnread() {
            fetch(window.__CHAT_CONFIG__.unread, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': window.__CHAT_CONFIG__.csrf }
            })
            .then(r => r.json())
            .then(d => { this.globalUnread = d.count || 0; })
            .catch(() => {});
        },

        // ── PARENT MONITORING ────────────────────────────────────────────────
        loadMonitorChildren() {
            if (!window.__IS_PARENT__) return;
            this.loadingMonitor = true;

            fetch(window.__CHAT_CONFIG__.monitorChildren, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': window.__CHAT_CONFIG__.csrf }
            })
            .then(r => r.json())
            .then(data => {
                this.monitorChildren = Array.isArray(data) ? data : [];
            })
            .catch(() => {})
            .finally(() => { this.loadingMonitor = false; });
        },

        selectMonitorChild(child) {
            this.monitorSelectedChild = child;
            this.monitorConversations = [];
            this.loadingMonitor = true;

            const url = `${window.__CHAT_CONFIG__.monitorConvBase}/${child.student_id}/conversations`;
            fetch(url, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': window.__CHAT_CONFIG__.csrf }
            })
            .then(r => r.json())
            .then(data => {
                this.monitorConversations = Array.isArray(data) ? data : [];
            })
            .catch(() => {})
            .finally(() => { this.loadingMonitor = false; });
        },

        openMonitorConversation(contact) {
            this.monitoringMode       = true;
            this.monitorChildName     = this.monitorSelectedChild?.name || 'Child';
            this.activeContact        = contact;
            this.activeConversationId = contact.conversation_id;
            this.view                 = 'chat';
            this.messages             = [];
            this.nextCursor           = 0;
            this.hasOlderMessages     = false;

            this.loadMonitorMessages(true);
            this.startPolling();
        },

        loadMonitorMessages(initial = false) {
            if (!this.activeConversationId || !this.monitorSelectedChild) return;
            const sid   = this.monitorSelectedChild.student_id;
            const since = initial ? 0 : this.nextCursor;
            const url   = `${window.__CHAT_CONFIG__.monitorConvBase}/${sid}/conversations/${this.activeConversationId}?since_id=${since}`;

            this.loadingMessages = initial;
            fetch(url, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': window.__CHAT_CONFIG__.csrf }
            })
            .then(r => r.json())
            .then(data => {
                if (!data.messages) return;
                if (initial) {
                    this.renderMessages(data.messages, true);
                    this.hasOlderMessages = data.has_more;
                } else if (data.messages.length > 0) {
                    this.appendMessages(data.messages);
                }
                if (data.next_cursor > this.nextCursor) this.nextCursor = data.next_cursor;
            })
            .catch(() => {})
            .finally(() => { this.loadingMessages = false; });
        },

        // ── ADMIN SYSTEMATIC FEATURES ────────────────────────────────────────
        updateConvStatus(status) {
            if (!this.activeConversationId) return;
            const url = `${window.__CHAT_CONFIG__.adminConvBase}/${this.activeConversationId}/status`;
            fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.__CHAT_CONFIG__.csrf,
                },
                body: JSON.stringify({ status })
            }).catch(() => {});
        },

        toggleAdminDrawer() {
            this.showAdminDrawer = !this.showAdminDrawer;
            if (this.showAdminDrawer && !this.adminUserContext && this.activeContact) {
                const targetId = this.activeContact.contact_id || this.activeContact.id;
                if (!targetId) return;

                this.loadingAdminContext = true;
                fetch(`${window.__CHAT_CONFIG__.adminUserContext}/${targetId}`, {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': window.__CHAT_CONFIG__.csrf }
                })
                .then(r => r.json())
                .then(d => { this.adminUserContext = d; })
                .catch(() => {})
                .finally(() => { this.loadingAdminContext = false; });
            }
        },

        // ── UTILITIES ────────────────────────────────────────────────────────
        escHtml(str) {
            return String(str || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        },

        formatDate(d) {
            if (!d) return '';
            const nowMY = new Date();
            const today = new Intl.DateTimeFormat('en-CA', { timeZone: 'Asia/Kuala_Lumpur' }).format(nowMY);
            const yestDate = new Date(Date.now() - 86400000);
            const yest = new Intl.DateTimeFormat('en-CA', { timeZone: 'Asia/Kuala_Lumpur' }).format(yestDate);
            if (d === today) return 'Today';
            if (d === yest)  return 'Yesterday';
            try {
                return new Date(d + 'T00:00:00+08:00').toLocaleDateString('en-MY', { day: 'numeric', month: 'short' });
            } catch (e) {
                return d;
            }
        }
    };
}
</script>
