<?php

namespace App\Http\Controllers;

use App\Models\Fest;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class FestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
        $this->middleware('can:viewAny,App\Models\Fest')->only(['index']);
        $this->middleware('can:create,App\Models\Fest')->only(['create', 'store']);
        $this->middleware('can:update,fest')->only(['edit', 'update']);
        $this->middleware('can:delete,fest')->only(['destroy']);
    }

    /**
     * Display a listing of fests
     */
    public function index()
    {
        $fests = Fest::with(['creator', 'events'])
            ->when(!Auth::check() || !Auth::user()->canManageFests(), function ($query) {
                return $query->where('status', 'published');
            })
            ->orderBy('start_date', 'desc')
            ->paginate(12);

        return view('fests.index', compact('fests'));
    }

    /**
     * Show the form for creating a new fest
     */
    public function create()
    {
        return view('fests.create');
    }

    /**
     * Store a newly created fest
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:50',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'banner_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status' => 'required|in:draft,published'
        ]);

        $fest = new Fest($validated);
        $fest->created_by = Auth::id();

        // Handle banner image upload
        if ($request->hasFile('banner_image')) {
            $path = $request->file('banner_image')->store('fests/banners', 'public');
            $fest->banner_image = $path;
        }

        $fest->save();

        return redirect()->route('fests.show', $fest)
            ->with('success', 'Fest created successfully!');
    }

    /**
     * Display the specified fest
     */
    public function show(Fest $fest)
    {
        // Check if user can view this fest
        if ($fest->status !== 'published' && (!Auth::check() || !Auth::user()->canManageFests())) {
            abort(404);
        }

        $fest->load(['creator', 'gallery', 'events' => function ($query) {
            $query->orderBy('event_date', 'asc');
        }]);

        return view('fests.show', compact('fest'));
    }

    /**
     * Show the form for editing the specified fest
     */
    public function edit(Fest $fest)
    {
        return view('fests.edit', compact('fest'));
    }

    /**
     * Update the specified fest
     */
    public function update(Request $request, Fest $fest)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:50',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'banner_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status' => 'required|in:draft,published,completed'
        ]);

        // Handle banner image upload
        if ($request->hasFile('banner_image')) {
            // Delete old image if exists
            if ($fest->banner_image) {
                Storage::disk('public')->delete($fest->banner_image);
            }
            
            $path = $request->file('banner_image')->store('fests/banners', 'public');
            $validated['banner_image'] = $path;
        }

        $fest->update($validated);

        return redirect()->route('fests.show', $fest)
            ->with('success', 'Fest updated successfully!');
    }

    /**
     * Remove the specified fest
     */
    public function destroy(Fest $fest)
    {
        // Delete banner image if exists
        if ($fest->banner_image) {
            Storage::disk('public')->delete($fest->banner_image);
        }

        $fest->delete();

        return redirect()->route('fests.index')
            ->with('success', 'Fest deleted successfully!');
    }
}