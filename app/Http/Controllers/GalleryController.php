<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Fest;
use App\Models\GalleryImage;
use App\Services\GalleryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    protected $galleryService;

    public function __construct(GalleryService $galleryService)
    {
        $this->galleryService = $galleryService;
    }

    /**
     * Display the main gallery page with filtering options
     */
    public function index(Request $request)
    {
        $filters = $request->only(['type', 'event_id', 'fest_id']);
        
        // Get all images with filters and optimized eager loading
        $images = $this->galleryService->getAllGalleryImages($filters);
        
        // Get events and fests for filter dropdowns with minimal data
        $events = Event::select('id', 'title')->published()->orderBy('title')->get();
        $fests = Fest::select('id', 'title')->published()->orderBy('title')->get();
        
        // Get statistics for display
        $statistics = $this->galleryService->getGalleryStatistics();
        
        return view('gallery.index', compact('images', 'events', 'fests', 'filters', 'statistics'));
    }

    /**
     * Display gallery for a specific event or fest
     */
    public function show(Request $request, $type, $id)
    {
        if ($type === 'event') {
            $imageable = Event::findOrFail($id);
            $images = $this->galleryService->getEventGallery($imageable);
        } elseif ($type === 'fest') {
            $imageable = Fest::findOrFail($id);
            $images = $this->galleryService->getFestGallery($imageable);
        } else {
            abort(404);
        }

        return view('gallery.show', compact('images', 'imageable', 'type'));
    }

    /**
     * Show the upload form for admins
     */
    public function create(Request $request)
    {
        Gate::authorize('create', GalleryImage::class);
        
        $events = Event::select('id', 'title')->orderBy('title')->get();
        $fests = Fest::select('id', 'title')->orderBy('title')->get();
        
        // Pre-select event or fest if provided in query params
        $selectedEvent = $request->get('event_id');
        $selectedFest = $request->get('fest_id');
        
        return view('gallery.create', compact('events', 'fests', 'selectedEvent', 'selectedFest'));
    }

    /**
     * Handle image upload
     */
    public function store(Request $request)
    {
        Gate::authorize('create', GalleryImage::class);
        
        $request->validate([
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
            'association_type' => 'nullable|in:event,fest,general',
            'association_id' => 'nullable|integer',
            'captions' => 'nullable|array',
            'captions.*' => 'nullable|string|max:255',
        ]);

        $imageable = null;
        
        // Determine what to associate images with
        if ($request->association_type === 'event' && $request->association_id) {
            $imageable = Event::findOrFail($request->association_id);
            Gate::authorize('uploadForEvent', GalleryImage::class);
        } elseif ($request->association_type === 'fest' && $request->association_id) {
            $imageable = Fest::findOrFail($request->association_id);
            Gate::authorize('uploadForFest', GalleryImage::class);
        } else {
            Gate::authorize('uploadGeneral', GalleryImage::class);
        }

        $uploadedImages = collect();
        $errors = [];

        foreach ($request->file('images') as $index => $file) {
            $options = [
                'caption' => $request->captions[$index] ?? null,
            ];
            
            $image = $this->galleryService->uploadSingleImage($file, $imageable, auth()->user(), $options);
            
            if ($image) {
                $uploadedImages->push($image);
            } else {
                $errors[] = "Failed to upload image: " . $file->getClientOriginalName();
            }
        }

        if ($uploadedImages->count() > 0) {
            $message = $uploadedImages->count() . ' image(s) uploaded successfully.';
            if (count($errors) > 0) {
                $message .= ' ' . count($errors) . ' image(s) failed to upload.';
            }
            
            return redirect()->route('gallery.index')->with('success', $message);
        } else {
            return redirect()->back()
                ->withErrors(['images' => 'Failed to upload any images.'])
                ->withInput();
        }
    }

    /**
     * Show the edit form for an image
     */
    public function edit(GalleryImage $image)
    {
        Gate::authorize('update', $image);
        
        $events = Event::select('id', 'title')->orderBy('title')->get();
        $fests = Fest::select('id', 'title')->orderBy('title')->get();
        
        return view('gallery.edit', compact('image', 'events', 'fests'));
    }

    /**
     * Update image details
     */
    public function update(Request $request, GalleryImage $image)
    {
        Gate::authorize('update', $image);
        
        $request->validate([
            'caption' => 'nullable|string|max:255',
            'alt_text' => 'nullable|string|max:255',
            'association_type' => 'nullable|in:event,fest,general',
            'association_id' => 'nullable|integer',
        ]);

        // Update basic details
        $this->galleryService->updateImage($image, $request->only(['caption', 'alt_text']));

        // Handle association changes
        if ($request->filled('association_type')) {
            $newImageable = null;
            
            if ($request->association_type === 'event' && $request->association_id) {
                $newImageable = Event::findOrFail($request->association_id);
            } elseif ($request->association_type === 'fest' && $request->association_id) {
                $newImageable = Fest::findOrFail($request->association_id);
            }
            
            $this->galleryService->reassociateImage($image, $newImageable);
        }

        return redirect()->route('gallery.index')->with('success', 'Image updated successfully.');
    }

    /**
     * Delete an image
     */
    public function destroy(GalleryImage $image)
    {
        Gate::authorize('delete', $image);
        
        if ($this->galleryService->deleteImage($image, auth()->user())) {
            return redirect()->route('gallery.index')->with('success', 'Image deleted successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to delete image.');
        }
    }

    /**
     * Bulk delete images
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'image_ids' => 'required|array|min:1',
            'image_ids.*' => 'integer|exists:gallery_images,id',
        ]);

        // Check authorization for each image
        $images = GalleryImage::whereIn('id', $request->image_ids)->get();
        foreach ($images as $image) {
            Gate::authorize('delete', $image);
        }

        $results = $this->galleryService->bulkDeleteImages($request->image_ids, auth()->user());
        
        $message = "{$results['success']} image(s) deleted successfully.";
        if ($results['failed'] > 0) {
            $message .= " {$results['failed']} image(s) failed to delete.";
        }

        return redirect()->route('gallery.index')->with('success', $message);
    }

    /**
     * Get image data for lightbox (AJAX)
     */
    public function getImageData(GalleryImage $image)
    {
        return response()->json([
            'id' => $image->id,
            'url' => $image->getImageUrl(),
            'thumbnail' => $image->getThumbnailUrl(),
            'caption' => $image->getDisplayCaption(),
            'alt_text' => $image->alt_text,
            'uploader' => $image->uploader->name,
            'uploaded_at' => $image->created_at->format('M j, Y'),
            'file_size' => $image->getFormattedFileSize(),
            'imageable' => $image->imageable ? [
                'type' => class_basename($image->imageable),
                'title' => $image->imageable->title,
                'url' => $this->getImageableUrl($image->imageable),
            ] : null,
        ]);
    }

    /**
     * Get widget data for embedding in other pages
     */
    public function widget(Request $request)
    {
        $type = $request->get('type', 'recent'); // recent, event, fest
        $limit = min($request->get('limit', 6), 12); // Max 12 images
        
        $images = collect();
        
        switch ($type) {
            case 'event':
                if ($eventId = $request->get('event_id')) {
                    $event = Event::find($eventId);
                    if ($event) {
                        $images = $this->galleryService->getEventGallery($event)->take($limit);
                    }
                }
                break;
                
            case 'fest':
                if ($festId = $request->get('fest_id')) {
                    $fest = Fest::find($festId);
                    if ($fest) {
                        $images = $this->galleryService->getFestGallery($fest)->take($limit);
                    }
                }
                break;
                
            case 'recent':
            default:
                $images = GalleryImage::with(['uploader', 'imageable'])
                    ->orderBy('created_at', 'desc')
                    ->limit($limit)
                    ->get();
                break;
        }
        
        return view('components.gallery-widget', compact('images', 'type'));
    }

    /**
     * Get URL for imageable model
     */
    private function getImageableUrl($imageable): ?string
    {
        if ($imageable instanceof Event) {
            return route('events.show', $imageable);
        } elseif ($imageable instanceof Fest) {
            return route('fests.show', $imageable);
        }
        
        return null;
    }
}