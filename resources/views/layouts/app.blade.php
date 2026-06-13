<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>@yield('title','TemuKembali') · Lost & Found</title><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body><div class="shell">
<aside class="sidebar"><x-brand/>
<nav class="nav">
<a class="{{request()->routeIs('home')?'active':''}}" href="{{route('home')}}">Beranda</a>
<a class="{{request()->routeIs('items.search')?'active':''}}" href="{{route('items.search')}}">Cari Barang</a>
<a class="{{request()->routeIs('items.resolved')?'active':''}}" href="{{route('items.resolved')}}">Barang Selesai</a>
@auth
<a class="{{request()->routeIs('chat.*')?'active':''}}" href="{{route('chat.index')}}">Pesan</a>
<a class="{{request()->routeIs('handovers.*')?'active':''}}" href="{{route('handovers.index')}}">Serah Terima</a>
<a class="{{request()->routeIs('notifications')?'active':''}}" href="{{route('notifications')}}">Notifikasi</a>
<a class="{{request()->routeIs('profile')?'active':''}}" href="{{route('profile')}}">Profil</a>
@if(auth()->user()->isAdmin())<a class="{{request()->routeIs('admin')?'active':''}}" href="{{route('admin')}}">Admin</a>@endif
@endauth
</nav>@auth<a href="{{route('profile')}}" class="sidebar-user"><img class="avatar" src="{{auth()->user()->avatar ?: 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&background=dbeafe&color=2563eb'}}"><span><strong style="display:block;font-size:13px">{{auth()->user()->name}}</strong><small class="muted">{{auth()->user()->email}}</small></span></a>@endauth</aside>
<main class="main"><header class="topbar"><div><h2 class="title">@yield('heading','TemuKembali')</h2><span class="muted">@yield('subheading','Barang hilang menemukan jalan pulang.')</span></div><div>@guest<a class="btn btn-white" href="{{route('login')}}">Masuk</a><a class="btn btn-primary" href="{{route('register')}}">Daftar</a>@else<a class="btn btn-soft" href="{{route('items.create')}}">+ Buat laporan</a>@endguest</div></header>
<section class="page">@if(session('success'))<div class="flash">{{session('success')}}</div>@endif @if($errors->any())<div class="errors">{{$errors->first()}}</div>@endif @yield('content')</section></main>
@auth<a class="btn fab fab-chat" href="{{route('chat.index')}}" aria-label="Chat">Chat</a><a class="btn btn-primary fab" href="{{route('items.create')}}" aria-label="Tambah">+</a>@endauth
<nav class="bottom-nav"><a class="{{request()->routeIs('home')?'active':''}}" href="{{route('home')}}">Home<span>Beranda</span></a><a class="{{request()->routeIs('items.search')?'active':''}}" href="{{route('items.search')}}">Cari<span>Barang</span></a><a class="{{request()->routeIs('items.resolved')?'active':''}}" href="{{route('items.resolved')}}">Selesai<span>Barang</span></a>@auth<a class="{{request()->routeIs('handovers.*')?'active':''}}" href="{{route('handovers.index')}}">Proses<span>Serah Terima</span></a><a class="{{request()->routeIs('profile')?'active':''}}" href="{{route('profile')}}">Akun<span>Profil</span></a>@else<a href="{{route('login')}}">Akun<span>Masuk</span></a>@endauth</nav>
</div></body></html>
