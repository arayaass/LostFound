@extends('layouts.app')
@section('title','Tambah Barang')
@section('heading','Buat Laporan')
@section('subheading','Berikan informasi yang jelas agar mudah dikenali.')
@section('content')
<form class="panel form-panel" method="post" action="{{route('items.store')}}" enctype="multipart/form-data">
    @csrf
    <div class="form-grid">
        <div class="field full">
            <label>Foto barang</label>
            <label class="upload" for="image">
                <div class="upload-preview" data-image-preview>
                    <span class="upload-placeholder">Klik untuk unggah foto<br><small>JPG, PNG, atau WebP, maksimal 5 MB</small></span>
                    <img data-image-preview-img alt="Pratinjau foto barang" hidden>
                </div>
            </label>
            <input id="image" data-image-input class="file-input" type="file" name="image" accept="image/jpeg,image/png,image/webp" required>
            <small class="muted" data-image-name>Belum ada gambar dipilih.</small>
            @error('image')<small class="field-error">{{$message}}</small>@enderror
        </div>
        <div class="field"><label>Nama barang</label><input class="input" name="name" value="{{old('name')}}" placeholder="Contoh: Dompet kulit cokelat" required></div>
        <div class="field"><label>Kategori barang</label><select class="input" name="category" required><option value="">Pilih kategori</option>@foreach($categories as $value => $label)<option value="{{$value}}" @selected(old('category')===$value)>{{$label}}</option>@endforeach</select></div>
        <div class="field"><label>Status laporan</label><select class="input" name="status" required><option value="lost">Hilang</option><option value="found">Ditemukan</option></select></div>
        <div class="field full"><label>Lokasi</label><div class="location-row"><input class="input" name="location" value="{{old('location')}}" placeholder="Nama tempat atau alamat" required><button type="button" data-gps class="btn btn-soft">Gunakan GPS</button></div><input type="hidden" name="latitude" value="{{old('latitude')}}"><input type="hidden" name="longitude" value="{{old('longitude')}}"><small class="muted" data-gps-status>GPS memerlukan izin lokasi browser. Gunakan HTTPS atau buka melalui localhost.</small></div>
        <div class="field full"><label>Deskripsi lengkap</label><textarea class="input" name="description" placeholder="Ciri-ciri, waktu terakhir terlihat, dan informasi penting lainnya..." required>{{old('description')}}</textarea></div>
    </div>
    <button class="btn btn-primary" style="width:100%">Terbitkan Laporan</button>
</form>
@endsection
