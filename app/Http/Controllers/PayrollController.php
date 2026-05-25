<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payroll;
use App\Models\Employee;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class PayrollController extends Controller
{
    public function index()
    {   
        // PERBAIKAN DI SINI
        if(session('role') == 'HR') {
            $payrolls = Payroll::all();
        } else {
            $payrolls = Payroll::where('employee_id', session('employee_id'))->get();
        }
        
        return view('payrolls.index', compact('payrolls'));
    }

        public function downloadSlip($id)
        {
            // 1. Ambil Data
            $payroll = Payroll::with(['employee.role', 'employee.department'])->findOrFail($id);

            // 2. Generate Link QR (Pastikan route 'payroll.verify' ada)
            // Jika ragu route-nya ada atau tidak, ganti route(...) dengan string kosong '' dulu untuk tes.
            $validationUrl = route('payroll.verify', [
                'id' => $payroll->id, 
                'hash' => md5($payroll->created_at . $payroll->id)
            ]);

            // 3. Render QR Code
            // Menggunakan base64 agar aman masuk PDF
            $qrcode = base64_encode(QrCode::format('svg')->size(100)->generate($validationUrl));

            // 4. Load View PDF
            $pdf = Pdf::loadView('pages.payroll.pdf_slip', [
                'payroll' => $payroll,
                'employee' => $payroll->employee,
                'qrcode' => $qrcode
            ]);

            // 5. RETURN HASILNYA (PENTING!)
            // Pastikan nama file tidak mengandung karakter aneh
            $filename = 'Slip-Gaji-' . str_replace(' ', '-', $payroll->employee->fullname) . '.pdf';
            
            return $pdf->download($filename);
        }

    public function create()
    {   
        $employees = Employee::all();
        return view('payrolls.create', compact('employees'));
    }
    public function store(Request $request) {
        $request->validate([
            'employee_id' => 'required',
            'salary' => 'required|numeric',
            'bonuses' => 'required|numeric',
            'deductions' => 'required|numeric',
            'pay_date' => 'required|date'

        ]);
        $netSalary = $request->input('salary') - $request->input('deductions') + $request->input('bonuses');

        $request->merge(['net_salary' => $netSalary]);

        Payroll::create($request->all()); 
        return redirect()->route('payrolls.index')->with('success', 'Payroll created successfully');
    }
    public function edit(Payroll $payroll) {
        $employees = Employee::all();

        return view ('payrolls.edit', compact('payroll', 'employees'));
    }

    public function update(Request $request, Payroll $payroll) {
        $request->validate([
            'employee_id' => 'required',
            'salary' => 'required|numeric',
            'bonuses' => 'required|numeric',
            'deductions' => 'required|numeric',
            'pay_date' => 'required|date'

        ]);
        $netSalary = $request->input('salary') - $request->input('deductions') + $request->input('bonuses');
        $request->merge(['net_salary' => $netSalary]);
        $payroll->update($request->all());
        return redirect()->route('payrolls.index')->with('success', 'Payroll updated successfully');

    }
    public function show(Payroll $payroll){ 
        return view('payrolls.show', compact('payroll'));

    }


    public function destroy(Payroll $payroll) {
        $payroll->delete();

        return redirect()->route('payrolls.index')->with('success', 'Payroll deleted sucessfully');
    }

    // --- TAMBAHKAN KODE INI ---
    public function verify($id, $hash)
    {
        // 1. Cari data payroll berdasarkan ID
        $payroll = \App\Models\Payroll::with('employee')->findOrFail($id);

        // 2. Buat ulang hash untuk dicocokkan (Logic harus sama persis dengan downloadSlip)
        $generatedHash = md5($payroll->created_at . $payroll->id);

        // 3. Cek validitas
        if ($hash !== $generatedHash) {
            abort(403, 'QR Code tidak valid atau dokumen palsu.');
        }

        // 4. Jika valid, tampilkan halaman verifikasi
        // Kita return view sederhana atau string dulu
        return view('pages.payroll.verify_result', [
            'payroll' => $payroll,
            'status' => 'valid'
        ]);
    }
}