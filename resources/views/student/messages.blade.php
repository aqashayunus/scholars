<x-dashboard-layout>
    <x-slot name="title">Messages</x-slot>
    <x-slot name="sidebar">
        @include('student.partials.sidebar')
    </x-slot>

    {{-- Custom scrollbar for chat panel and contacts --}}
    <style>
        /* ── Scrollbar styles ─────────────────────────────── */
        .chat-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f8fafc;
        }
        .chat-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .chat-scrollbar::-webkit-scrollbar-track {
            background: #f8fafc;
            border-radius: 9999px;
        }
        .chat-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
            border: 1px solid #f8fafc;
        }
        .chat-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        .chat-scrollbar::-webkit-scrollbar-thumb:active {
            background: #64748b;
        }
        /* ── Messenger layout containment ─────────────────── */
        .messenger-wrap {
            height: 620px;
            max-height: 80vh;
            display: grid;
            grid-template-columns: 1fr 2fr;
            overflow: hidden;
        }
        @media (max-width: 767px) {
            .messenger-wrap { grid-template-columns: 1fr; }
        }
        .messenger-left,
        .messenger-right {
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: hidden;
        }
        /* The key rule: messages area fills remaining space and scrolls */
        #chat-messages {
            flex: 1 1 0;
            min-height: 0;
            overflow-y: auto;
            overflow-x: hidden;
        }
        #contact-list {
            flex: 1 1 0;
            min-height: 0;
            overflow-y: auto;
            overflow-x: hidden;
        }
        #chat-panel {
            display: flex;
            flex-direction: column;
            height: 100%;
            min-height: 0;
            overflow: hidden;
        }
    </style>

    {{-- Pass PHP contacts data to JS --}}
    <script>
        window.__CONTACTS__ = @json($contactsData);
        window.__SEND_URL__    = '/student/messages/send';
        window.__CONV_BASE__   = '/student/messages/conversation';
        window.__PREVIEW_URL__ = '/student/messages/contacts-preview';
        window.__CSRF__        = "{{ csrf_token() }}";
        window.__ME_ID__       = {{ auth()->id() }};
        window.__ME_NAME__     = @json(auth()->user()->name);
    </script>

    <div class="space-y-4">

        {{-- ── HEADER ── --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="font-heading font-black text-2xl text-gray-900">Messages</h2>
                <p class="text-xs text-gray-500 font-medium">Communicate directly with your teachers &amp; mentors.</p>
            </div>
            <span id="online-badge" class="px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-black flex items-center gap-1.5 w-fit">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Teachers Online
            </span>
        </div>

        {{-- ── MESSENGER 2-COLUMN WORKSPACE ── --}}
        <div class="messenger-wrap bg-white rounded-3xl border border-gray-100 shadow-sm">

            {{-- LEFT PANEL: CONTACTS ── --}}
            <div class="messenger-left border-r border-gray-100">
                {{-- Search --}}
                <div class="p-4 border-b border-gray-50">
                    <input id="msg-search" type="text" placeholder="Search teacher..."
                           class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-4 py-2.5 text-xs focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                {{-- Contact list --}}
                <div id="contact-list" class="flex-1 overflow-y-auto space-y-1 p-3 chat-scrollbar">
                    @if($contacts->isEmpty())
                        <div class="flex flex-col items-center justify-center h-full text-center px-6 py-10">
                            <div class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center mb-3">
                                <svg class="w-7 h-7 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            </div>
                            <p class="text-xs font-bold text-gray-500">No enrolled teachers yet.</p>
                            <p class="text-[11px] text-gray-400 mt-1">Enrol in a course to start chatting.</p>
                        </div>
                    @else
                        @foreach($contacts as $contact)
                            @php
                                $words    = explode(' ', $contact->name);
                                $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
                                $colors   = ['bg-purple-600','bg-emerald-600','bg-blue-600','bg-amber-500','bg-pink-500','bg-indigo-600'];
                                $color    = $colors[$contact->id % count($colors)];
                            @endphp
                            <div class="contact-item cursor-pointer p-3.5 rounded-2xl border border-transparent hover:bg-gray-50 transition-all flex items-center gap-3 group"
                                 data-id="{{ $contact->id }}"
                                 data-name="{{ $contact->name }}"
                                 data-initials="{{ $initials }}"
                                 data-color="{{ $color }}"
                                 onclick="selectContact({{ $contact->id }}, '{{ addslashes($contact->name) }}', '{{ $initials }}', '{{ $color }}')">
                                <div class="relative w-10 h-10 rounded-2xl {{ $color }} text-white font-black text-xs flex items-center justify-center shrink-0">
                                    {{ $initials }}
                                    <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-emerald-500 rounded-full ring-2 ring-white"></span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-heading font-extrabold text-xs text-gray-900 truncate">{{ $contact->name }}</h4>
                                    <p class="text-[11px] text-blue-600 font-bold truncate capitalize">{{ $contact->role }}</p>
                                    <p class="contact-preview-{{ $contact->id }} text-[10px] text-gray-400 truncate mt-0.5">No messages yet</p>
                                </div>
                                <span class="contact-badge-{{ $contact->id }} hidden w-5 h-5 rounded-full bg-blue-600 text-white text-[10px] font-black flex items-center justify-center shrink-0"></span>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            {{-- RIGHT PANEL: CHAT ── --}}
            <div class="messenger-right border-l-0">

                {{-- Empty state --}}
                <div id="chat-empty" class="flex-1 flex flex-col items-center justify-center text-center px-8">
                    <div class="w-16 h-16 rounded-3xl bg-gradient-to-br from-blue-100 to-indigo-100 flex items-center justify-center mb-4 shadow-sm">
                        <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <h3 class="font-heading font-black text-base text-gray-700">Select a teacher</h3>
                    <p class="text-xs text-gray-400 mt-1">Choose a teacher from the left to start or continue a conversation.</p>
                </div>

                {{-- Active chat --}}
                <div id="chat-panel" class="hidden flex-col h-full">
                    {{-- Chat header --}}
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 shrink-0">
                        <div class="flex items-center gap-3">
                            <div id="chat-avatar" class="w-10 h-10 rounded-2xl bg-purple-600 text-white font-black text-xs flex items-center justify-center shadow-sm">
                                <span id="chat-avatar-initials"></span>
                            </div>
                            <div>
                                <h3 id="chat-name" class="font-heading font-black text-sm text-gray-900"></h3>
                                <p class="text-[11px] text-emerald-600 font-semibold flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span> Online
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span id="polling-indicator" class="text-[10px] text-gray-400 hidden">↻ live</span>
                        </div>
                    </div>

                    {{-- Messages area --}}
                    <div id="chat-messages" class="flex-1 overflow-y-auto px-5 py-4 space-y-3 chat-scrollbar">
                        {{-- Messages injected by JS --}}
                    </div>

                    {{-- Input bar --}}
                    <div class="px-4 py-3 border-t border-gray-100 bg-white shrink-0">
                        <div class="flex items-center gap-2">
                            <input id="msg-input" type="text"
                                   placeholder="Type your message..."
                                   class="flex-1 bg-gray-50 border border-gray-200 rounded-2xl px-4 py-3 text-xs focus:ring-2 focus:ring-blue-500 outline-none transition"
                                   onkeydown="if(event.key==='Enter' && !event.shiftKey){ event.preventDefault(); sendMessage(); }">
                            <button id="send-btn"
                                    onclick="sendMessage()"
                                    class="py-3 px-5 rounded-2xl bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-extrabold text-xs shadow-md transition-all flex items-center gap-2">
                                <span>Send</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    (() => {
        let activeContactId   = null;
        let activeContactName = null;
        let lastMsgId         = 0;
        let pollTimer         = null;

        // ── Search filter ──────────────────────────────────────────────────────
        document.getElementById('msg-search').addEventListener('input', function () {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.contact-item').forEach(el => {
                const name = el.dataset.name.toLowerCase();
                el.style.display = name.includes(q) ? '' : 'none';
            });
        });

        // ── Select contact ─────────────────────────────────────────────────────
        window.selectContact = function (id, name, initials, color) {
            activeContactId   = id;
            activeContactName = name;
            lastMsgId         = 0;

            // Highlight sidebar item
            document.querySelectorAll('.contact-item').forEach(el => {
                el.classList.remove('bg-blue-50/80', 'border-blue-200');
                el.classList.add('border-transparent');
            });
            const active = document.querySelector(`.contact-item[data-id='${id}']`);
            if (active) {
                active.classList.add('bg-blue-50/80', 'border-blue-200');
                active.classList.remove('border-transparent');
            }

            // Show chat panel
            document.getElementById('chat-empty').classList.add('hidden');
            const panel = document.getElementById('chat-panel');
            panel.classList.remove('hidden');
            panel.classList.add('flex');

            // Set header
            document.getElementById('chat-name').textContent = name;
            document.getElementById('chat-avatar-initials').textContent = initials;
            const avatar = document.getElementById('chat-avatar');
            avatar.className = `w-10 h-10 rounded-2xl text-white font-black text-xs flex items-center justify-center shadow-sm ${color}`;

            // Clear messages & load
            document.getElementById('chat-messages').innerHTML = '';
            loadConversation(true);

            // Focus input
            document.getElementById('msg-input').focus();

            // Start polling
            stopPolling();
            pollTimer = setInterval(() => loadConversation(false), 5000);
            document.getElementById('polling-indicator').classList.remove('hidden');
        };

        function stopPolling() {
            if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
        }

        // ── Load conversation ──────────────────────────────────────────────────
        function loadConversation(initial) {
            if (!activeContactId) return;
            fetch(`${window.__CONV_BASE__}/${activeContactId}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': window.__CSRF__ }
            })
            .then(r => r.json())
            .then(msgs => {
                if (!Array.isArray(msgs)) return;

                const container = document.getElementById('chat-messages');
                const newMsgs   = msgs.filter(m => m.id > lastMsgId);
                if (newMsgs.length === 0 && !initial) return;

                if (initial) {
                    container.innerHTML = '';
                    lastMsgId = 0;
                }

                let lastDate = null;
                newMsgs.forEach(m => {
                    // Date divider
                    if (m.date !== lastDate) {
                        const div = document.createElement('div');
                        div.className = 'flex items-center gap-3 my-2';
                        div.innerHTML = `<div class="flex-1 h-px bg-gray-100"></div>
                            <span class="text-[10px] text-gray-400 font-semibold whitespace-nowrap">${formatDate(m.date)}</span>
                            <div class="flex-1 h-px bg-gray-100"></div>`;
                        container.appendChild(div);
                        lastDate = m.date;
                    }

                    const wrap = document.createElement('div');
                    wrap.className = `flex ${m.mine ? 'justify-end' : 'justify-start'} items-end gap-2`;
                    wrap.innerHTML = m.mine
                        ? `<div class="max-w-sm bg-blue-600 text-white rounded-3xl rounded-tr-sm px-4 py-2.5 text-xs leading-relaxed shadow-sm">
                               <p>${escHtml(m.body)}</p>
                               <span class="text-blue-200 text-[10px] font-semibold block text-right mt-1">${m.time}</span>
                           </div>`
                        : `<div class="w-7 h-7 rounded-xl bg-gray-200 flex items-center justify-center text-[10px] font-black text-gray-600 shrink-0">${getInitials(activeContactName)}</div>
                           <div class="max-w-sm bg-gray-100 text-gray-800 rounded-3xl rounded-tl-sm px-4 py-2.5 text-xs leading-relaxed shadow-sm">
                               <p>${escHtml(m.body)}</p>
                               <span class="text-gray-400 text-[10px] font-semibold block text-right mt-1">${m.time}</span>
                           </div>`;
                    container.appendChild(wrap);
                    if (m.id > lastMsgId) lastMsgId = m.id;
                });

                container.scrollTop = container.scrollHeight;

                // Clear badge & update preview
                clearBadge(activeContactId);
                if (newMsgs.length > 0) {
                    const latest = newMsgs[newMsgs.length - 1];
                    updatePreview(activeContactId, latest.body, latest.time);
                }
            })
            .catch(() => {});
        }

        // ── Send message ───────────────────────────────────────────────────────
        window.sendMessage = function () {
            const input = document.getElementById('msg-input');
            const body  = input.value.trim();
            if (!body || !activeContactId) return;

            const btn = document.getElementById('send-btn');
            btn.disabled = true;
            input.value  = '';

            fetch(window.__SEND_URL__, {
                method:  'POST',
                headers: {
                    'Content-Type':  'application/json',
                    'Accept':        'application/json',
                    'X-CSRF-TOKEN':  window.__CSRF__,
                },
                body: JSON.stringify({ receiver_id: activeContactId, body })
            })
            .then(r => r.json())
            .then(m => {
                btn.disabled = false;
                if (!m.id) return;

                const container = document.getElementById('chat-messages');
                const wrap = document.createElement('div');
                wrap.className = 'flex justify-end items-end gap-2';
                wrap.innerHTML = `<div class="max-w-sm bg-blue-600 text-white rounded-3xl rounded-tr-sm px-4 py-2.5 text-xs leading-relaxed shadow-sm">
                    <p>${escHtml(m.body)}</p>
                    <span class="text-blue-200 text-[10px] font-semibold block text-right mt-1">${m.time}</span>
                </div>`;
                container.appendChild(wrap);
                container.scrollTop = container.scrollHeight;
                if (m.id > lastMsgId) lastMsgId = m.id;
                updatePreview(activeContactId, m.body, m.time);
            })
            .catch(() => { btn.disabled = false; input.value = body; });
        };

        // ── Helpers ────────────────────────────────────────────────────────────
        function escHtml(str) {
            return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }
        function getInitials(name) {
            const p = name.split(' ');
            return (p[0][0] + (p[1] ? p[1][0] : '')).toUpperCase();
        }
        function formatDate(d) {
            const today = new Date().toISOString().slice(0,10);
            const yest  = new Date(Date.now() - 86400000).toISOString().slice(0,10);
            if (d === today) return 'Today';
            if (d === yest)  return 'Yesterday';
            return new Date(d).toLocaleDateString('en-MY', { day:'numeric', month:'short', year:'numeric' });
        }
        function updatePreview(id, body, time) {
            const el = document.querySelector(`.contact-preview-${id}`);
            if (el) el.textContent = body.length > 45 ? body.slice(0, 45) + '…' : body;
        }
        function clearBadge(id) {
            const el = document.querySelector(`.contact-badge-${id}`);
            if (el) { el.textContent = ''; el.classList.add('hidden'); }
        }

        // ── Initial contacts preview load ─────────────────────────────────────
        const contactIds = window.__CONTACTS__.map(c => c.id);
        if (contactIds.length) {
            fetch(window.__PREVIEW_URL__, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': window.__CSRF__ },
                body: JSON.stringify({ contact_ids: contactIds })
            })
            .then(r => r.json())
            .then(data => {
                Object.entries(data).forEach(([id, info]) => {
                    const prev = document.querySelector(`.contact-preview-${id}`);
                    if (prev) prev.textContent = info.latest_body;
                    if (info.unread > 0) {
                        const badge = document.querySelector(`.contact-badge-${id}`);
                        if (badge) { badge.textContent = info.unread; badge.classList.remove('hidden'); badge.classList.add('flex'); }
                    }
                });
            })
            .catch(() => {});
        }
    })();
    </script>
    @endpush

</x-dashboard-layout>
