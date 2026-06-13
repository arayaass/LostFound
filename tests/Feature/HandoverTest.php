<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HandoverTest extends TestCase
{
    use RefreshDatabase;

    public function test_handover_completes_after_both_parties_confirm(): void
    {
        $owner = User::factory()->create();
        $claimant = User::factory()->create();
        $item = Item::create(['user_id' => $owner->id, 'name' => 'Dompet Hitam', 'slug' => 'dompet-hitam-test', 'status' => 'found', 'location' => 'Denpasar', 'description' => 'Dompet hitam dengan ciri khusus yang cukup jelas.', 'reported_at' => now()]);

        $this->actingAs($claimant)->post(route('handovers.store', $item), ['claim_note' => 'Saya pemilik dompet ini dan dapat menjelaskan ciri khususnya.'])->assertRedirect(route('handovers.index'));
        $handover = $item->handovers()->first();
        $this->actingAs($owner)->patch(route('handovers.approve', $handover), ['owner_note' => 'Silakan membawa bukti kepemilikan saat bertemu.', 'meeting_location' => 'Pos keamanan kampus', 'meeting_at' => now()->addDay()->format('Y-m-d H:i:s')])->assertSessionHasNoErrors();
        $this->actingAs($owner)->patch(route('handovers.confirm', $handover))->assertSessionHasNoErrors();
        $this->actingAs($owner)->patch(route('handovers.confirm', $handover))->assertSessionHasNoErrors();
        $this->actingAs($claimant)->patch(route('handovers.confirm', $handover))->assertSessionHasNoErrors();
        $this->actingAs($claimant)->patch(route('handovers.confirm', $handover))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('handovers', ['id' => $handover->id, 'status' => 'completed']);
        $this->assertDatabaseHas('items', ['id' => $item->id, 'is_resolved' => true]);
    }

    public function test_confirmation_before_approval_returns_friendly_message(): void
    {
        $owner = User::factory()->create();
        $claimant = User::factory()->create();
        $item = Item::create(['user_id' => $owner->id, 'name' => 'Tas', 'slug' => 'tas-test', 'status' => 'found', 'location' => 'Denpasar', 'description' => 'Tas hitam dengan ciri khusus pada bagian depan.', 'reported_at' => now()]);
        $this->actingAs($claimant)->post(route('handovers.store', $item), ['claim_note' => 'Saya mengenali tas ini dan dapat menjelaskan isi serta cirinya.']);
        $handover = $item->handovers()->first();

        $this->actingAs($claimant)->patch(route('handovers.confirm', $handover))
            ->assertRedirect()
            ->assertSessionHas('handover_error');
    }

    public function test_unrelated_user_cannot_approve_handover(): void
    {
        $owner = User::factory()->create();
        $claimant = User::factory()->create();
        $item = Item::create(['user_id' => $owner->id, 'name' => 'Kunci', 'slug' => 'kunci-test', 'status' => 'found', 'location' => 'Kuta', 'description' => 'Kunci motor dengan gantungan berwarna biru.', 'reported_at' => now()]);
        $this->actingAs($claimant)->post(route('handovers.store', $item), ['claim_note' => 'Saya mengenali kunci ini dan mengetahui ciri lengkapnya.']);
        $handover = $item->handovers()->first();

        $this->actingAs(User::factory()->create())->patch(route('handovers.approve', $handover), ['owner_note' => 'Catatan verifikasi cukup panjang.', 'meeting_location' => 'Pos keamanan', 'meeting_at' => now()->addDay()])->assertForbidden();
    }
}
