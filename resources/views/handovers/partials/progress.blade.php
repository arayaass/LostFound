@if($handover->owner_note)<div class="handover-note"><strong>Catatan pelapor</strong><p>{{$handover->owner_note}}</p></div>@endif

@if($handover->status === 'approved' || $handover->status === 'completed')
<div class="meeting-box">
    <div><span class="muted">Lokasi Penyerahan</span><strong>{{$handover->meeting_location}}</strong></div>
    <div><span class="muted">Jadwal</span><strong>{{$handover->meeting_at?->translatedFormat('d F Y, H:i')}}</strong></div>
</div>
<div class="confirmation-progress">
    <div class="{{$handover->owner_confirmed_at ? 'confirmed' : ''}}"><span>{{$handover->owner_confirmed_at ? '✓' : '1'}}</span><strong>Konfirmasi Pelapor</strong><small>{{$handover->owner_confirmed_at ? 'Sudah dikonfirmasi' : 'Belum dikonfirmasi'}}</small></div>
    <div class="{{$handover->claimant_confirmed_at ? 'confirmed' : ''}}"><span>{{$handover->claimant_confirmed_at ? '✓' : '2'}}</span><strong>Konfirmasi Pemohon</strong><small>{{$handover->claimant_confirmed_at ? 'Sudah dikonfirmasi' : 'Belum dikonfirmasi'}}</small></div>
</div>
@endif

@if($handover->status === 'approved')
<div class="confirm-box">
    <div><strong>Langkah 3: Konfirmasi Penyerahan</strong><p class="muted">Tekan konfirmasi hanya setelah barang benar-benar diserahkan. Proses selesai setelah kedua pihak mengonfirmasi.</p></div>
    @php($confirmed=$isOwner ? $handover->owner_confirmed_at : $handover->claimant_confirmed_at)
    @if(!$confirmed)
        <form method="post" action="{{route('handovers.confirm',$handover)}}" onsubmit="return confirm('Pastikan barang benar-benar sudah diserahkan. Lanjutkan?')">@csrf @method('PATCH')<button class="btn btn-primary">Konfirmasi Barang Diserahkan</button></form>
    @else
        <span class="badge badge-found">Konfirmasi Anda tersimpan</span>
    @endif
    @if(!$isOwner)<form method="post" action="{{route('handovers.cancel',$handover)}}">@csrf @method('PATCH')<button class="btn btn-danger">Batalkan Proses</button></form>@endif
</div>
@elseif($handover->status === 'completed')
<div class="handover-completed"><strong>Serah terima selesai</strong><span>Kedua pihak telah mengonfirmasi bahwa barang sudah diserahkan.</span></div>
@elseif($handover->status === 'requested' && !$isOwner)
<form method="post" action="{{route('handovers.cancel',$handover)}}">@csrf @method('PATCH')<button class="btn btn-danger">Batalkan Permintaan</button></form>
@endif

<details class="timeline"><summary>Riwayat proses ({{$handover->events->count()}})</summary>@foreach($handover->events as $event)<div class="timeline-event"><span></span><div><strong>{{$event->description}}</strong><small class="muted">{{$event->user?->name ?? 'Sistem'}} · {{$event->created_at->diffForHumans()}}</small></div></div>@endforeach</details>
