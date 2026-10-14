<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->daftarkanFungsiMySqlDiSqlite();
    }

    // Test berjalan di SQLite in-memory, sedangkan DashboardController memakai fungsi MySQL MONTH().
    // Fungsi itu didaftarkan ke SQLite agar query yang sama bisa diuji tanpa mengubah kode aplikasi.
    private function daftarkanFungsiMySqlDiSqlite(): void
    {
        $koneksi = DB::connection();

        if ($koneksi->getDriverName() !== 'sqlite') {
            return;
        }

        $koneksi->getPdo()->sqliteCreateFunction(
            'MONTH',
            fn ($tanggal) => $tanggal === null ? null : (int) date('n', strtotime($tanggal)),
            1
        );
    }
}
