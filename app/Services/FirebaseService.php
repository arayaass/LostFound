<?php

namespace App\Services;

use App\Models\Item;
use Illuminate\Support\Facades\Http;

class FirebaseService
{
    public function syncItem(Item $item): ?string
    {
        $project = config('services.firebase.project_id');
        $key = config('services.firebase.api_key');
        if (! $project || ! $key) return null;

        $response = Http::post("https://firestore.googleapis.com/v1/projects/{$project}/databases/(default)/documents/items?key={$key}", [
            'fields' => [
                'name' => ['stringValue' => $item->name],
                'status' => ['stringValue' => $item->status],
                'location' => ['stringValue' => $item->location],
                'description' => ['stringValue' => $item->description],
            ],
        ]);
        return $response->successful() ? basename($response->json('name')) : null;
    }
}
