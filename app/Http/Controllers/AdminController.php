<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class AdminController extends Controller
{
    private function guard(Request $request): void { abort_unless($request->user()->isAdmin(), 403); }
    public function index(Request $request)
    {
        $this->guard($request);
        $sort = in_array($request->sort, ['name', 'category', 'location', 'status', 'reported_at'], true) ? $request->sort : 'reported_at';
        $direction = $request->direction === 'asc' ? 'asc' : 'desc';
        $items = Item::query()->with('user')
            ->when($request->q, fn (Builder $query, string $value) => $query->where(fn (Builder $query) => $query
                ->where('name', 'like', "%{$value}%")
                ->orWhere('location', 'like', "%{$value}%")
                ->orWhereHas('user', fn (Builder $query) => $query->where('name', 'like', "%{$value}%"))))
            ->when($request->status, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($request->category, fn (Builder $query, string $value) => $query->where('category', $value))
            ->when($request->completion === 'resolved', fn (Builder $query) => $query->where('is_resolved', true))
            ->when($request->completion === 'active', fn (Builder $query) => $query->where('is_resolved', false))
            ->when($request->moderation === 'spam', fn (Builder $query) => $query->where('is_spam', true))
            ->when($request->moderation === 'active', fn (Builder $query) => $query->where('is_spam', false))
            ->orderBy($sort, $direction)->paginate(15)->withQueryString();

        return view('admin', [
            'users' => User::latest()->take(8)->get(),
            'items' => $items,
            'categories' => Item::CATEGORIES,
            'stats' => ['users' => User::count(), 'lost' => Item::where('status', 'lost')->count(), 'found' => Item::where('status', 'found')->count(), 'spam' => Item::where('is_spam', true)->count()],
        ]);
    }
    public function spam(Request $request, Item $item) { $this->guard($request); $item->update(['is_spam' => ! $item->is_spam]); return back()->with('success', 'Status laporan diperbarui.'); }
    public function destroy(Request $request, Item $item) { $this->guard($request); $item->delete(); return back()->with('success', 'Laporan dihapus.'); }
}
