<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Item;
use App\Notifications\AppNotification;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $conversations = $request->user()->conversations()->with(['users', 'item', 'messages' => fn ($q) => $q->latest()->limit(1)])->latest('updated_at')->get();
        return view('chat.index', compact('conversations'));
    }

    public function start(Request $request, Item $item)
    {
        abort_if($item->user_id === $request->user()->id, 422, 'Ini laporan Anda sendiri.');
        $conversation = Conversation::where('item_id', $item->id)->whereHas('users', fn ($q) => $q->where('users.id', $request->user()->id))->first();
        if (! $conversation) {
            $conversation = Conversation::create(['item_id' => $item->id, 'created_by' => $request->user()->id]);
            $conversation->users()->attach([$request->user()->id, $item->user_id]);
        }
        return redirect()->route('chat.show', $conversation);
    }

    public function show(Request $request, Conversation $conversation)
    {
        abort_unless($conversation->users()->where('users.id', $request->user()->id)->exists(), 403);
        $conversation->load(['users', 'item', 'messages.user']);
        $conversation->users()->updateExistingPivot($request->user()->id, ['last_read_at' => now()]);
        return view('chat.show', compact('conversation'));
    }

    public function send(Request $request, Conversation $conversation)
    {
        abort_unless($conversation->users()->where('users.id', $request->user()->id)->exists(), 403);
        $data = $request->validate(['body' => 'required|string|max:2000']);
        $conversation->messages()->create($data + ['user_id' => $request->user()->id]);
        $conversation->touch();
        $conversation->users()->where('users.id', '!=', $request->user()->id)->get()
            ->each(fn ($user) => $user->notify(new AppNotification('Pesan baru dari '.$request->user()->name, $data['body'], route('chat.show', $conversation))));
        return back();
    }
}
