<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Rental</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #333; }
        h1 { text-align: center; color: #A47551; font-size: 20px; margin-bottom: 5px; }
        .subtitle { text-align: center; color: #666; font-size: 12px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #A47551; color: white; padding: 8px 6px; text-align: left; font-size: 10px; }
        td { padding: 6px; border-bottom: 1px solid #eee; font-size: 10px; }
        tr:nth-child(even) { background-color: #f9f7f5; }
        .status-pending { color: #d97706; }
        .status-approved { color: #2563eb; }
        .status-ongoing { color: #059669; }
        .status-returned { color: #6b7280; }
        .status-cancelled { color: #dc2626; }
        .footer { text-align: center; margin-top: 20px; font-size: 9px; color: #999; }
        .total { font-weight: bold; text-align: right; padding-top: 10px; }
    </style>
</head>
<body>
    <h1>SonaraRent - Laporan Data Rental</h1>
    <p class="subtitle">Dicetak pada: {{ now()->format('d F Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Customer</th>
                <th>Tgl Sewa</th>
                <th>Tgl Kembali (Rencana)</th>
                <th>Tgl Kembali (Aktual)</th>
                <th>Instrumen</th>
                @if(!($hideStatus ?? false))
                <th>Status</th>
                @endif
                <th>Harga Sewa</th>
                <th>Denda</th>
                <th>Keterangan Denda</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $rental)
            @php
                $penaltyAmount = $rental->penalty->sum('amount') ?? 0;
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $rental->customer->name ?? '-' }}</td>
                <td>{{ \Carbon\Carbon::parse($rental->rent_date)->format('d/m/Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($rental->return_date)->format('d/m/Y') }}</td>
                <td>{{ $rental->actual_return_date ? \Carbon\Carbon::parse($rental->actual_return_date)->format('d/m/Y H:i') : '-' }}</td>
                <td>{{ $rental->details->map(fn($d) => $d->instrument->name ?? '')->filter()->implode(', ') ?: '-' }}</td>
                @if(!($hideStatus ?? false))
                <td class="status-{{ $rental->status }}">{{ ucfirst($rental->status) }}</td>
                @endif
                <td>Rp {{ number_format($rental->total_price, 0, ',', '.') }}</td>
                <td style="{{ $penaltyAmount > 0 ? 'color: #dc2626; font-weight: bold;' : '' }}">
                    Rp {{ number_format($penaltyAmount, 0, ',', '.') }}
                </td>
                <td style="font-size: 9px; color: #666;">
                    {{ $rental->penalty->pluck('reason')->filter()->implode(', ') ?: '-' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p class="total">Total Data: {{ count($data) }} rental</p>
    <p class="footer">© {{ date('Y') }} SonaraRent - Premium Instrument Rental</p>
</body>
</html>
