@extends('layouts.app')
@section('title','Serah Terima')
@section('heading','Pusat Serah Terima')
@section('subheading','Verifikasi dan selesaikan penyerahan barang dengan aman.')
@section('content')
@if(session('handover_error'))<div class="errors">{{session('handover_error')}}</div>@endif
<div class="handover-guide panel">
    <div><strong>1. Ajukan</strong><span>Pemohon menjelaskan keterkaitan dengan barang.</span></div>
    <div><strong>2. Verifikasi</strong><span>Pelapor memeriksa dan menentukan jadwal.</span></div>
    <div><strong>3. Konfirmasi</strong><span>Kedua pihak mengonfirmasi penyerahan.</span></div>
    <div><strong>4. Selesai</strong><span>Laporan otomatis ditandai selesai.</span></div>
</div>

<div class="section-head"><div><h2>Permintaan pada laporan saya</h2><span class="muted">Tinjau permintaan dari pengguna lain.</span></div></div>
<div class="list">
@forelse($owned as $handover)
    <article class="panel handover-card">
        <div class="handover-head"><div><span class="badge handover-status status-{{$handover->status}}">{{$handover->status_label}}</span><h3>{{$handover->item->name}}</h3><div class="muted">Pemohon: {{$handover->claimant->name}}</div></div><a class="btn btn-white" href="{{route('items.show',$handover->item->slug)}}">Lihat Barang</a></div>
        <div class="handover-note"><strong>Pesan pemohon</strong><p>{{$handover->claim_note}}</p></div>
        @if($handover->status === 'requested')
        <div class="handover-decisions">
            <form class="panel decision-form" method="post" action="{{route('handovers.approve',$handover)}}">@csrf @method('PATCH')<strong>Setujui dan buat jadwal</strong><input class="input" name="meeting_location" placeholder="Lokasi pertemuan aman" required><input class="input" type="datetime-local" name="meeting_at" required><textarea class="input" name="owner_note" required minlength="10" placeholder="Pesan dan instruksi untuk pemohon"></textarea><button class="btn btn-primary">Setujui Permintaan</button></form>
            <form class="panel decision-form" method="post" action="{{route('handovers.reject',$handover)}}">@csrf @method('PATCH')<strong>Tolak permintaan</strong><textarea class="input" name="owner_note" required minlength="10" placeholder="Jelaskan alasan penolakan"></textarea><button class="btn btn-danger">Tolak Permintaan</button></form>
        </div>
        @endif
        @include('handovers.partials.progress',['handover'=>$handover,'isOwner'=>true])
    </article>
@empty<div class="panel empty">Belum ada permintaan serah terima pada laporan Anda.</div>@endforelse
</div>

<div class="section-head"><div><h2>Permintaan yang saya ajukan</h2><span class="muted">Pantau verifikasi dan jadwal penyerahan.</span></div></div>
<div class="list">
@forelse($claimed as $handover)
    <article class="panel handover-card"><div class="handover-head"><div><span class="badge handover-status status-{{$handover->status}}">{{$handover->status_label}}</span><h3>{{$handover->item->name}}</h3><div class="muted">Pelapor: {{$handover->item->user->name}}</div></div><a class="btn btn-white" href="{{route('items.show',$handover->item->slug)}}">Lihat Barang</a></div>@include('handovers.partials.progress',['handover'=>$handover,'isOwner'=>false])</article>
@empty<div class="panel empty">Anda belum mengajukan serah terima.</div>@endforelse
</div>
@endsection
