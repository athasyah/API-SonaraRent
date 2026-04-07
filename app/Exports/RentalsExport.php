<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RentalsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;
    protected $hideStatus;
    private $index = 0;

    public function __construct($data, $hideStatus = false)
    {
        $this->data = $data;
        $this->hideStatus = $hideStatus;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        $headings = [
            'No',
            'Customer',
            'Tanggal Sewa',
            'Tgl Kembali (Rencana)',
            'Tgl Kembali (Aktual)',
            'Total Harga',
            'Denda',
            'Status',
            'Instrumen',
            'Catatan Denda'
        ];

        if ($this->hideStatus) {
            unset($headings[7]); // Remove 'Status'
            return array_values($headings);
        }

        return $headings;
    }

    public function map($item): array
    {
        $map = [
            ++$this->index,
            $item->customer->name ?? '-',
            Carbon::parse($item->rent_date)->format('d/m/Y'),
            Carbon::parse($item->return_date)->format('d/m/Y'),
            $item->actual_return_date ? Carbon::parse($item->actual_return_date)->format('d/m/Y H:i') : '-',
            number_format($item->total_price, 0, ',', '.'),
            number_format($item->penalty->sum('amount') ?? 0, 0, ',', '.'),
            ucfirst($item->status),
            $item->details->pluck('instrument.name')->filter()->implode(', ') ?: '-',
            $item->penalty->pluck('reason')->filter()->implode(', ') ?: '-',
        ];

        if ($this->hideStatus) {
            unset($map[7]); // Remove status value
            return array_values($map);
        }

        return $map;
    }
}
