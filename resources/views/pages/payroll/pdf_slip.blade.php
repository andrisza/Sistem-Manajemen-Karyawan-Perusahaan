<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Slip Gaji - {{ $employee->name }}</title>
    <style>
        /* --- STYLE SAMA SEPERTI SEBELUMNYA --- */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', Times, serif; line-height: 1.6; color: #333; padding: 20px; }
        .container { max-width: 210mm; margin: 0 auto; padding: 35px 40px; }
        .header { text-align: center; border-bottom: 3px solid #000; margin-bottom: 20px; padding-bottom: 10px; }
        .header-content { display: flex; justify-content: center; align-items: center; gap: 30px; }
        .header-text { text-align: center; }
        .header-text .company-name { font-size: 16px; font-weight: bold; text-transform: uppercase; margin-bottom: 5px; }
        .header-text .address { font-size: 11px; color: #555; }
        .document-title { text-align: center; margin: 15px 0; }
        .document-title h2 { font-size: 18px; font-weight: bold; text-transform: uppercase; text-decoration: underline; }
        .period-text { text-align: center; font-size: 12px; margin-bottom: 20px; color: #555; }
        .content { font-size: 12px; }
        .employee-info { margin-bottom: 20px; width: 100%; border-collapse: collapse; }
        .employee-info td { padding: 3px 0; vertical-align: top; }
        .label { font-weight: bold; width: 120px; }
        .separator { width: 15px; text-align: center; }
        .salary-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; border: 1px solid #000; }
        .salary-table th, .salary-table td { border: 1px solid #000; padding: 8px; }
        .salary-table th { background-color: #f0f0f0; text-align: left; text-transform: uppercase; font-size: 11px; }
        .amount { text-align: right; font-family: 'Courier New', Courier, monospace; }
        .total-row { font-weight: bold; background-color: #eef; }
        .signature-section { display: flex; justify-content: space-between; margin-top: 30px; }
        .signature-box { width: 40%; text-align: center; }
        .signature-date { font-size: 11px; margin-bottom: 10px; }
        .signature-qrcode img { width: 80px; height: 80px; padding: 5px; border: 1px solid #ddd; }
        .signature-name { font-weight: bold; text-decoration: underline; margin-top: 10px; }
        .signature-title { font-size: 11px; margin-bottom: 40px; } 
        .footer { text-align: center; font-size: 9px; color: #999; margin-top: 30px; border-top: 1px dotted #ccc; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <div class="header-text">
                    <div class="company-name">PT. NUSANTARA HUMAN CAPITAL</div>
                    <div class="address">Jl. Teknologi No. 123, Surabaya, Jawa Timur 60111</div>
                </div>
            </div>
        </div>

        <div class="document-title">
            <h2>SLIP GAJI</h2>
        </div>
        <div class="period-text">
            Tanggal Pembayaran: {{ \Carbon\Carbon::parse($payroll->pay_date)->translatedFormat('d F Y') }}
        </div>

        <div class="content">
            <table class="employee-info">
                <tr>
                    <td class="label">Nama Karyawan</td>
                    <td class="separator">:</td>
                    <td>{{ $employee->fullname }}</td> 
                    
                    <td class="label">Jabatan</td>
                    <td class="separator">:</td>
                    <td>{{ $employee->role->title ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">NIP / ID</td>
                    <td class="separator">:</td>
                    <td>{{ $employee->id }}</td>
                    
                    <td class="label">Departemen</td>
                    <td class="separator">:</td>
                    <td>{{ $employee->department->name ?? '-' }}</td>
                </tr>
            </table>

            <table class="salary-table">
                <thead>
                    <tr>
                        <th style="width: 50%;">KETERANGAN</th>
                        <th style="width: 25%; text-align:center;">POTONGAN</th>
                        <th style="width: 25%; text-align:center;">PENERIMAAN</th>
                    </tr>
                </thead>
                    <tbody>
                        <tr>
                            <td>Gaji Pokok</td>
                            <td style="background-color: #fafafa;"></td> <td class="amount">Rp {{ number_format($payroll->salary, 0, ',', '.') }}</td>
                        </tr>

                        <tr>
                            <td>Bonus / Tunjangan</td>
                            <td style="background-color: #fafafa;"></td>
                            <td class="amount">Rp {{ number_format($payroll->bonuses, 0, ',', '.') }}</td>
                        </tr>

                        <tr>
                            <td>Total Potongan</td>
                            <td class="amount" style="color: #dc3545;">
                                Rp {{ number_format($payroll->deductions, 0, ',', '.') }}
                            </td>
                            <td style="background-color: #fafafa;"></td>
                        </tr>

                        <tr class="total-row">
                            <td style="text-align: right; font-weight:bold; padding-right: 15px;">
                                TOTAL DITERIMA (Take Home Pay)
                            </td>
                            <td colspan="2" class="amount" style="font-size: 14px; background-color: #eef;">
                                Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
            </table>

            <p style="font-size: 11px; font-style: italic;">
                * Gaji ditransfer ke rekening karyawan terdaftar.
            </p>
        </div>

        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-date">Diterima oleh,</div>
                <div style="height: 80px;"></div> 
                <div class="signature-name">{{ $employee->name }}</div>
            </div>

            <div class="signature-box">
                <div class="signature-date">
                    Surabaya, {{ \Carbon\Carbon::parse($payroll->pay_date)->translatedFormat('d F Y') }}
                </div>
                <div class="signature-title">Finance Dept.</div>
                
                <div class="signature-qrcode">
                    <img src="data:image/svg+xml;base64,{{ $qrcode }}" alt="QR Validation">
                </div>
                
                <div class="signature-name">Admin HR</div>
            </div>
        </div>

        <div class="footer">
            PT. Nusantara Human Capital — Hiro
        </div>
    </div>
</body>
</html>