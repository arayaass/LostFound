<?php

namespace App\Services;

use App\Models\Item;
use App\Notifications\AppNotification;
use Illuminate\Support\Facades\Http;

class MatchingService
{
    public function refresh(Item $item): void
    {
        $opposite = $item->status === 'lost' ? 'found' : 'lost';
        $words = collect(preg_split('/\W+/', strtolower($item->name.' '.$item->description)))->filter(fn ($word) => strlen($word) > 3)->unique();

        Item::query()->where('status', $opposite)->whereKeyNot($item->id)->where('is_spam', false)->latest()
            ->limit(40)->get()->map(function (Item $candidate) use ($item, $words) {
                $haystack = strtolower($candidate->name.' '.$candidate->description.' '.$candidate->location);
                $hits = $words->filter(fn ($word) => str_contains($haystack, $word))->count();
                $location = str_contains(strtolower($candidate->location), strtolower($item->location)) ? 20 : 0;
                $score = min(98, 35 + ($hits * 12) + $location);
                return [$candidate, $score];
            })->filter(fn ($match) => $match[1] >= 47)->sortByDesc(fn ($match) => $match[1])->take(5)
            ->each(function ($match) use ($item) {
                $score = $this->aiScore($item, $match[0], $match[1]);
                $item->matches()->syncWithoutDetaching([$match[0]->id => ['score' => $score, 'reason' => 'Kesamaan nama, deskripsi, lokasi, dan analisis AI bila tersedia.']]);
                $match[0]->user->notify(new AppNotification('Potensi kecocokan barang', "{$item->name} memiliki kecocokan {$score}% dengan laporan Anda.", route('items.show', $item->slug)));
            });
    }

    private function aiScore(Item $item, Item $candidate, int $fallback): int
    {
        $key = config('services.openai.key');
        if (! $key) return $fallback;
        $response = Http::withToken($key)->timeout(15)->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4o-mini',
            'response_format' => ['type' => 'json_object'],
            'messages' => [['role' => 'user', 'content' => "Bandingkan laporan dan jawab JSON {\"score\":0-100}. A: {$item->name}; {$item->description}; {$item->location}. B: {$candidate->name}; {$candidate->description}; {$candidate->location}."]],
        ]);
        $content = $response->json('choices.0.message.content');
        return min(100, max(0, (int) ($content ? (json_decode($content, true)['score'] ?? $fallback) : $fallback)));
    }
}
