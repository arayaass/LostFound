@extends('layouts.app')
@section('title','Barang Selesai')
@section('heading','Barang Selesai')
@section('subheading','Barang yang telah dikonfirmasi dan berhasil diserahkan.')
@section('content')
<div class="resolved-hero panel"><div><span class="badge badge-found">Kabar Baik</span><h1>{{$items->total()}} barang berhasil diselesaikan</h1><p>Daftar ini berisi laporan yang telah dikonfirmasi oleh kedua pihak.</p></div></div>
<div class="category-chips"><a class="{{request('category')?'':'active'}}" href="{{route('items.resolved')}}">Semua</a>@foreach($categories as $value=>$label)<a class="{{request('category')===$value?'active':''}}" href="{{route('items.resolved',['category'=>$value])}}">{{$label}}</a>@endforeach</div>
<form class="panel resolved-filters"><input class="input" name="q" value="{{request('q')}}" placeholder="Cari barang selesai"><select class="input" name="category"><option value="">Semua kategori</option>@foreach($categories as $value=>$label)<option value="{{$value}}" @selected(request('category')===$value)>{{$label}}</option>@endforeach</select><button class="btn btn-primary">Cari</button></form>
<div class="grid">@forelse($items as $item)<x-item-card :item="$item"/>@empty<div class="panel empty" style="grid-column:1/-1">Belum ada barang selesai pada kategori ini.</div>@endforelse</div><div style="margin-top:24px">{{$items->links()}}</div>
@endsection
