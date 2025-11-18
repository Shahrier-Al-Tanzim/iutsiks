<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Fest;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class EventController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Event::class);
        
        $query = Event::with([
                        'author:id,name', 
                        'fest:id,title,start_date,end_date', 
                        'approvedRegistrations:id,event_id,registration_type,team_members_json'
                     ])
                     ->select('id', 'fest_id', 'title', 'description', 'event_date', 'event_time', 'type', 'registration_type', 'location', 'max_participants', 'fee_amount', 'registration_deadline', 'status', 'author_id', 'image')
                     ->where('status', 'published');
        
        // Apply filters
        if ($request->filled('fest_id')) {
            $query->where('fest_id', $request->fest_id);
        }
        
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->filled('registration_status')) {
            switch ($request->registration_status) {
                case 'open':
                    $query->where('registration_type', '!=', 'on_spot')
                          ->where(function($q) {
                              $q->whereNull('registration_deadline')
                                ->orWhere('registration_deadline', '>=', now());
                          })
                          ->whereRaw('(max_participants IS NULL OR (SELECT COUNT(*) FROM registrations WHERE event_id = events.id AND status = "approved") < max_participants)');
                    break;
                case 'closed':
                    $query->where(function($q) {
                        $q->where('registration_deadline', '<', now())
                          ->orWhere('registration_type', 'on_spot');
                    });
                    break;
                case 'full':
                    $query->whereNotNull('max_participants')
                          ->whereRaw('(SELECT COUNT(*) FROM registrations WHERE event_id = events.id AND status = "approved") >= max_participants');
                    break;
            }
        }
        
        if ($request->filled('date_filter')) {
            switch ($request->date_filter) {
                case 'upcoming':
                    $query->where('event_date', '>=', now()->startOfDay());
                    break;
                case 'past':
                    $query->where('event_date', '<', now()->startOfDay());
                    break;
                case 'today':
                    $query->whereDate('event_date', now()->toDateString());
                    break;
            }
        }
        
        $events = $query->orderBy('event_date', 'asc')
                       ->paginate(12);
        
        return view('events.index', compact('events'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Event::class);
        
        $fests = Fest::published()->orderBy('title')->get();
        return view('events.create', compact('fests'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(\App\Http\Requests\EventStoreRequest $request)
    {
        $validated = $request->validated();
        
        // Handle image upload securely
        if ($request->hasFile('image')) {
            $fileSecurityService = new \App\Services\FileSecurityService();
            $validation = $fileSecurityService->validateFile($request->file('image'), 'image');
            
            if (!$validation['valid']) {
                return redirect()->back()
                    ->withErrors(['image' => implode(' ', $validation['errors'])])
                    ->withInput();
            }
            
            $validated['image'] = $fileSecurityService->storeFile(
                $request->file('image'), 
                'event_images'
            );
        }

        $validated['author_id'] = auth()->id();
        $validated['fee_amount'] = $validated['fee_amount'] ?? 0;

        Event::create($validated);

        return redirect()->route('events.index')->with('success', 'Event created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        $this->authorize('view', $event);
        
        $event->load([
            'author:id,name,email', 
            'fest:id,title,description,start_date,end_date,banner_image', 
            'registrations:id,event_id,user_id,registration_type,team_name,team_members_json,status,payment_status',
            'registrations.user:id,name,email,phone,student_id',
            'gallery:id,imageable_type,imageable_id,image_path,thumbnail_path,caption,alt_text'
        ]);
        
        // Check if current user is already registered
        $userRegistration = null;
        if (auth()->check()) {
            $userRegistration = $event->registrations()
                                     ->select('id', 'event_id', 'user_id', 'registration_type', 'team_name', 'status', 'payment_status')
                                     ->where('user_id', auth()->id())
                                     ->first();
        }
        
        return view('events.show', compact('event', 'userRegistration'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        $this->authorize('update', $event);

        $fests = Fest::published()->orderBy('title')->get();
        return view('events.edit', compact('event', 'fests'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $request->validate([
            'fest_id' => 'nullable|exists:fests,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'event_date' => 'required|date|after_or_equal:today',
            'event_time' => 'required',
            'type' => 'required|in:quiz,lecture,donation,competition,workshop',
            'registration_type' => 'required|in:individual,team,both,on_spot',
            'location' => 'nullable|string|max:255',
            'max_participants' => 'nullable|integer|min:1',
            'fee_amount' => 'nullable|numeric|min:0',
            'registration_deadline' => 'nullable|date|after_or_equal:today|before_or_equal:event_date',
            'status' => 'required|in:draft,published,completed',
            'image' => 'nullable|image|mimes:png,jpg,jpeg,gif|max:2048'
        ]);

        $path = $event->image;
        if($request->hasFile('image')) {
            $path = $request->file('image')->store('event_images', 'public');
        }

        $event->update([
            'fest_id' => $request->fest_id,
            'title' => $request->title,
            'description' => $request->description,
            'event_date' => $request->event_date,
            'event_time' => $request->event_time,
            'type' => $request->type,
            'registration_type' => $request->registration_type,
            'location' => $request->location,
            'max_participants' => $request->max_participants,
            'fee_amount' => $request->fee_amount ?? 0,
            'registration_deadline' => $request->registration_deadline,
            'status' => $request->status,
            'image' => $path
        ]);

        return redirect()->route('events.index')->with('success', 'Event updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $this->authorize('delete', $event);

        if($event->image) {
            \Storage::disk('public')->delete($event->image);
        }

        $event->delete();
        return redirect()->route('events.index')->with('success', 'Event deleted!');
    }
}
