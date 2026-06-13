@extends('layouts.app')
@section('title','Admin')
@section('heading','Admin Dashboard')
@section('subheading','Kelola komunitas, data laporan, dan ekspor.')
@section('content')
<div class="stats admin-stats">
    <div class="stat"><span class="muted">Pengguna</span><strong>{{$stats['users']}}</strong></div>
    <div class="stat"><span class="muted">Hilang</span><strong>{{$stats['lost']}}</strong></div>
    <div class="stat"><span class="muted">Ditemukan</span><strong>{{$stats['found']}}</strong></div>
    <div class="stat"><span class="muted">Spam</span><strong>{{$stats['spam']}}</strong></div>
</div>

<div class="section-head">
    <div><h2>Data Tables Laporan</h2><span class="muted">Cari, filter, urutkan, dan ekstrak seluruh data laporan.</span></div>
    <div class="export-actions">
        <a class="btn btn-danger" href="{{route('admin.export.pdf', request()->query())}}">Ekstrak PDF</a>
        <a class="btn btn-soft" href="{{route('admin.export.excel', request()->query())}}">Ekstrak Excel</a>
        <a class="btn btn-white" href="{{route('admin.export.csv', request()->query())}}">Ekstrak CSV</a>
    </div>
</div>

<form class="panel table-filters" method="get" action="{{route('admin')}}">
    <input class="input" name="q" value="{{request('q')}}" placeholder="Cari barang, lokasi, atau pelapor">
    <select class="input" name="status"><option value="">Semua status</option><option value="lost" @selected(request('status')==='lost')>Hilang</option><option value="found" @selected(request('status')==='found')>Ditemukan</option></select>
    <select class="input" name="category"><option value="">Semua kategori</option>@foreach($categories as $value=>$label)<option value="{{$value}}" @selected(request('category')===$value)>{{$label}}</option>@endforeach</select>
    <select class="input" name="completion"><option value="">Semua proses</option><option value="active" @selected(request('completion')==='active')>Aktif</option><option value="resolved" @selected(request('completion')==='resolved')>Selesai</option></select>
    <select class="input" name="moderation"><option value="">Semua moderasi</option><option value="active" @selected(request('moderation')==='active')>Aktif</option><option value="spam" @selected(request('moderation')==='spam')>Spam</option></select>
    <select class="input" name="sort"><option value="reported_at">Tanggal</option><option value="name" @selected(request('sort')==='name')>Nama</option><option value="location" @selected(request('sort')==='location')>Lokasi</option><option value="status" @selected(request('sort')==='status')>Status</option></select>
    <select class="input" name="direction"><option value="desc">Terbaru / Z-A</option><option value="asc" @selected(request('direction')==='asc')>Terlama / A-Z</option></select>
    <button class="btn btn-primary">Terapkan</button>
    <a class="btn btn-white" href="{{route('admin')}}">Reset</a>
</form>

<div class="panel table-panel">
    <div class="table-summary">Menampilkan {{ $items->firstItem() ?? 0 }}-{{ $items->lastItem() ?? 0 }} dari {{ $items->total() }} laporan</div>
    <div class="table-scroll">
        <table class="data-table">
            <thead><tr><th>ID</th><th>Barang</th><th>Kategori</th><th>Status</th><th>Pelapor</th><th>Lokasi</th><th>Tanggal</th><th>Proses</th><th>Moderasi</th><th>Aksi</th></tr></thead>
            <tbody>
            @forelse($items as $item)
                <tr>
                    <td>#{{$item->id}}</td>
                    <td><a href="{{route('items.show',$item->slug)}}"><strong>{{$item->name}}</strong></a></td>
                    <td>{{$item->category_label}}</td>
                    <td><span class="badge badge-{{$item->status}}">{{$item->status_label}}</span></td>
                    <td>{{$item->user->name}}</td><td>{{$item->location}}</td><td>{{$item->reported_at->format('d M Y H:i')}}</td>
                    <td><span class="badge {{$item->is_resolved?'badge-found':'badge-category'}}">{{$item->is_resolved?'Selesai':'Aktif'}}</span></td>
                    <td><span class="badge {{$item->is_spam?'badge-lost':'badge-found'}}">{{$item->is_spam?'Spam':'Aktif'}}</span></td>
                    <td><div class="table-actions"><form method="post" action="{{route('admin.items.spam',$item)}}">@csrf @method('PATCH')<button class="btn btn-soft">{{$item->is_spam?'Pulihkan':'Spam'}}</button></form><form method="post" action="{{route('admin.items.destroy',$item)}}" onsubmit="return confirm('Hapus laporan ini?')">@csrf @method('DELETE')<button class="btn btn-danger">Hapus</button></form></div></td>
                </tr>
            @empty<tr><td colspan="10"><div class="empty">Data laporan tidak ditemukan.</div></td></tr>@endforelse
            </tbody>
        </table>
    </div>
    <div class="table-pagination">{{$items->links()}}</div>
</div>

<div class="section-head"><h2>Pengguna terbaru</h2></div>
<div class="list">@foreach($users as $user)<div class="panel list-row"><div class="reporter"><img class="avatar" src="{{$user->avatar ?: 'https://ui-avatars.com/api/?name='.urlencode($user->name)}}"><div><strong>{{$user->name}}</strong><div class="muted">{{$user->email}}</div></div></div><span class="badge badge-found">{{$user->role}}</span></div>@endforeach</div>
@endsection
