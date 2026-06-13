<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #14213d; font-size: 10px; }
        h1 { margin-bottom: 3px; color: #2563eb; }
        p { color: #64748b; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th { color: white; background: #2563eb; text-align: left; padding: 8px; }
        td { border-bottom: 1px solid #dbe4f1; padding: 7px 8px; }
        tr:nth-child(even) td { background: #f5f8fd; }
    </style>
</head>
<body>
    <h1>TemuKembali - Laporan Data Barang</h1>
    <p>Dibuat pada {{ $generatedAt->format('d-m-Y H:i') }} | Total {{ $items->count() }} laporan</p>
    <table>
        <thead><tr><th>ID</th><th>Nama Barang</th><th>Kategori</th><th>Status</th><th>Pelapor</th><th>Lokasi</th><th>Tanggal</th><th>Proses</th><th>Moderasi</th></tr></thead>
        <tbody>
        @foreach($items as $item)
            <tr><td>{{ $item->id }}</td><td>{{ $item->name }}</td><td>{{ $item->category_label }}</td><td>{{ $item->status_label }}</td><td>{{ $item->user->name }}</td><td>{{ $item->location }}</td><td>{{ $item->reported_at->format('d-m-Y H:i') }}</td><td>{{ $item->is_resolved ? 'Selesai' : 'Aktif' }}</td><td>{{ $item->is_spam ? 'Spam' : 'Aktif' }}</td></tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
