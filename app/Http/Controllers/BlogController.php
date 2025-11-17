<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
// use App\Http\Controllers\Controller;

class BlogController extends Controller
{
    // // Ensure user is authenticated for all actions except index/show
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $blogs = Blog::with('author')->latest()->paginate(10);
        return view('blogs.index', compact('blogs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('blogs.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
            'image' => 'nullable|image|mimes:png,jpg,png,gif,jpeg|max:2048'
        ]);

        $path = $request->file('image') ? $request->file('image')->store('blog_images', 'public') : null;
        Blog::create([
            'title' => $request->title,
            'content' => $request->content,
            'author_id' => auth()->id(),
            'image' => $path
        ]);

        return redirect()->route('blogs.index')->with('success', 'Blog created!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Blog $blog)
    {
        $blog->load('author');
        return view('blogs.show', compact('blog'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Blog $blog)
    {
        // Optional: Only allow the author to edit
        if ($blog->author_id !== auth()->id()) {
            abort(403);
        }
        return view('blogs.edit', compact(var_name: 'blog'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Blog $blog)
    {
        if ($blog->author_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'title' => 'required',
            'content' => 'required',
            'image' => 'nullable|image|mimes:png,jpg,png,jpg,gif,jpeg|max:2048'
        ]);

        $path = $blog->image;
        if($request->hasFile('image')) {
            $path = $request->file('image')->store('blog_images', 'publc');
        }
        $blog->update([
            'title' => $request->title,
            'content' => $request->content,
            'image' => $path
        ]);

        return redirect()->route('blogs.index')->with('success', 'Blog updated!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Blog $blog)
    {
        if ($blog->author_id !== auth()->id()) {
            abort(403);
        }
        if($blog->image) {
            \Storage::disk('public')->delete($blog->image);
        }
        $blog->delete();
        return redirect()->route('blogs.index')->with('success', 'Blog deleted!');
    }
}
