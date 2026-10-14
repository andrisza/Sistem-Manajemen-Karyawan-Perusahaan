# Automasi Test Case HIRO (Pest)


Setiap nama test diawali kode test case (`TCU-F-01`, `TCT-HIR-F-19`, ...) agar hasil run bisa dicocokkan langsung dengan dokumen.

| File | Kode |
|---|---|
| `Utama/AutentikasiOtorisasiTest.php` | TCU-F-01 – F-08 |
| `Utama/ManajemenDataMasterTest.php` | TCU-F-09 – F-15, F-20, F-21 |
| `Utama/DashboardLaporanTest.php` | TCU-F-16 – F-19 |
| `Utama/NonFungsionalTest.php` | TCU-N-01 – N-17 |
| `Tambahan/AktivasiAkunTest.php` | TCT-HIR-F-01 – F-07 |
| `Tambahan/AbsensiGpsTest.php` | TCT-HIR-F-08 – F-14 |
| `Tambahan/PengajuanCutiTest.php` | TCT-HIR-F-15 – F-18 |
| `Tambahan/PenggajianSlipQrTest.php` | TCT-HIR-F-19 – F-26 |
| `Tambahan/TugasDanPenghapusanKaryawanTest.php` | TCT-HIR-F-27 – F-30 |
| `Tambahan/NonFungsionalHiroTest.php` | TCT-HIR-N-01 – N-10 |

Helper data uji ada di `tests/Support/HiroHelpers.php`.

## Menjalankan

```bash
./vendor/bin/pest tests/Feature/Hiro            # semua
./vendor/bin/pest --group=tcu                    # Test Case Utama saja
./vendor/bin/pest --group=tct-hir                # Test Case Tambahan HIR saja
./vendor/bin/pest --group=hiro --exclude-group=performa   # tanpa uji volume/beban
./vendor/bin/pest tests/Feature/Hiro --filter="TCT-HIR-F-0"  # per kode
```

## Membaca hasil

- **✓ passed** = Acceptance Criteria *Pass* terpenuhi.
- **⨯ failed** = Acceptance Criteria *Fail* terjadi → cacat sistem (bukan cacat test). Assertion ditulis sesuai kriteria *Pass* di dokumen.
- **skipped** = tidak bisa diotomasi lewat HTTP test; alasan diawali `MANUAL`, `ALAT LAIN`, `MONITORING`, `INFRA`, atau `BLOCKED` (fitur/aturan belum ada di SKPL).

Test berjalan di SQLite in-memory (lihat `phpunit.xml`). Fungsi MySQL `MONTH()` yang dipakai dashboard didaftarkan ke SQLite di `tests/TestCase.php`.
