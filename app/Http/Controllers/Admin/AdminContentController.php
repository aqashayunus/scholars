<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AdminContentController extends Controller
{
    /**
     * Display content management dashboard (Popup settings & Blog articles).
     */
    public function index(Request $request)
    {
        return redirect()->to(route('admin.dashboard') . '#content');
    }

    /**
     * Update parent dashboard greeting popup settings and image.
     */
    public function updatePopup(Request $request)
    {
        $validated = $request->validate([
            'title'      => 'required|string|max:255',
            'body'       => 'required|string|max:2000',
            'is_enabled' => 'nullable|boolean',
            'image'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        if ($request->hasFile('image')) {
            // Delete old uploaded image if it was in uploads/popups/
            $oldImage = Setting::get('parent_greeting_image');
            if ($oldImage && str_starts_with($oldImage, 'uploads/popups/') && file_exists(public_path($oldImage))) {
                @unlink(public_path($oldImage));
            }

            $file = $request->file('image');
            $filename = 'greeting_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/popups'), $filename);

            Setting::set('parent_greeting_image', 'uploads/popups/' . $filename, 'content', 'Parent Greeting Popup Image');
        }

        Setting::set('parent_greeting_title', $validated['title'], 'content', 'Parent Greeting Popup Title');
        Setting::set('parent_greeting_body', $validated['body'], 'content', 'Parent Greeting Popup Message');
        Setting::set('parent_greeting_enabled', $request->boolean('is_enabled') ? '1' : '0', 'content', 'Parent Greeting Popup Enabled');

        return redirect()->to(route('admin.dashboard') . '#content')
            ->with('success', 'Parent greeting popup settings updated successfully.');
    }

    /**
     * Store a newly created blog article.
     */
    public function storeBlog(Request $request)
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'category'     => 'required|string|max:100',
            'excerpt'      => 'nullable|string|max:500',
            'body'         => 'required|string',
            'thumbnail'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'is_published' => 'nullable|boolean',
        ]);

        // Generate unique slug
        $baseSlug = Str::slug($validated['title']);
        $slug = $baseSlug;
        $count = 1;
        while (BlogPost::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$count}";
            $count++;
        }

        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $filename = 'blog_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/blog'), $filename);
            $thumbnailPath = 'uploads/blog/' . $filename;
        }

        BlogPost::create([
            'user_id'      => Auth::id() ?? 1,
            'title'        => $validated['title'],
            'slug'         => $slug,
            'category'     => $validated['category'],
            'excerpt'      => $validated['excerpt'] ?? Str::limit(strip_tags($validated['body']), 150),
            'body'         => $validated['body'],
            'thumbnail'    => $thumbnailPath,
            'published_at' => $request->boolean('is_published') ? now() : null,
        ]);

        return redirect()->to(route('admin.dashboard') . '#content')
            ->with('success', 'Article published successfully.');
    }

    /**
     * Update an existing blog article.
     */
    public function updateBlog(Request $request, $id)
    {
        $post = BlogPost::findOrFail($id);

        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'category'     => 'required|string|max:100',
            'excerpt'      => 'nullable|string|max:500',
            'body'         => 'required|string',
            'thumbnail'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'is_published' => 'nullable|boolean',
        ]);

        if ($validated['title'] !== $post->title) {
            $baseSlug = Str::slug($validated['title']);
            $slug = $baseSlug;
            $count = 1;
            while (BlogPost::where('slug', $slug)->where('id', '!=', $post->id)->exists()) {
                $slug = "{$baseSlug}-{$count}";
                $count++;
            }
            $post->slug = $slug;
        }

        if ($request->hasFile('thumbnail')) {
            // Delete old file if present
            if ($post->thumbnail && file_exists(public_path($post->thumbnail))) {
                @unlink(public_path($post->thumbnail));
            }

            $file = $request->file('thumbnail');
            $filename = 'blog_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/blog'), $filename);
            $post->thumbnail = 'uploads/blog/' . $filename;
        }

        $post->title    = $validated['title'];
        $post->category = $validated['category'];
        $post->excerpt  = $validated['excerpt'] ?? Str::limit(strip_tags($validated['body']), 150);
        $post->body     = $validated['body'];

        if ($request->boolean('is_published')) {
            if (!$post->published_at) {
                $post->published_at = now();
            }
        } else {
            $post->published_at = null;
        }

        $post->save();

        return redirect()->to(route('admin.dashboard') . '#content')
            ->with('success', 'Article updated successfully.');
    }

    /**
     * Remove the specified blog article.
     */
    public function destroyBlog($id)
    {
        $post = BlogPost::findOrFail($id);

        if ($post->thumbnail && file_exists(public_path($post->thumbnail))) {
            @unlink(public_path($post->thumbnail));
        }

        $post->delete();

        return redirect()->to(route('admin.dashboard') . '#content')
            ->with('success', 'Article deleted successfully.');
    }
}
