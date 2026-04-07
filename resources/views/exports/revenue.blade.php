<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pendapatan</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #333; }
        h1 { text-align: center; color: #115e59; font-size: 20px; margin-bottom: 5px; }
        .subtitle { text-align: center; color: #666; font-size: 12px; margin-bottom: 20px; }
        .summary { margin-bottom: 20px; border: 1px solid #115e59; padding: 15px; border-radius: 8px; background-color: #f0fdfa; }
        .summary table { width: 100%; border: none; }
        .summary td { border: none; padding: 5px; font-size: 12px; }
        .summary .label { font-weight: bold; color: #115e59; }
        .summary .value { font-weight: bold; font-size: 14px; text-align: right; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #115e59; color: white; padding: 8px 6px; text-align: left; font-size: 10px; }
        td { padding: 6px; border-bottom: 1px solid #eee; font-size: 10px; }
        tr:nth-child(even) { background-color: #f9fdfc; }
        .footer { text-align: center; margin-top: 30px; font-size: 9px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <h1>SonaraRent - Laporan Pendapatan</h1>
    <p class="subtitle">Periode: {{ $dateRange }} | Dicetak pada: {{ now()->format('d F Y H:i') }}</p>

    <div class="summary">
        <table>
            <tr>
                <td class="label">Total Transaksi Selesai:</td>
                <td class="value">{{ $summary['total_sales'] }} Transaksi</td>
            </tr>
            <tr>
                <td class="label">Total Pendapatan:</td>
                <td class="value">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th width="30" class="text-center">No</th>
                <th>
                    @if($isMonthly) Bulan 
                    @elseif($isHourly) Waktu/Jam
                    @else Tanggal @endif
                </th>
                <th class="text-center">Qty Transaksi</th>
                <th class="text-right">Total Pendapatan (IDR)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item['date_label'] }}</td>
                <td class="text-center">{{ $item['count'] }}</td>
                <td class="text-right">Rp {{ number_format($item['total'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f0fdfa; font-weight: bold;">
                <td colspan="2" class="text-right">TOTAL</td>
                <td class="text-center">{{ $summary['total_sales'] }}</td>
                <td class="text-right">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <p class="footer">© {{ date('Y') }} SonaraRent - Premium Instrument Rental Management Report</p>
</body>
</html>
