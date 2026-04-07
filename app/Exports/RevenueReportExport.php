<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class RevenueReportExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting
{
    protected $data;
    protected $isMonthly;
    protected $isHourly;
    private $index = 0;

    public function __construct($data, $isMonthly = false, $isHourly = false)
    {
        $this->data = collect($data);
        $this->isMonthly = $isMonthly;
        $this->isHourly = $isHourly;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        $dateHeader = 'Tanggal';
        if ($this->isMonthly) $dateHeader = 'Bulan';
        if ($this->isHourly) $dateHeader = 'Waktu/Jam';

        return [
            'No',
            $dateHeader,
            'Total Transaksi Selesai',
            'Total Pendapatan (IDR)',
        ];
    }

    public function map($item): array
    {
        $item = (object) $item;
        return [
            ++$this->index,
            $item->date_label,
            $item->count,
            $item->total,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => '#,##0',
        ];
    }
}
