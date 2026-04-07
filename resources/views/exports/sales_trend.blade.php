<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Volume Penyewaan</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #333; }
        h1 { text-align: center; color: #A47551; font-size: 20px; margin-bottom: 5px; }
        .subtitle { text-align: center; color: #666; font-size: 12px; margin-bottom: 20px; }
        .summary { margin-bottom: 20px; border: 1px solid #EFE6DC; padding: 15px; border-radius: 8px; background-color: #fcfaf8; }
        .summary .label { font-weight: bold; color: #A47551; }
        .summary .value { font-weight: bold; font-size: 16px; margin-left: 10px; }
        .badge { 
            display: inline-block; 
            background-color: #A47551; 
            color: white; 
            font-size: 9px; 
            padding: 2px 8px; 
            border-radius: 12px; 
            margin-left: 10px;
            vertical-align: middle;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #A47551; color: white; padding: 8px 6px; text-align: left; font-size: 10px; }
        td { padding: 6px; border-bottom: 1px solid #eee; font-size: 10px; }
        tr:nth-child(even) { background-color: #f9f7f5; }
        .footer { text-align: center; margin-top: 30px; font-size: 9px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <h1>SonaraRent - Laporan Volume Penyewaan</h1>
    <p class="subtitle">
        Periode: {{ $dateRange }} 
        @if(isset($isHourly) && $isHourly)
            <span class="badge">Mode Per Jam</span>
        @elseif(isset($isMonthly) && $isMonthly)
            <span class="badge">Mode Per Bulan</span>
        @else
            <span class="badge">Mode Per Hari</span>
        @endif
        <br>Dicetak pada: {{ now()->format('d F Y H:i') }}
    </p>

    <div class="summary">
        <span class="label">Total Unit Disewakan:</span>
        <span class="value">{{ $totalVolume }} Unit</span>
    </div>

    @if(isset($isHourly) && $isHourly)
        {{-- Tabel untuk mode per jam --}}
        <table>
            <thead>
                <tr>
                    <th width="30" class="text-center">No</th>
                    <th width="100" class="text-center">Jam</th>
                    <th>Waktu (Detail)</th>
                    <th width="120" class="text-center">Volume Penyewaan (Unit)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $item['date_label'] }}</td>
                    <td>{{ $item['datetime'] ?? '-' }}</td>
                    <td class="text-center">{{ $item['count'] }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background-color: #fcfaf8; font-weight: bold;">
                    <td colspan="3" class="text-right">TOTAL</td>
                    <td class="text-center">{{ $totalVolume }}</td>
                </tr>
            </tfoot>
        </table>
    @else
        {{-- Tabel untuk mode per hari atau per bulan --}}
        <table>
            <thead>
                <tr>
                    <th width="30" class="text-center">No</th>
                    <th>{{ isset($isMonthly) && $isMonthly ? 'Bulan' : 'Tanggal' }}</th>
                    <th class="text-center">Volume Penyewaan (Unit)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item['date_label'] }}</td>
                    <td class="text-center">{{ $item['count'] }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background-color: #fcfaf8; font-weight: bold;">
                    <td colspan="2" class="text-right">TOTAL</td>
                    <td class="text-center">{{ $totalVolume }}</td>
                </tr>
            </tfoot>
        </table>
    @endif

    <p class="footer">© {{ date('Y') }} SonaraRent - Premium Instrument Rental Analytics</p>
</body>
</html>