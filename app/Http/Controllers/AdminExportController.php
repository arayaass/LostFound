<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminExportController extends Controller
{
    private function guard(Request $request): void
    {
        abort_unless($request->user()->isAdmin(), 403);
    }

    private function items(Request $request): Builder
    {
        $sort = in_array($request->sort, ['name', 'category', 'location', 'status', 'reported_at'], true) ? $request->sort : 'reported_at';
        $direction = $request->direction === 'asc' ? 'asc' : 'desc';

        return Item::query()->with('user')
            ->when($request->q, fn (Builder $query, string $value) => $query->where(fn (Builder $query) => $query
                ->where('name', 'like', "%{$value}%")
                ->orWhere('location', 'like', "%{$value}%")
                ->orWhereHas('user', fn (Builder $query) => $query->where('name', 'like', "%{$value}%"))))
            ->when($request->status, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($request->category, fn (Builder $query, string $value) => $query->where('category', $value))
            ->when($request->completion === 'resolved', fn (Builder $query) => $query->where('is_resolved', true))
            ->when($request->completion === 'active', fn (Builder $query) => $query->where('is_resolved', false))
            ->when($request->moderation === 'spam', fn (Builder $query) => $query->where('is_spam', true))
            ->when($request->moderation === 'active', fn (Builder $query) => $query->where('is_spam', false))
            ->orderBy($sort, $direction);
    }

    public function pdf(Request $request)
    {
        $this->guard($request);

        return Pdf::loadView('exports.items-pdf', ['items' => $this->items($request)->get(), 'generatedAt' => now()])
            ->setPaper('a4', 'landscape')
            ->download('laporan-barang-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function excel(Request $request): StreamedResponse
    {
        $this->guard($request);
        $items = $this->items($request)->get();

        return response()->streamDownload(function () use ($items) {
            echo '<?xml version="1.0" encoding="UTF-8"?>';
            echo '<?mso-application progid="Excel.Sheet"?>';
            echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"><Worksheet ss:Name="Laporan Barang"><Table>';
            $this->excelRow(['ID', 'Nama Barang', 'Kategori', 'Status', 'Pelapor', 'Lokasi', 'Tanggal', 'Proses', 'Moderasi']);
            foreach ($items as $item) {
                $this->excelRow([$item->id, $item->name, $item->category_label, $item->status_label, $item->user->name, $item->location, $item->reported_at->format('Y-m-d H:i'), $item->is_resolved ? 'Selesai' : 'Aktif', $item->is_spam ? 'Spam' : 'Aktif']);
            }
            echo '</Table></Worksheet></Workbook>';
        }, 'laporan-barang-'.now()->format('Y-m-d-His').'.xls', ['Content-Type' => 'application/vnd.ms-excel; charset=UTF-8']);
    }

    public function csv(Request $request): StreamedResponse
    {
        $this->guard($request);
        $items = $this->items($request)->get();

        return response()->streamDownload(function () use ($items) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['ID', 'Nama Barang', 'Kategori', 'Status', 'Pelapor', 'Lokasi', 'Tanggal', 'Proses', 'Moderasi']);
            foreach ($items as $item) {
                fputcsv($output, [$item->id, $item->name, $item->category_label, $item->status_label, $item->user->name, $item->location, $item->reported_at->format('Y-m-d H:i'), $item->is_resolved ? 'Selesai' : 'Aktif', $item->is_spam ? 'Spam' : 'Aktif']);
            }
            fclose($output);
        }, 'laporan-barang-'.now()->format('Y-m-d-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function excelRow(array $values): void
    {
        echo '<Row>';
        foreach ($values as $value) {
            echo '<Cell><Data ss:Type="'.(is_numeric($value) ? 'Number' : 'String').'">'.htmlspecialchars((string) $value, ENT_XML1).'</Data></Cell>';
        }
        echo '</Row>';
    }
}
