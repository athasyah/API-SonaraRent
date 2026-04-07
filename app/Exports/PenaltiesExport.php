<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PenaltiesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'No.',
            'Customer',
            'Tanggal',
            'Judul Denda',
            'Alasan',
            'Instrumen',
            'Besaran (Rp)',
        ];
    }

    public function map($item): array
    {
        static $no = 1;
        return [
            $no++,
            $item->rental?->customer?->name ?? 'Unknown',
            \Carbon\Carbon::parse($item->created_at)->format('d F Y'),
            $item->title,
            $item->reason,
            $item->condition?->instrument?->name ?? '-',
            number_format($item->amount, 0, ',', '.'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
