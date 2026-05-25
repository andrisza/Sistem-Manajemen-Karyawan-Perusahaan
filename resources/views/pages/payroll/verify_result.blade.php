<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Slip Gaji</title>
    <style>
        body { font-family: sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px; }
        .card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); max-width: 400px; width: 100%; text-align: center; }
        .icon { font-size: 60px; color: #28a745; margin-bottom: 20px; }
        h2 { margin: 0 0 10px 0; color: #333; }
        .label { font-size: 12px; color: #777; margin-bottom: 2px; }
        .value { font-size: 16px; font-weight: bold; color: #333; margin-bottom: 15px; }
        .divider { border-top: 1px dashed #ddd; margin: 20px 0; }
        .footer { font-size: 11px; color: #aaa; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">✅</div>
        <h2>DOKUMEN VALID</h2>
        <p style="color: #666;">Slip gaji ini terdaftar resmi di sistem kami.</p>

        <div class="divider"></div>

        <div style="text-align: left;">
            <div class="label">Nama Karyawan</div>
            <div class="value">{{ $payroll->employee->fullname }}</div>

            <div class="label">Periode Gaji</div>
            <div class="value">{{ \Carbon\Carbon::parse($payroll->pay_date)->translatedFormat('d F Y') }}</div>

            <div class="label">Total Diterima</div>
            <div class="value">Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}</div>
            
            <div class="label">Dicetak Pada</div>
            <div class="value">{{ $payroll->created_at->format('d M Y H:i') }}</div>
        </div>

        <div class="footer">
            ID Dokumen: {{ $payroll->id }}<br>
            Hash: {{ substr(md5($payroll->created_at . $payroll->id), 0, 10) }}...
        </div>
    </div>
</body>
</html>