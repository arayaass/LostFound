<?php

namespace App\Http\Controllers;

use App\Models\Handover;
use App\Models\Item;
use App\Notifications\AppNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HandoverController extends Controller
{
    public function index(Request $request)
    {
        $owned = Handover::with(['item', 'claimant', 'events.user'])->whereHas('item', fn ($q) => $q->where('user_id', $request->user()->id))->latest()->get();
        $claimed = Handover::with(['item.user', 'events.user'])->where('claimant_id', $request->user()->id)->latest()->get();
        return view('handovers.index', compact('owned', 'claimed'));
    }

    public function store(Request $request, Item $item)
    {
        abort_if($item->user_id === $request->user()->id || $item->is_resolved, 422, 'Permintaan serah terima tidak dapat dibuat.');
        abort_if($item->handovers()->where('claimant_id', $request->user()->id)->whereIn('status', ['requested', 'approved'])->exists(), 422, 'Anda sudah memiliki proses serah terima aktif.');
        $data = $request->validate(['claim_note' => 'required|string|min:20|max:1500']);
        $handover = $item->handovers()->create($data + ['claimant_id' => $request->user()->id]);
        $this->event($handover, $request->user()->id, 'requested', 'Permintaan serah terima diajukan.');
        $item->user->notify(new AppNotification('Permintaan serah terima baru', $request->user()->name.' mengajukan proses untuk '.$item->name, route('handovers.index')));
        return redirect()->route('handovers.index')->with('success', 'Permintaan serah terima telah dikirim.');
    }

    public function approve(Request $request, Handover $handover)
    {
        $this->ownerGuard($request, $handover);
        abort_unless($handover->status === 'requested', 422);
        $data = $request->validate(['owner_note' => 'required|string|min:10|max:1000', 'meeting_location' => 'required|string|max:180', 'meeting_at' => 'required|date|after:now']);
        $handover->update($data + ['status' => 'approved']);
        $this->event($handover, $request->user()->id, 'approved', 'Pelapor menyetujui permintaan dan menentukan jadwal penyerahan.');
        $handover->claimant->notify(new AppNotification('Serah terima disetujui', 'Jadwal penyerahan '.$handover->item->name.' telah ditentukan.', route('handovers.index')));
        return back()->with('success', 'Permintaan disetujui dan jadwal disimpan.');
    }

    public function reject(Request $request, Handover $handover)
    {
        $this->ownerGuard($request, $handover);
        abort_unless($handover->status === 'requested', 422);
        $data = $request->validate(['owner_note' => 'required|string|min:10|max:1000']);
        $handover->update($data + ['status' => 'rejected']);
        $this->event($handover, $request->user()->id, 'rejected', 'Permintaan serah terima ditolak oleh pelapor.');
        $handover->claimant->notify(new AppNotification('Permintaan serah terima ditolak', $data['owner_note'], route('handovers.index')));
        return back()->with('success', 'Permintaan ditolak.');
    }

    public function confirm(Request $request, Handover $handover)
    {
        $isOwner = $handover->item->user_id === $request->user()->id;
        $isClaimant = $handover->claimant_id === $request->user()->id;
        abort_unless($isOwner || $isClaimant, 403);

        $result = DB::transaction(function () use ($handover, $request, $isOwner) {
            $locked = Handover::query()->with(['item.user', 'claimant'])->lockForUpdate()->findOrFail($handover->id);
            $column = $isOwner ? 'owner_confirmed_at' : 'claimant_confirmed_at';

            if ($locked->status === 'completed') {
                return ['message' => 'Serah terima ini sudah selesai.', 'completed' => true, 'notify' => null, 'handover' => $locked];
            }

            if ($locked->status !== 'approved') {
                return ['error' => 'Konfirmasi belum dapat dilakukan karena proses belum disetujui atau sudah dibatalkan.'];
            }

            if ($locked->{$column}) {
                return ['message' => 'Konfirmasi Anda sudah tersimpan. Menunggu konfirmasi pihak lainnya.', 'completed' => false, 'notify' => null, 'handover' => $locked];
            }

            $locked->update([$column => now()]);
            $this->event($locked, $request->user()->id, 'confirmed', ($isOwner ? 'Pelapor' : 'Pemohon').' mengonfirmasi barang telah diserahkan.');
            $locked->refresh();

            if (! $locked->owner_confirmed_at || ! $locked->claimant_confirmed_at) {
                return ['message' => 'Konfirmasi berhasil disimpan. Menunggu konfirmasi pihak lainnya.', 'completed' => false, 'notify' => 'waiting', 'handover' => $locked];
            }

            $locked->update(['status' => 'completed', 'completed_at' => now()]);
            $locked->item->update(['is_resolved' => true]);
            $this->event($locked, null, 'completed', 'Kedua pihak telah mengonfirmasi. Proses serah terima selesai.');

            return ['message' => 'Kedua pihak telah mengonfirmasi. Serah terima selesai.', 'completed' => true, 'notify' => 'completed', 'handover' => $locked];
        });

        if (isset($result['error'])) {
            return back()->with('handover_error', $result['error']);
        }

        $fresh = $result['handover']->fresh(['item.user', 'claimant']);
        if ($result['notify'] === 'completed') {
            $fresh->claimant->notify(new AppNotification('Serah terima selesai', $fresh->item->name.' telah berhasil diserahkan.', route('handovers.index')));
            $fresh->item->user->notify(new AppNotification('Serah terima selesai', $fresh->item->name.' telah berhasil diserahkan.', route('handovers.index')));
        } elseif ($result['notify'] === 'waiting') {
            $recipient = $isOwner ? $fresh->claimant : $fresh->item->user;
            $recipient->notify(new AppNotification('Menunggu konfirmasi serah terima', $request->user()->name.' telah mengonfirmasi penyerahan '.$fresh->item->name.'.', route('handovers.index')));
        }

        return back()->with('success', $result['message']);
    }

    public function cancel(Request $request, Handover $handover)
    {
        abort_unless($handover->claimant_id === $request->user()->id && in_array($handover->status, ['requested', 'approved'], true), 403);
        $handover->update(['status' => 'cancelled']);
        $this->event($handover, $request->user()->id, 'cancelled', 'Permintaan dibatalkan oleh pemohon.');
        return back()->with('success', 'Proses serah terima dibatalkan.');
    }

    private function ownerGuard(Request $request, Handover $handover): void
    {
        abort_unless($handover->item->user_id === $request->user()->id, 403);
    }

    private function event(Handover $handover, ?int $userId, string $event, string $description): void
    {
        $handover->events()->create(['user_id' => $userId, 'event' => $event, 'description' => $description]);
    }
}
