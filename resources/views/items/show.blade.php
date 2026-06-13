@extends('layouts.app')
@section('title',$item->name)
@section('heading','Detail Barang')
@section('subheading','Informasi lengkap laporan.')
@section('content')
<div class="detail">
    <div>
        <img class="detail-image" src="{{$item->image_url}}" alt="{{$item->name}}">
        @auth
            @if(auth()->id() !== $item->user_id && !$item->is_resolved)
            <div class="panel claim-panel">
                <h2>Ajukan Serah Terima</h2>
                <p class="muted">Jelaskan alasan Anda mengenali atau menemukan barang ini. Hindari menuliskan data sensitif.</p>
                <form method="post" action="{{route('handovers.store',$item)}}">@csrf
                    <textarea class="input" name="claim_note" required minlength="20" placeholder="Contoh: Saya pemilik barang ini. Saya dapat menjelaskan ciri khusus yang tidak terlihat pada foto..."></textarea>
                    <button class="btn btn-primary" style="width:100%;margin-top:10px">Ajukan Verifikasi dan Serah Terima</button>
                </form>
            </div>
            @elseif(auth()->id() === $item->user_id && $item->handovers->isNotEmpty())
            <a class="panel handover-alert" href="{{route('handovers.index')}}"><strong>Ada {{$item->handovers->where('status','requested')->count()}} permintaan menunggu verifikasi</strong><span>Buka pusat serah terima untuk meninjau permintaan.</span></a>
            @endif
        @endauth
        <div class="section-head"><div><h2>Rekomendasi AI</h2><span class="muted">Kecocokan berdasarkan deskripsi dan lokasi.</span></div></div>
        <div class="list">@forelse($item->matches as $match)<a class="panel match-card" href="{{route('items.show',$match->slug)}}"><img src="{{$match->image_url}}"><div style="flex:1"><strong>{{$match->name}}</strong><div class="muted">{{$match->location}}</div></div><span class="match-score">{{$match->pivot->score}}% cocok</span></a>@empty<div class="panel empty">Belum ada rekomendasi yang cukup mirip.</div>@endforelse</div>
    </div>
    <aside class="panel detail-copy">
        <div style="display:flex;gap:7px;flex-wrap:wrap"><span class="badge badge-{{$item->status}}">{{$item->status_label}}</span><span class="badge badge-category">{{$item->category_label}}</span>@if($item->is_resolved)<span class="badge badge-found">Serah Terima Selesai</span>@endif</div>
        <h1>{{$item->name}}</h1>
        <div class="reporter"><img class="avatar" src="{{$item->user->avatar ?: 'https://ui-avatars.com/api/?name='.urlencode($item->user->name)}}"><span>Dilaporkan oleh <strong>{{$item->user->name}}</strong></span></div>
        <div class="meta-list"><span>Lokasi: {{$item->location}}</span><span>Tanggal: {{$item->reported_at->translatedFormat('d F Y, H:i')}}</span></div>
        <h3>Deskripsi</h3><p class="muted" style="line-height:1.7">{{$item->description}}</p>
        <div class="actions">@auth @if(auth()->id()!==$item->user_id)<form method="post" action="{{route('chat.start',$item)}}">@csrf<button class="btn btn-primary" style="width:100%">Hubungi Pelapor</button></form>@endif @else<a class="btn btn-primary" href="{{route('login')}}">Masuk untuk menghubungi</a>@endauth
        @if($item->latitude && $item->longitude)<a target="_blank" class="btn btn-white" href="https://maps.google.com/?q={{$item->latitude}},{{$item->longitude}}">Buka Google Maps</a>@else<a target="_blank" class="btn btn-white" href="https://maps.google.com/?q={{urlencode($item->location)}}">Buka Google Maps</a>@endif</div>
    </aside>
</div>
@endsection
