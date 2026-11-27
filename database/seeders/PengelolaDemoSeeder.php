<?php

namespace Database\Seeders;

use App\Models\Meja;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\Pengelola;
use App\Models\SubOrder;
use App\Models\Tenant;
use App\Models\User;
use App\Services\MejaQrService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder demo terpisah untuk pengujian manual Panel Pengelola & Dashboard Analitik
 * sebelum integrasi penuh data transaksi riil dari Anggota 4.
 *
 * Jalankan secara eksplisit pada database lokal dengan:
 *   php artisan db:seed --class=PengelolaDemoSeeder
 *
 * Kredensial demo:
 * - Pengelola : username `admin_kupon` | password `password123`
 * - Tenant 1  : username `soto_betawi` | password `password123`
 * - Tenant 2  : username `kopi_dkupon` | password `password123`
 */
class PengelolaDemoSeeder extends Seeder
{
    public function run(): void
    {
        $qrService = app(MejaQrService::class);

        // 1. Akun Pengelola Demo
        $adminUser = User::firstOrCreate(
            ['username' => 'admin_kupon'],
            [
                'name' => 'Admin Sentra D Kupon',
                'password' => Hash::make('password123'),
                'role' => 'pengelola',
                'is_active' => true,
            ]
        );

        Pengelola::firstOrCreate(
            ['id_user' => $adminUser->id_user],
            [
                'nama' => 'Admin Sentra D Kupon',
                'no_hp' => '081200000001',
            ]
        );

        // 2. Akun & Profil Tenant Demo
        $tenant1User = User::firstOrCreate(
            ['username' => 'soto_betawi'],
            [
                'name' => 'H. Mamat',
                'password' => Hash::make('password123'),
                'role' => 'tenant',
                'is_active' => true,
            ]
        );

        $tenant1 = Tenant::firstOrCreate(
            ['id_user' => $tenant1User->id_user],
            [
                'nama_tenant' => 'Soto Betawi D\'Kupon',
                'nomor_kontak' => '081234567801',
                'deskripsi' => 'Soto betawi kuah santan & bening khas Jakarta.',
                'status_operasional' => Tenant::STATUS_OPERASIONAL_BUKA,
                'is_active' => true,
            ]
        );

        $tenant2User = User::firstOrCreate(
            ['username' => 'kopi_dkupon'],
            [
                'name' => 'Kedai Kopi D Kupon',
                'password' => Hash::make('password123'),
                'role' => 'tenant',
                'is_active' => true,
            ]
        );

        $tenant2 = Tenant::firstOrCreate(
            ['id_user' => $tenant2User->id_user],
            [
                'nama_tenant' => 'Kopi & Es D\'Kupon',
                'nomor_kontak' => '081234567802',
                'deskripsi' => 'Aneka kopi susu gula aren dan minuman segar.',
                'status_operasional' => Tenant::STATUS_OPERASIONAL_BUKA,
                'is_active' => true,
            ]
        );

        $tenant3User = User::firstOrCreate(
            ['username' => 'bakmi_arsip'],
            [
                'name' => 'Bakmi Arsip',
                'password' => Hash::make('password123'),
                'role' => 'tenant',
                'is_active' => false,
            ]
        );

        Tenant::firstOrCreate(
            ['id_user' => $tenant3User->id_user],
            [
                'nama_tenant' => 'Bakmi Nusantara (Arsip)',
                'nomor_kontak' => '081234567803',
                'deskripsi' => 'Contoh tenant nonaktif (soft-deactivation).',
                'status_operasional' => Tenant::STATUS_OPERASIONAL_TUTUP,
                'is_active' => false,
            ]
        );

        // 3. Meja & Generate QR Otomatis
        $mejaSpecs = [
            ['nomor_meja' => 'M-01', 'kapasitas' => 4, 'status' => Meja::STATUS_TERSEDIA],
            ['nomor_meja' => 'M-02', 'kapasitas' => 4, 'status' => Meja::STATUS_TERISI],
            ['nomor_meja' => 'M-03', 'kapasitas' => 6, 'status' => Meja::STATUS_TERSEDIA],
            ['nomor_meja' => 'M-04', 'kapasitas' => 2, 'status' => Meja::STATUS_TIDAK_AKTIF],
        ];

        $mejaUtama = null;
        foreach ($mejaSpecs as $spec) {
            $meja = Meja::firstOrCreate(
                ['nomor_meja' => $spec['nomor_meja']],
                ['kapasitas' => $spec['kapasitas'], 'status' => $spec['status']]
            );
            $qrService->generateAndStoreQrSvg($meja);
            $mejaUtama ??= $meja;
        }

        // 4. Menu & Transaksi Demo (sudah_dibayar vs menunggu_pembayaran)
        $menuSoto = Menu::firstOrCreate(
            ['id_tenant' => $tenant1->id_tenant, 'nama_menu' => 'Soto Betawi Daging'],
            ['kategori' => 'makanan', 'harga' => 35000, 'is_tersedia' => true, 'is_active' => true]
        );

        $menuKopi = Menu::firstOrCreate(
            ['id_tenant' => $tenant2->id_tenant, 'nama_menu' => 'Es Kopi Susu Aren'],
            ['kategori' => 'minuman', 'harga' => 20000, 'is_tersedia' => true, 'is_active' => true]
        );

        // Order 1: QRIS Lunas (masuk omzet valid: Rp 90.000)
        if (! Order::where('kode_order', 'ORD-DEMO-001')->exists()) {
            $order1 = Order::create([
                'kode_order' => 'ORD-DEMO-001',
                'id_meja' => $mejaUtama->id_meja,
                'nama_pembeli' => 'Budi Santoso',
                'metode_pembayaran' => Order::METODE_QRIS,
                'total_harga' => 90000,
                'status_pembayaran' => Order::STATUS_SUDAH_DIBAYAR,
                'waktu_pesan' => now()->subHours(2),
            ]);

            $sub1a = SubOrder::create([
                'id_order' => $order1->id_order,
                'id_tenant' => $tenant1->id_tenant,
                'kode_suborder' => 'SUB-DEMO-001-T1',
                'subtotal' => 70000,
                'status_pembayaran' => SubOrder::STATUS_PEMBAYARAN_SUDAH,
                'status_pesanan' => SubOrder::STATUS_PESANAN_SELESAI,
                'waktu_selesai' => now()->subHour(),
            ]);

            OrderDetail::create([
                'id_suborder' => $sub1a->id_suborder,
                'id_menu' => $menuSoto->id_menu,
                'nama_menu' => $menuSoto->nama_menu,
                'harga_satuan' => 35000,
                'jumlah' => 2,
                'subtotal' => 70000,
            ]);

            $sub1b = SubOrder::create([
                'id_order' => $order1->id_order,
                'id_tenant' => $tenant2->id_tenant,
                'kode_suborder' => 'SUB-DEMO-001-T2',
                'subtotal' => 20000,
                'status_pembayaran' => SubOrder::STATUS_PEMBAYARAN_SUDAH,
                'status_pesanan' => SubOrder::STATUS_PESANAN_SELESAI,
                'waktu_selesai' => now()->subHour(),
            ]);

            OrderDetail::create([
                'id_suborder' => $sub1b->id_suborder,
                'id_menu' => $menuKopi->id_menu,
                'nama_menu' => $menuKopi->nama_menu,
                'harga_satuan' => 20000,
                'jumlah' => 1,
                'subtotal' => 20000,
            ]);

            Payment::create([
                'id_order' => $order1->id_order,
                'id_suborder' => null,
                'kode_payment' => 'PAY-DEMO-001',
                'metode_pembayaran' => Payment::METODE_QRIS,
                'jumlah_bayar' => 90000,
                'status_pembayaran' => Payment::STATUS_BERHASIL,
                'waktu_pembayaran' => now()->subHours(2),
            ]);
        }

        // Order 2: Menunggu Pembayaran (TIDAK boleh dihitung ke omzet valid)
        if (! Order::where('kode_order', 'ORD-DEMO-002')->exists()) {
            $order2 = Order::create([
                'kode_order' => 'ORD-DEMO-002',
                'id_meja' => $mejaUtama->id_meja,
                'nama_pembeli' => 'Siti Aminah',
                'metode_pembayaran' => Order::METODE_TUNAI,
                'total_harga' => 35000,
                'status_pembayaran' => Order::STATUS_MENUNGGU_PEMBAYARAN,
                'waktu_pesan' => now()->subMinutes(15),
            ]);

            SubOrder::create([
                'id_order' => $order2->id_order,
                'id_tenant' => $tenant1->id_tenant,
                'kode_suborder' => 'SUB-DEMO-002-T1',
                'subtotal' => 35000,
                'status_pembayaran' => SubOrder::STATUS_PEMBAYARAN_MENUNGGU,
                'status_pesanan' => SubOrder::STATUS_PESANAN_MENUNGGU,
            ]);
        }
    }
}
