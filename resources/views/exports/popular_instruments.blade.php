<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Instrumen Populer</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #333; }
        h1 { text-align: center; color: #A47551; font-size: 20px; margin-bottom: 5px; }
        .subtitle { text-align: center; color: #666; font-size: 12px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #A47551; color: white; padding: 8px 6px; text-align: left; font-size: 10px; }
        td { padding: 6px; border-bottom: 1px solid #eee; font-size: 10px; }
        tr:nth-child(even) { background-color: #f9f7f5; }
        .footer { text-align: center; margin-top: 30px; font-size: 9px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { padding: 2px 6px; border-radius: 4px; font-weight: bold; font-size: 9px; }
        .badge-primary { background-color: #EFE6DC; color: #A47551; }
    </style>
</head>
<body>
    <h1>SonaraRent - Laporan Instrumen Terpopuler</h1>
    <p class="subtitle">Periode: {{ $dateRange }} | Dicetak pada: {{ now()->format('d F Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th width="30" class="text-center">No</th>
                <th>Nama Instrumen</th>
                <th>Kategori</th>
                <th class="text-center">Total Sewa</th>
                <th class="text-right">Rerata Harga</th>
                <th class="text-right">Total Revenue</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td style="font-weight: bold;">{{ $item['name'] }}</td>
                <td><span class="badge badge-primary">{{ $item['category_name'] }}</span></td>
                <td class="text-center">{{ $item['rental_count'] }}x</td>
                <td class="text-right">Rp {{ number_format($item['avg_price'], 0, ',', '.') }}</td>
                <td class="text-right" style="font-weight: bold; color: #A47551;">Rp {{ number_format($item['total_revenue'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p class="footer">© {{ date('Y') }} SonaraRent - Premium Instrument Rental Analytics</p>
</body>
</html>
