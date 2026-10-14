<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Payroll;
use App\Models\Employee;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

// Controller untuk mengelola data Penggajian (Payroll) — semua role dapat melihat, hanya HR yang bisa kelola
class PayrollController extends Controller
{
    // Menampilkan daftar data payroll sesuai role pengguna
    public function index()
    {
        if (session('role') == 'HR') {
            // HR melihat seluruh data payroll semua karyawan
            $payrolls = Payroll::with('employee')->get();
        } else {
            // Karyawan biasa hanya melihat slip gaji miliknya sendiri
            $payrolls = Payroll::with('employee')->where('employee_id', session('employee_id'))->get();
        }

        return view('payrolls.index', compact('payrolls'));
    }

    // Menghasilkan dan mengunduh slip gaji dalam format PDF
    public function downloadSlip($id)
    {
        // Ambil data payroll beserta relasi employee (role dan department) untuk ditampilkan di slip
        $payroll = Payroll::with(['employee.role', 'employee.department'])->findOrFail($id);
        $this->pastikanMilikSendiriAtauHR($payroll->employee_id); // Karyawan hanya boleh mengunduh slip miliknya

        // Buat URL verifikasi yang akan dienkode sebagai QR Code di dalam slip gaji
        $validationUrl = route('payroll.verify', [
            'id'   => $payroll->id,
            'hash' => md5($payroll->created_at . $payroll->id) // Hash unik untuk validasi keaslian dokumen
        ]);

        // Generate QR Code dalam format SVG, lalu encode base64 agar aman dimasukkan ke dalam PDF
        $qrcode = base64_encode(QrCode::format('svg')->size(100)->generate($validationUrl));

        // Muat view slip gaji sebagai PDF menggunakan library DomPDF
        $pdf = Pdf::loadView('pages.payroll.pdf_slip', [
            'payroll'  => $payroll,
            'employee' => $payroll->employee,
            'qrcode'   => $qrcode
        ]);

        // Buat nama file PDF yang rapi (spasi diganti tanda hubung)
        $filename = 'Slip-Gaji-' . str_replace(' ', '-', $payroll->employee->fullname) . '.pdf';

        // Langsung unduh file PDF ke browser pengguna
        return $pdf->download($filename);
    }

    // Menampilkan form untuk membuat data payroll baru
    public function create()
    {
        $employees = Employee::all(); // Daftar karyawan untuk dropdown pilihan
        return view('payrolls.create', compact('employees'));
    }

    // Memproses dan menyimpan data payroll baru ke database
    public function store(Request $request)
    {
        // Validasi input form payroll
        $request->validate($this->aturanValidasi($request));

        // Hitung gaji bersih: gaji pokok + bonus - potongan
        $netSalary = $request->input('salary') - $request->input('deductions') + $request->input('bonuses');

        // Tambahkan net_salary ke request agar ikut tersimpan
        $request->merge(['net_salary' => $netSalary]);

        Payroll::create($request->all()); // Simpan ke database

        return redirect()->route('payrolls.index')->with('success', 'Payroll created successfully');
    }

    // Menampilkan form edit untuk data payroll tertentu (Route Model Binding)
    public function edit(Payroll $payroll)
    {
        $employees = Employee::all(); // Daftar karyawan untuk dropdown
        return view('payrolls.edit', compact('payroll', 'employees'));
    }

    // Memproses dan menyimpan perubahan data payroll
    public function update(Request $request, Payroll $payroll)
    {
        // Validasi input perubahan
        $request->validate($this->aturanValidasi($request, $payroll));

        // Hitung ulang gaji bersih berdasarkan nilai baru
        $netSalary = $request->input('salary') - $request->input('deductions') + $request->input('bonuses');
        $request->merge(['net_salary' => $netSalary]);

        $payroll->update($request->all()); // Terapkan perubahan

        return redirect()->route('payrolls.index')->with('success', 'Payroll updated successfully');
    }

    // Menampilkan halaman detail (slip gaji) untuk satu data payroll
    public function show(Payroll $payroll)
    {
        $this->pastikanMilikSendiriAtauHR($payroll->employee_id);

        return view('payrolls.show', compact('payroll'));
    }

    // Menghapus data payroll (SoftDelete)
    public function destroy(Payroll $payroll)
    {
        $payroll->delete(); // Soft delete: mengisi deleted_at

        return redirect()->route('payrolls.index')->with('success', 'Payroll deleted successfully');
    }

    // Aturan validasi payroll: satu slip per karyawan per tanggal bayar (mencegah data ganda akibat klik ganda)
    private function aturanValidasi(Request $request, ?Payroll $payroll = null): array
    {
        $slipUnik = Rule::unique('payroll', 'pay_date')
            ->where('employee_id', $request->input('employee_id'))
            ->whereNull('deleted_at')
            ->ignore($payroll?->id);

        return [
            'employee_id' => 'required',
            'salary'      => 'required|numeric',
            'bonuses'     => 'required|numeric',
            'deductions'  => 'required|numeric',
            'pay_date'    => ['required', 'date', $slipUnik],
        ];
    }

    // Memverifikasi keaslian slip gaji melalui QR Code yang discan
    public function verify($id, $hash)
    {
        // Cari data payroll beserta informasi karyawan berdasarkan ID
        $payroll = \App\Models\Payroll::with('employee')->findOrFail($id);

        // Buat ulang hash dengan logika yang sama persis dengan saat slip diterbitkan
        $generatedHash = md5($payroll->created_at . $payroll->id);

        // Bandingkan hash dari QR Code dengan hash yang dibuat ulang
        if ($hash !== $generatedHash) {
            // Hash tidak cocok — dokumen kemungkinan palsu atau sudah dimanipulasi
            abort(403, 'QR Code tidak valid atau dokumen palsu.');
        }

        // Hash cocok — tampilkan halaman konfirmasi keaslian dokumen
        return view('pages.payroll.verify_result', [
            'payroll' => $payroll,
            'status'  => 'valid'
        ]);
    }
}
