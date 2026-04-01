<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;
    private $index = 0;

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
            'No',
            'Nama',
            'Email',
            'No. Telepon',
            'Role',
            'Tanggal Daftar'
        ];
    }

    public function map($item): array
    {
        return [
            ++$this->index,
            $item->name,
            $item->email,
            $item->phone ?? '-',
            $item->getRoleNames()->first() ?? '-',
            Carbon::parse($item->created_at)->format('d/m/Y'),
        ];
    }
}
