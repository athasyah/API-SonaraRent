<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan User</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #333; }
        h1 { text-align: center; color: #A47551; font-size: 20px; margin-bottom: 5px; }
        .subtitle { text-align: center; color: #666; font-size: 12px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #A47551; color: white; padding: 8px 6px; text-align: left; font-size: 10px; }
        td { padding: 6px; border-bottom: 1px solid #eee; font-size: 10px; }
        tr:nth-child(even) { background-color: #f9f7f5; }
        .footer { text-align: center; margin-top: 20px; font-size: 9px; color: #999; }
        .total { font-weight: bold; text-align: right; padding-top: 10px; }
    </style>
</head>
<body>
    <h1>SonaraRent - Laporan Data User</h1>
    <p class="subtitle">Dicetak pada: {{ now()->format('d F Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Email</th>
                <th>No. Telepon</th>
                <th>Role</th>
                <th>Tanggal Daftar</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $user)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->phone ?? '-' }}</td>
                <td>{{ ucfirst($user->getRoleNames()->first() ?? '-') }}</td>
                <td>{{ \Carbon\Carbon::parse($user->created_at)->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p class="total">Total Data: {{ count($data) }} user</p>
    <p class="footer">© {{ date('Y') }} SonaraRent - Premium Instrument Rental</p>
</body>
</html>
