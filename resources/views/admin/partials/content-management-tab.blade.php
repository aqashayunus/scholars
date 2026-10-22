<div x-data="{ 
        contentSubTab: 'popup',
        showCreateModal: false,
        editModalOpen: false,
        editPost: { id: null, title: '', category: '', excerpt: '', body: '', is_published: true, thumbnail_url: '' },
        previewImage: '{{ asset($popupSettings['image']) }}',
        previewTitle: '{{ addslashes($popupSettings['title']) }}',
        previewBody: '{{ addslashes($popupSettings['body']) }}',
        previewEnabled: {{ $popupSettings['enabled'] === '1' ? 'true' : 'false' }},
        handleImageChange(e) {
            const file = e.target.files[0];
            if (file) {
                this.previewImage = URL.createObjectURL(file);
            }
        },
        openEdit(post) {
            this.editPost = { ...post };
            this.editModalOpen = true;
        }
     }" 
     class="space-y-6">

    {{-- Header Card --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs">
        <div>
            <h2 class="font-semibold text-lg text-gray-900 tracking-tight">Content &amp; Media Management</h2>
            <p class="text-xs text-gray-400 font-normal mt-0.5">Control the parent dashboard greeting popup and publish homepage blog articles</p>
        </div>
        
        {{-- Internal Subtab Switcher (No Emojis) --}}
        <div class="inline-flex rounded-xl bg-gray-100 p-1 text-xs font-semibold text-gray-600">
            <button type="button" 
                    @click="contentSubTab = 'popup'" 
                    :class="contentSubTab === 'popup' ? 'bg-white text-blue-700 shadow-xs' : 'text-gray-600 hover:text-gray-900'"
                    class="px-3.5 py-1.5 rounded-lg transition-all flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>Parent Greeting Popup</span>
            </button>
            <button type="button" 
                    @click="contentSubTab = 'blog'" 
                    :class="contentSubTab === 'blog' ? 'bg-white text-blue-700 shadow-xs' : 'text-gray-600 hover:text-gray-900'"
                    class="px-3.5 py-1.5 rounded-lg transition-all flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                </svg>
                <span>Homepage &amp; Blog Articles</span>
            </button>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         PANEL 1: PARENT GREETING POPUP MANAGEMENT
    ══════════════════════════════════════════════ --}}
    <div x-show="contentSubTab === 'popup'" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- Settings Form --}}
            <div class="lg:col-span-7 bg-white p-6 rounded-2xl border border-gray-200/80 shadow-xs space-y-5">
                <div class="border-b border-gray-100 pb-3">
                    <h3 class="font-semibold text-base text-gray-900">Popup Display Settings</h3>
                    <p class="text-xs text-gray-500 font-normal">Configure the greeting picture, text, and active visibility for parents</p>
                </div>

                <form action="{{ route('admin.content.popup.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    {{-- Active Switch --}}
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-200">
                        <div>
                            <label class="text-xs font-bold text-gray-800 block">Popup Visibility Status</label>
                            <p class="text-[11px] text-gray-500 font-normal">When enabled, the popup window will appear for parents on their dashboard.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_enabled" value="1" class="sr-only peer" x-model="previewEnabled">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>

                    {{-- Headline Text --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-700 block">Headline / Title <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="title" 
                               x-model="previewTitle" 
                               required 
                               maxlength="255"
                               placeholder="e.g. Welcome to Olympia Scholars"
                               class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs text-gray-900 outline-none focus:ring-2 focus:ring-blue-500 font-normal">
                    </div>

                    {{-- Body Message Text --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-700 block">Welcome Message Text <span class="text-red-500">*</span></label>
                        <textarea name="body" 
                                  rows="4" 
                                  x-model="previewBody" 
                                  required 
                                  maxlength="2000"
                                  placeholder="Enter the welcoming message to engage parents..."
                                  class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs text-gray-900 outline-none focus:ring-2 focus:ring-blue-500 font-normal leading-relaxed"></textarea>
                        <p class="text-[11px] text-gray-400">Keep the message inspiring, encouraging, and focused on partnership with parents.</p>
                    </div>

                    {{-- Upload Image --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-700 block">Upload Greeting Picture</label>
                        <div class="flex items-center gap-3">
                            <input type="file" 
                                   name="image" 
                                   accept="image/jpeg,image/png,image/webp,image/jpg" 
                                   @change="handleImageChange($event)" 
                                   class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                        </div>
                        <p class="text-[11px] text-gray-400">Recommended size: 1200 &times; 675 (16:9 ratio). Formats: JPG, PNG, WebP (Max 5MB).</p>
                    </div>

                    {{-- Save Button --}}
                    <div class="pt-3 border-t border-gray-100 flex items-center justify-end">
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Save Popup Settings</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Right: Live Visual Preview --}}
            <div class="lg:col-span-5 space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Live Preview for Parents</h4>
                    <span :class="previewEnabled ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-100 text-gray-500 border-gray-200'" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border" x-text="previewEnabled ? 'Active' : 'Disabled'"></span>
                </div>

                {{-- Preview Container (Split-Screen Structure matching parent modal) --}}
                <div class="bg-slate-900/65 p-3 sm:p-4 rounded-2xl border border-gray-200 shadow-md">
                    <div class="bg-white rounded-xl overflow-hidden shadow-xl border border-gray-100 grid grid-cols-1 sm:grid-cols-2 min-h-[300px]">
                        {{-- Left Side: Content & Headline --}}
                        <div class="p-4 sm:p-5 flex flex-col justify-between text-left order-2 sm:order-1 bg-white">
                            <div>
                                <div class="mb-2">
                                    <img src="{{ asset('images/logo-horizontal.png') }}" alt="Olympian Scholars" class="h-8 w-auto object-contain">
                                </div>
                                <h4 class="font-heading font-black text-sm sm:text-base text-amber-500 leading-tight mb-2 line-clamp-2" x-text="previewTitle"></h4>
                                <p class="text-[11px] text-gray-600 font-normal leading-relaxed line-clamp-3 mb-3" x-text="previewBody"></p>

                                <span class="inline-block px-3 py-1.5 bg-amber-400 text-slate-950 rounded text-[10px] font-black uppercase tracking-wider shadow-xs">
                                    Explore Dashboard
                                </span>
                            </div>

                            <div class="pt-3 mt-3 border-t border-gray-100 flex items-center justify-between">
                                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">No Thanks</span>
                                <span class="text-[9px] text-gray-400">Don't show again</span>
                            </div>
                        </div>

                        {{-- Right Side: Full-Bleed Image --}}
                        <div class="relative bg-gray-100 overflow-hidden order-1 sm:order-2 h-36 sm:h-auto min-h-[140px]">
                            <img :src="previewImage" alt="Preview Banner" class="w-full h-full object-cover object-center absolute inset-0">
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         PANEL 2: HOMEPAGE & BLOG ARTICLES
    ══════════════════════════════════════════════ --}}
    <div x-show="contentSubTab === 'blog'" class="space-y-6">

        {{-- Action Bar --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs">
            <div>
                <h3 class="font-semibold text-base text-gray-900">Articles &amp; Blog Posts</h3>
                <p class="text-xs text-gray-500 font-normal">Articles published here appear in the Homepage Latest Articles section and the Blog page.</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <svg class="w-3.5 h-3.5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text"
                           oninput="filterTable('blogArticlesTable', this.value)"
                           placeholder="Search articles..."
                           class="text-xs rounded-xl border border-gray-200 bg-white pl-8 pr-3 py-2 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-slate-400 w-56 shadow-2xs">
                </div>

                <button type="button" 
                        @click="showCreateModal = true" 
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>New Article</span>
                </button>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto admin-scrollbar">
                <table id="blogArticlesTable" class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 font-semibold border-b border-gray-100">
                            <th class="py-3 px-4">Picture</th>
                            <th class="py-3 px-4">Title &amp; Excerpt</th>
                            <th class="py-3 px-4">Category</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($allBlogPosts ?? [] as $post)
                            <tr class="hover:bg-gray-50/70 transition-colors target-row">
                                <td class="py-3 px-4 w-20">
                                    @if($post->thumbnail && file_exists(public_path($post->thumbnail)))
                                        <img src="{{ asset($post->thumbnail) }}" alt="{{ $post->title }}" class="w-16 h-12 rounded-lg object-cover border border-gray-100">
                                    @else
                                        <div class="w-16 h-12 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center text-gray-400 text-[10px]">
                                            No image
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3 px-4 max-w-xs">
                                    <p class="font-semibold text-gray-900 truncate">{{ $post->title }}</p>
                                    <p class="text-gray-500 text-[11px] truncate mt-0.5">{{ $post->excerpt }}</p>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 text-[10px] font-semibold border border-blue-100">
                                        {{ $post->category ?? 'General' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    @if($post->published_at)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-semibold border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Published
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-gray-100 text-gray-600 text-[10px] font-semibold border border-gray-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                            Draft
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-gray-500 text-[11px]">
                                    {{ $post->published_at ? $post->published_at->format('d M Y') : $post->created_at->format('d M Y') }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($post->published_at)
                                            <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="View on website">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </a>
                                        @endif

                                        <button type="button" 
                                                @click="openEdit({
                                                    id: {{ $post->id }},
                                                    title: '{{ addslashes($post->title) }}',
                                                    category: '{{ addslashes($post->category ?? '') }}',
                                                    excerpt: '{{ addslashes($post->excerpt ?? '') }}',
                                                    body: {{ json_encode($post->body) }},
                                                    is_published: {{ $post->published_at ? 'true' : 'false' }},
                                                    thumbnail_url: '{{ $post->thumbnail ? asset($post->thumbnail) : '' }}'
                                                })" 
                                                class="p-1.5 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition cursor-pointer" 
                                                title="Edit Article">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>

                                        <form action="{{ route('admin.content.blog.destroy', $post->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this article?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer" title="Delete Article">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-gray-400">
                                    No articles found. Click "+ New Article" to publish your first post!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════
         MODAL: CREATE NEW ARTICLE
    ══════════════════════════════════════════════ --}}
    <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-2xl p-6 space-y-5"
             @click.away="showCreateModal = false">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="font-bold text-base text-gray-900">Create New Article</h3>
                <button type="button" @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600 font-bold">&times;</button>
            </div>

            <form action="{{ route('admin.content.blog.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Article Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required placeholder="e.g. 5 Essential Study Habits for SPM Excellence" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                        <input type="text" name="category" required placeholder="e.g. Study Tips, SPM, Announcements" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Article Picture</label>
                        <input type="file" name="thumbnail" accept="image/*" class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 cursor-pointer">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Short Excerpt / Summary</label>
                    <textarea name="excerpt" rows="2" placeholder="Brief summary displayed on homepage cards (optional, auto-generated if left blank)" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Article Content / Body <span class="text-red-500">*</span></label>
                    <textarea name="body" rows="8" required placeholder="Write the full article content here..." class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 leading-relaxed"></textarea>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <label class="inline-flex items-center gap-2 cursor-pointer text-xs text-gray-700 font-medium">
                        <input type="checkbox" name="is_published" value="1" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-4 w-4">
                        <span>Publish immediately (visible on homepage and blog)</span>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold text-xs cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold text-xs shadow-xs cursor-pointer">Publish Article</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         MODAL: EDIT ARTICLE
    ══════════════════════════════════════════════ --}}
    <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-2xl p-6 space-y-5"
             @click.away="editModalOpen = false">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="font-bold text-base text-gray-900">Edit Article</h3>
                <button type="button" @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600 font-bold">&times;</button>
            </div>

            <form :action="'{{ url('admin/content/blog') }}/' + editPost.id" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                @method('PUT')

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Article Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" x-model="editPost.title" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                        <input type="text" name="category" x-model="editPost.category" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Replace Picture (Optional)</label>
                        <input type="file" name="thumbnail" accept="image/*" class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 cursor-pointer">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Short Excerpt / Summary</label>
                    <textarea name="excerpt" rows="2" x-model="editPost.excerpt" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Article Content / Body <span class="text-red-500">*</span></label>
                    <textarea name="body" rows="8" x-model="editPost.body" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:ring-2 focus:ring-blue-500 leading-relaxed"></textarea>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <label class="inline-flex items-center gap-2 cursor-pointer text-xs text-gray-700 font-medium">
                        <input type="checkbox" name="is_published" value="1" x-model="editPost.is_published" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-4 w-4">
                        <span>Published</span>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold text-xs cursor-pointer">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold text-xs shadow-xs cursor-pointer">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

</div>
