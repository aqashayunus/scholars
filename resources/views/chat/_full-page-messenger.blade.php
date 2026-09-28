@php
    $chatUrls = [
        'contacts'     => route('chat.contacts'),
        'directory'    => route('chat.directory'),
        'findOrCreate' => route('chat.conversations.find-or-create'),
        'messagesBase' => url('chat/conversations'),
    ];
@endphp

<style>
    .chat-scrollbar { scrollbar-width: thin; scrollbar-color: #cbd5e1 #f8fafc; }
    .chat-scrollbar::-webkit-scrollbar { width: 6px; }
    .chat-scrollbar::-webkit-scrollbar-track { background: #f8fafc; border-radius: 9999px; }
    .chat-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; border: 1px solid #f8fafc; }
    .chat-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    .messenger-wrap { height: 620px; max-height: 80vh; display: grid; grid-template-columns: 1fr 2fr; overflow: hidden; }
    @media (max-width: 767px) {
        .messenger-wrap { grid-template-columns: 1fr; height: 75vh; }
        .messenger-wrap[data-conversation-open="true"] .messenger-contacts { display: none; }
        .messenger-wrap[data-conversation-open="false"] .messenger-thread { display: none; }
    }
</style>

<div x-data="OlympiaMessengerPage({
        contactsUrl: '{{ $chatUrls['contacts'] }}',
        directoryUrl: '{{ $chatUrls['directory'] }}',
        findOrCreateUrl: '{{ $chatUrls['findOrCreate'] }}',
        messagesBase: '{{ $chatUrls['messagesBase'] }}',
        csrf: '{{ csrf_token() }}',
        myId: {{ auth()->id() }},
     })"
     x-init="init()"
     class="card p-0 overflow-hidden">

    <div class="messenger-wrap" :data-conversation-open="activeConversationId !== null">
        {{-- ── LEFT: Contacts / Directory ─────────────────────────────── --}}
        <div class="messenger-contacts border-r border-gray-100 flex flex-col bg-gray-50/50">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-heading font-bold text-sm text-charcoal" x-text="showDirectory ? 'Start a Conversation' : 'Messages'"></h2>
                <button @click="toggleDirectory()" class="text-xs font-semibold text-primary hover:underline">
                    <span x-text="showDirectory ? 'Back' : 'New chat'"></span>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto chat-scrollbar">
                {{-- Existing conversations --}}
                <template x-if="!showDirectory">
                    <div>
                        <template x-if="contacts.length === 0">
                            <p class="text-xs text-muted text-center py-8 px-4">No conversations yet. Click "New chat" to start one.</p>
                        </template>
                        <template x-for="c in contacts" :key="c.conversation_id">
                            <button @click="openConversation(c.conversation_id, c.contact_name)"
                                    class="w-full text-left px-4 py-3 flex items-center gap-3 border-b border-gray-100 hover:bg-white transition-colors"
                                    :class="activeConversationId === c.conversation_id ? 'bg-white' : ''">
                                <div class="w-10 h-10 rounded-full bg-primary-100 text-primary flex items-center justify-center font-bold text-xs shrink-0" x-text="c.contact_initials"></div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between">
                                        <span class="font-semibold text-xs text-charcoal truncate" x-text="c.contact_name"></span>
                                        <span class="text-[10px] text-muted shrink-0" x-text="c.last_time"></span>
                                    </div>
                                    <p class="text-[11px] text-muted truncate" x-text="c.last_message"></p>
                                </div>
                                <span x-show="c.unread > 0" class="bg-rose-500 text-white text-[10px] font-bold rounded-full w-5 h-5 flex items-center justify-center shrink-0" x-text="c.unread"></span>
                            </button>
                        </template>
                    </div>
                </template>

                {{-- Directory (start new chat) --}}
                <template x-if="showDirectory">
                    <div>
                        <template x-if="directory.length === 0">
                            <p class="text-xs text-muted text-center py-8 px-4">No available contacts.</p>
                        </template>
                        <template x-for="d in directory" :key="d.contact_id">
                            <button @click="startConversationWith(d.contact_id)"
                                    class="w-full text-left px-4 py-3 flex items-center gap-3 border-b border-gray-100 hover:bg-white transition-colors">
                                <div class="w-10 h-10 rounded-full bg-primary-100 text-primary flex items-center justify-center font-bold text-xs shrink-0" x-text="d.contact_initials"></div>
                                <div class="min-w-0 flex-1">
                                    <span class="font-semibold text-xs text-charcoal truncate block" x-text="d.contact_name"></span>
                                    <p class="text-[11px] text-muted truncate" x-text="d.context_label"></p>
                                </div>
                            </button>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        {{-- ── RIGHT: Active conversation thread ──────────────────────── --}}
        <div class="messenger-thread flex flex-col">
            <template x-if="activeConversationId === null">
                <div class="flex-1 flex items-center justify-center text-xs text-muted">
                    Select a conversation to start messaging.
                </div>
            </template>

            <template x-if="activeConversationId !== null">
                <div class="flex flex-col h-full">
                    <div class="p-4 border-b border-gray-100 flex items-center gap-2">
                        <button @click="activeConversationId = null" class="md:hidden text-muted">&larr;</button>
                        <h3 class="font-heading font-bold text-sm text-charcoal" x-text="activeContactName"></h3>
                    </div>

                    <div class="flex-1 overflow-y-auto chat-scrollbar p-4 space-y-3" x-ref="threadScroll">
                        <template x-for="m in messages" :key="m.id">
                            <div class="flex" :class="m.mine ? 'justify-end' : 'justify-start'">
                                <div class="max-w-[75%] rounded-2xl px-3 py-2 text-xs"
                                     :class="m.mine ? 'bg-primary text-white' : 'bg-gray-100 text-charcoal'">
                                    <p x-text="m.body"></p>
                                    <span class="block text-[10px] mt-1 opacity-70" x-text="m.time"></span>
                                </div>
                            </div>
                        </template>
                    </div>

                    <form @submit.prevent="sendMessage()" class="p-3 border-t border-gray-100 flex items-center gap-2">
                        <input type="text" x-model="newMessageBody" placeholder="Type a message..."
                               class="flex-1 px-3 py-2 rounded-full border border-gray-200 text-xs">
                        <button type="submit" class="px-4 py-2 rounded-full bg-primary text-white text-xs font-semibold">Send</button>
                    </form>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
function OlympiaMessengerPage(config) {
    return {
        contactsUrl: config.contactsUrl,
        directoryUrl: config.directoryUrl,
        findOrCreateUrl: config.findOrCreateUrl,
        messagesBase: config.messagesBase,
        csrf: config.csrf,
        myId: config.myId,

        contacts: [],
        directory: [],
        showDirectory: false,
        activeConversationId: null,
        activeContactName: '',
        messages: [],
        newMessageBody: '',
        sinceId: 0,
        pollTimer: null,

        init() {
            this.loadContacts();
            this.pollTimer = setInterval(() => {
                this.loadContacts();
                if (this.activeConversationId !== null) {
                    this.loadMessages(false);
                }
            }, 4000);
        },

        headers(extra = {}) {
            return Object.assign({
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.csrf,
            }, extra);
        },

        async loadContacts() {
            const res = await fetch(this.contactsUrl, { headers: this.headers() });
            if (res.ok) this.contacts = await res.json();
        },

        async toggleDirectory() {
            this.showDirectory = !this.showDirectory;
            if (this.showDirectory && this.directory.length === 0) {
                const res = await fetch(this.directoryUrl, { headers: this.headers() });
                if (res.ok) this.directory = await res.json();
            }
        },

        openConversation(conversationId, contactName) {
            this.activeConversationId = conversationId;
            this.activeContactName = contactName;
            this.messages = [];
            this.sinceId = 0;
            this.loadMessages(true);
        },

        async startConversationWith(contactId) {
            const res = await fetch(this.findOrCreateUrl, {
                method: 'POST',
                headers: this.headers({ 'Content-Type': 'application/json' }),
                body: JSON.stringify({ contact_id: contactId }),
            });
            if (!res.ok) {
                const err = await res.json().catch(() => ({}));
                alert(err.error || 'Unable to start this conversation.');
                return;
            }
            const data = await res.json();
            this.showDirectory = false;
            await this.loadContacts();
            const contact = this.directory.find(d => d.contact_id === contactId);
            this.openConversation(data.conversation_id, contact ? contact.contact_name : 'Conversation');
        },

        async loadMessages(reset) {
            const url = `${this.messagesBase}/${this.activeConversationId}/messages?since_id=${reset ? 0 : this.sinceId}`;
            const res = await fetch(url, { headers: this.headers() });
            if (!res.ok) return;
            const data = await res.json();

            if (reset) {
                this.messages = data.messages;
            } else if (data.messages.length > 0) {
                this.messages = this.messages.concat(data.messages);
            }
            this.sinceId = data.next_cursor;
            this.$nextTick(() => this.scrollToBottom());
        },

        async sendMessage() {
            const body = this.newMessageBody.trim();
            if (!body || this.activeConversationId === null) return;

            const url = `${this.messagesBase}/${this.activeConversationId}/messages`;
            const res = await fetch(url, {
                method: 'POST',
                headers: this.headers({ 'Content-Type': 'application/json' }),
                body: JSON.stringify({ body }),
            });
            if (!res.ok) return;

            const message = await res.json();
            this.messages.push(message);
            this.sinceId = message.id;
            this.newMessageBody = '';
            this.loadContacts();
            this.$nextTick(() => this.scrollToBottom());
        },

        scrollToBottom() {
            const el = this.$refs.threadScroll;
            if (el) el.scrollTop = el.scrollHeight;
        },
    };
}
</script>
