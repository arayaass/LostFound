<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Services\FirebaseService;
use App\Services\MatchingService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::with('user')->active()->latest('reported_at')->take(12)->get();
        $stats = ['lost' => Item::active()->where('status', 'lost')->count(), 'found' => Item::active()->where('status', 'found')->count(), 'resolved' => Item::resolved()->count()];
        return view('home', ['items' => $items, 'stats' => $stats, 'categories' => Item::CATEGORIES]);
    }

    public function search(Request $request)
    {
        $items = Item::with('user')->active()
            ->when($request->q, fn ($q, $value) => $q->where(fn ($q) => $q->where('name', 'like', "%{$value}%")->orWhere('description', 'like', "%{$value}%")))
            ->when($request->location, fn ($q, $value) => $q->where('location', 'like', "%{$value}%"))
            ->when($request->status, fn ($q, $value) => $q->where('status', $value))
            ->when($request->category, fn ($q, $value) => $q->where('category', $value))
            ->latest('reported_at')->paginate(9)->withQueryString();
        return view('items.search', ['items' => $items, 'categories' => Item::CATEGORIES]);
    }

    public function resolved(Request $request)
    {
        $items = Item::with('user')->resolved()
            ->when($request->q, fn ($q, $value) => $q->where(fn ($q) => $q->where('name', 'like', "%{$value}%")->orWhere('description', 'like', "%{$value}%")))
            ->when($request->category, fn ($q, $value) => $q->where('category', $value))
            ->latest('updated_at')->paginate(9)->withQueryString();
        return view('items.resolved', ['items' => $items, 'categories' => Item::CATEGORIES]);
    }

    public function create() { return view('items.create', ['categories' => Item::CATEGORIES]); }

    public function store(Request $request, FirebaseService $firebase, MatchingService $matching)
    {
        $data = $request->validate(['name' => 'required|max:120', 'category' => 'required|in:'.implode(',', array_keys(Item::CATEGORIES)), 'status' => 'required|in:lost,found', 'location' => 'required|max:180', 'latitude' => 'nullable|numeric', 'longitude' => 'nullable|numeric', 'description' => 'required|min:20|max:3000', 'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120']);
        $data['user_id'] = $request->user()->id;
        $data['slug'] = Str::slug($data['name']).'-'.Str::lower(Str::random(6));
        $data['reported_at'] = now();
        $data['image_path'] = $request->file('image')->store('items', 'public');
        $item = Item::create($data);
        $item->update(['firebase_id' => $firebase->syncItem($item)]);
        $matching->refresh($item);
        return redirect()->route('items.show', $item->slug)->with('success', 'Laporan berhasil diterbitkan.');
    }

    public function show(Item $item)
    {
        $item->load(['user', 'matches.user', 'handovers.claimant']);
        return view('items.show', compact('item'));
    }
}
