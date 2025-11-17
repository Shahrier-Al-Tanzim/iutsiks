<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $events = Event::with('author')->latest()->paginate(10);
        return view('events.index', compact('events')); // fixed typo
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('events.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'event_date' => 'required|date',
            'event_time' => 'required',
            'image' => 'nullable|image|mimes:png,jpg,jpeg,gif|max:2048'
        ]);

        $path = $request->file('image') ? $request->file('image')->store('event_images', 'public') : null;

        Event::create([
            'title' => $request->title,
            'description' => $request->description,
            'event_date' => $request->event_date,
            'event_time' => $request->event_time,
            'author_id' => auth()->id(),
            'image' => $path
        ]);

        return redirect()->route('events.index')->with('success', 'Event created!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        $event->load('author');
        return view('events.show', compact('event'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        if ($event->author_id !== auth()->id()) {
            abort(403);
        }

        return view('events.edit', compact('event'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        if ($event->author_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'event_date' => 'required|date',
            'event_time' => 'required',
            'image' => 'nullable|image|mimes:png,jpg,jpeg,gif|max:2048'
        ]);

        $path = $event->image;
        if($request->hasFile('image')) {
            $path = $request->file('image')->store('event_images', 'public');
        }

        $event->update([
            'title' => $request->title,
            'description' => $request->description,
            'event_date' => $request->event_date,
            'event_time' => $request->event_time,
            'image' => $path
        ]);

        return redirect()->route('events.index')->with('success', 'Event updated!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        if ($event->author_id !== auth()->id()) {
            abort(403);
        }

        if($event->image) {
            \Storage::disk('public')->delete($event->image);
        }

        $event->delete();
        return redirect()->route('events.index')->with('success', 'Event deleted!');
    }
}
