<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bukti Transaksi #{{ $rental->id }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px; border-bottom: 1px solid #ddd; text-align: left; }
        .header { text-align: center; margin-bottom: 30px; }
        .total { text-align: right; font-weight: bold; font-size: 14px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>SonaraRent</h1>
        <p>Bukti Transaksi Penyewaan #{{ $rental->id }}</p>
        <p>Tanggal: {{ \Carbon\Carbon::parse($rental->created_at)->format('d F Y') }}</p>
    </div>

    <table style="width: 100%; margin-bottom: 30px;">
        <tr>
            <td style="border: none; width: 50%;">
                <strong>Pelanggan:</strong><br>
                {{ $rental->customer->name ?? 'N/A' }}<br>
                {{ $rental->customer->email ?? '' }}
            </td>
            <td style="border: none; text-align: right;">
                <strong>Status:</strong> {{ strtoupper($rental->status) }}<br>
                <strong>Pembayaran:</strong> {{ strtoupper($rental->payment_status) }}
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Instrumen</th>
                <th>Harga/Hari</th>
                <th>Durasi</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rental->details as $detail)
            <tr>
                <td>{{ $detail->instrument->name ?? 'Produk' }}</td>
                <td>Rp {{ number_format($detail->price_per_day, 0, ',', '.') }}</td>
                <td>{{ $detail->days }} Hari</td>
                <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @php
        $penaltyAmount = $rental->penalty->sum('amount') ?? 0;
    @endphp
    
    <div class="total">
        <p>Total Harga: Rp {{ number_format($rental->total_price, 0, ',', '.') }}</p>
        @if($penaltyAmount > 0)
            <p>Total Denda: Rp {{ number_format($penaltyAmount, 0, ',', '.') }}</p>
        @endif
        <hr>
        <p style="color: #A47551;">GRAND TOTAL: Rp {{ number_format($rental->total_price + $penaltyAmount, 0, ',', '.') }}</p>
    </div>

    <div style="margin-top: 50px; text-align: center; color: #888;">
        Terima kasih telah mempercayakan kebutuhan musik Anda kepada SonaraRent.
    </div>
</body>
</html>
