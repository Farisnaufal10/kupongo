<?php

namespace Tests\Feature;

use App\Models\Meja;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\Pengelola;
use App\Models\SubOrder;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseFoundationTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Memverifikasi bahwa seluruh 9 tabel inti KuponGo ada pada database.
     */
    public function test_all_nine_core_tables_exist(): void
    {
        $tables = [
            'users',
            'pengelola',
            'tenants',
            'meja',
            'menu',
            'orders',
            'sub_orders',
            'order_details',
            'payments',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(Schema::hasTable($table), "Tabel {$table} harus tersedia di database.");
        }
    }

    /**
     * Memverifikasi struktur kolom kunci primer sesuai ERD.
     */
    public function test_primary_keys_match_erd(): void
    {
        $this->assertTrue(Schema::hasColumn('users', 'id_user'));
        $this->assertTrue(Schema::hasColumn('pengelola', 'id_pengelola'));
        $this->assertTrue(Schema::hasColumn('tenants', 'id_tenant'));
        $this->assertTrue(Schema::hasColumn('meja', 'id_meja'));
        $this->assertTrue(Schema::hasColumn('menu', 'id_menu'));
        $this->assertTrue(Schema::hasColumn('orders', 'id_order'));
        $this->assertTrue(Schema::hasColumn('sub_orders', 'id_suborder'));
        $this->assertTrue(Schema::hasColumn('order_details', 'id_detail'));
        $this->assertTrue(Schema::hasColumn('payments', 'id_payment'));
    }

    /**
     * Menguji unique constraint pada username di tabel users.
     */
    public function test_users_username_is_unique(): void
    {
        User::create([
            'username' => 'admin_test',
            'name' => 'Admin Test',
            'password' => 'secret123',
            'role' => 'pengelola',
            'is_active' => true,
        ]);

        $this->expectException(QueryException::class);

        User::create([
            'username' => 'admin_test', // duplikat
            'name' => 'Admin Test Duplikat',
            'password' => 'secret456',
            'role' => 'pengelola',
            'is_active' => true,
        ]);
    }

    /**
     * Menguji relasi one-to-one dan unique constraint id_user pada tabel pengelola.
     */
    public function test_pengelola_unique_user_and_relationship(): void
    {
        $user = User::create([
            'username' => 'pengelola_1',
            'name' => 'Budi Pengelola',
            'password' => 'secret',
            'role' => 'pengelola',
        ]);

        $pengelola = Pengelola::create([
            'id_user' => $user->id_user,
            'nama' => 'Budi Pengelola',
            'no_hp' => '081234567890',
        ]);

        $this->assertInstanceOf(User::class, $pengelola->user);
        $this->assertEquals($user->id_user, $pengelola->user->id_user);
        $this->assertInstanceOf(Pengelola::class, $user->pengelola);
        $this->assertTrue($user->isPengelola());

        // Pengujian unique: satu user tidak boleh memiliki dua entri pengelola
        $this->expectException(QueryException::class);
        Pengelola::create([
            'id_user' => $user->id_user,
            'nama' => 'Pengelola Kloning',
        ]);
    }

    /**
     * Menguji relasi one-to-one dan unique constraint id_user pada tabel tenants.
     */
    public function test_tenant_unique_user_and_relationship(): void
    {
        $user = User::create([
            'username' => 'tenant_ayam',
            'name' => 'Pak Joko',
            'password' => 'secret',
            'role' => 'tenant',
        ]);

        $tenant = Tenant::create([
            'id_user' => $user->id_user,
            'nama_tenant' => 'Ayam Geprek Mas Joko',
            'status_operasional' => 'buka',
            'is_active' => true,
        ]);

        $this->assertInstanceOf(User::class, $tenant->user);
        $this->assertEquals($user->id_user, $tenant->user->id_user);
        $this->assertTrue($tenant->isBuka());
        $this->assertTrue($user->isTenant());

        // Pengujian unique constraint
        $this->expectException(QueryException::class);
        Tenant::create([
            'id_user' => $user->id_user,
            'nama_tenant' => 'Ayam Geprek Cabang',
        ]);
    }

    /**
     * Menguji unique constraint pada nomor meja.
     */
    public function test_nomor_meja_is_unique(): void
    {
        Meja::create([
            'nomor_meja' => 'M-01',
            'kapasitas' => 4,
            'status' => 'tersedia',
        ]);

        $this->expectException(QueryException::class);

        Meja::create([
            'nomor_meja' => 'M-01', // duplikat
            'kapasitas' => 2,
        ]);
    }

    /**
     * Menguji unique constraint pada kode_order (master order) dan kode_suborder.
     */
    public function test_order_codes_are_unique(): void
    {
        $meja = Meja::create(['nomor_meja' => 'M-02']);

        $order = Order::create([
            'kode_order' => 'ORD-20261112-0001',
            'id_meja' => $meja->id_meja,
            'metode_pembayaran' => 'qris',
            'total_harga' => 50000.00,
            'status_pembayaran' => 'menunggu_pembayaran',
            'waktu_pesan' => now(),
        ]);

        $this->expectException(QueryException::class);

        Order::create([
            'kode_order' => 'ORD-20261112-0001', // duplikat
            'id_meja' => $meja->id_meja,
            'metode_pembayaran' => 'tunai',
            'total_harga' => 25000.00,
            'status_pembayaran' => 'menunggu_pembayaran',
            'waktu_pesan' => now(),
        ]);
    }

    /**
     * Menguji relasi lengkap: Meja -> Order -> SubOrder -> OrderDetail -> Menu.
     */
    public function test_full_order_hierarchy_and_snapshot(): void
    {
        // 1. Setup tenant & menu
        $userTenant = User::create([
            'username' => 'tenant_mie',
            'password' => 'secret',
            'role' => 'tenant',
        ]);
        $tenant = Tenant::create([
            'id_user' => $userTenant->id_user,
            'nama_tenant' => 'Bakmie Sentra',
            'status_operasional' => 'buka',
        ]);
        $menu = Menu::create([
            'id_tenant' => $tenant->id_tenant,
            'nama_menu' => 'Mie Ayam Spesial',
            'kategori' => 'makanan',
            'harga' => 20000.00,
            'is_tersedia' => true,
        ]);

        // 2. Setup Meja & Master Order
        $meja = Meja::create(['nomor_meja' => 'M-03']);
        $order = Order::create([
            'kode_order' => 'ORD-20261112-0002',
            'id_meja' => $meja->id_meja,
            'nama_pembeli' => 'Ahmad',
            'metode_pembayaran' => 'tunai',
            'total_harga' => 40000.00,
            'status_pembayaran' => 'menunggu_pembayaran',
            'waktu_pesan' => now(),
        ]);

        // 3. Setup SubOrder
        $subOrder = SubOrder::create([
            'id_order' => $order->id_order,
            'id_tenant' => $tenant->id_tenant,
            'kode_suborder' => 'SUB-ORD-0002-T1',
            'subtotal' => 40000.00,
            'status_pembayaran' => 'menunggu_pembayaran',
            'status_pesanan' => 'menunggu',
        ]);

        // 4. Setup OrderDetail dengan snapshot harga & nama menu
        $detail = OrderDetail::create([
            'id_suborder' => $subOrder->id_suborder,
            'id_menu' => $menu->id_menu,
            'nama_menu' => $menu->nama_menu,     // Snapshot
            'harga_satuan' => $menu->harga,      // Snapshot 20000.00
            'jumlah' => 2,
            'subtotal' => 40000.00,
            'catatan' => 'Porsi banyak',
        ]);

        // Verifikasi relasi
        $this->assertEquals(1, $order->subOrders->count());
        $this->assertEquals(1, $subOrder->orderDetails->count());
        $this->assertEquals(1, $order->orderDetails->count());
        $this->assertEquals('Mie Ayam Spesial', $order->orderDetails->first()->nama_menu);

        // Simulasi perubahan harga menu master: snapshot histori pesanan tidak boleh terpengaruh
        $menu->update(['harga' => 25000.00, 'nama_menu' => 'Mie Ayam Jumbo']);
        $detail->refresh();

        $this->assertEquals('20000.00', $detail->harga_satuan);
        $this->assertEquals('Mie Ayam Spesial', $detail->nama_menu);
    }

    /**
     * Menguji mekanisme pembayaran ganda (QRIS master order vs Tunai per sub-order).
     */
    public function test_qris_and_cash_payment_mechanisms(): void
    {
        $meja = Meja::create(['nomor_meja' => 'M-04']);

        $userT1 = User::create(['username' => 'tenant_t1', 'password' => 'secret', 'role' => 'tenant']);
        $t1 = Tenant::create(['id_user' => $userT1->id_user, 'nama_tenant' => 'Tenant 1']);

        $userT2 = User::create(['username' => 'tenant_t2', 'password' => 'secret', 'role' => 'tenant']);
        $t2 = Tenant::create(['id_user' => $userT2->id_user, 'nama_tenant' => 'Tenant 2']);

        // Skenario 1: Pembayaran QRIS (id_suborder is null, berlaku master order)
        $orderQris = Order::create([
            'kode_order' => 'ORD-QRIS-0001',
            'id_meja' => $meja->id_meja,
            'metode_pembayaran' => 'qris',
            'total_harga' => 50000.00,
            'status_pembayaran' => 'menunggu_pembayaran',
            'waktu_pesan' => now(),
        ]);

        $subQris1 = SubOrder::create([
            'id_order' => $orderQris->id_order,
            'id_tenant' => $t1->id_tenant,
            'kode_suborder' => 'SUB-QRIS-0001-T1',
            'subtotal' => 30000.00,
        ]);

        $subQris2 = SubOrder::create([
            'id_order' => $orderQris->id_order,
            'id_tenant' => $t2->id_tenant,
            'kode_suborder' => 'SUB-QRIS-0001-T2',
            'subtotal' => 20000.00,
        ]);

        $paymentQris = Payment::create([
            'id_order' => $orderQris->id_order,
            'id_suborder' => null, // Pembayaran QRIS master order
            'kode_payment' => 'PAY-QRIS-0001',
            'idempotency_key' => 'idemp-uuid-qris-001',
            'transaction_id' => 'midtrans-trans-12345',
            'metode_pembayaran' => 'qris',
            'jumlah_bayar' => 50000.00,
            'status_pembayaran' => 'berhasil',
            'waktu_pembayaran' => now(),
        ]);

        $this->assertTrue($paymentQris->isMasterOrderPayment());
        $this->assertFalse($paymentQris->isSubOrderPayment());
        $this->assertNull($paymentQris->subOrder);
        $this->assertEquals($orderQris->id_order, $paymentQris->order->id_order);

        // Skenario 2: Pembayaran Tunai per Sub-Order
        $orderCash = Order::create([
            'kode_order' => 'ORD-CASH-0001',
            'id_meja' => $meja->id_meja,
            'metode_pembayaran' => 'tunai',
            'total_harga' => 50000.00,
            'status_pembayaran' => 'sebagian_dibayar',
            'waktu_pesan' => now(),
        ]);

        $subCash1 = SubOrder::create([
            'id_order' => $orderCash->id_order,
            'id_tenant' => $t1->id_tenant,
            'kode_suborder' => 'SUB-CASH-0001-T1',
            'subtotal' => 30000.00,
            'status_pembayaran' => 'sudah_dibayar',
        ]);

        $paymentCash1 = Payment::create([
            'id_order' => $orderCash->id_order,
            'id_suborder' => $subCash1->id_suborder, // Pembayaran tunai untuk sub-order T1
            'kode_payment' => 'PAY-CASH-0001',
            'idempotency_key' => 'cash-proof-t1-001',
            'metode_pembayaran' => 'tunai',
            'jumlah_bayar' => 30000.00,
            'status_pembayaran' => 'berhasil',
            'waktu_pembayaran' => now(),
        ]);

        $this->assertFalse($paymentCash1->isMasterOrderPayment());
        $this->assertTrue($paymentCash1->isSubOrderPayment());
        $this->assertNotNull($paymentCash1->subOrder);
        $this->assertEquals($subCash1->id_suborder, $paymentCash1->subOrder->id_suborder);
    }

    /**
     * Memverifikasi perlindungan riwayat transaksi:
     * ON DELETE RESTRICT mencegah penghapusan master order dan sub-order secara tidak sengaja.
     */
    public function test_restrict_delete_protects_transaction_history(): void
    {
        $meja = Meja::create(['nomor_meja' => 'M-05']);
        $user = User::create(['username' => 'tenant_restrict', 'password' => 'secret', 'role' => 'tenant']);
        $tenant = Tenant::create(['id_user' => $user->id_user, 'nama_tenant' => 'Tenant Restrict']);

        $order = Order::create([
            'kode_order' => 'ORD-RESTRICT-0001',
            'id_meja' => $meja->id_meja,
            'metode_pembayaran' => 'qris',
            'total_harga' => 15000.00,
            'waktu_pesan' => now(),
        ]);

        $subOrder = SubOrder::create([
            'id_order' => $order->id_order,
            'id_tenant' => $tenant->id_tenant,
            'kode_suborder' => 'SUB-RESTRICT-0001',
            'subtotal' => 15000.00,
        ]);

        $payment = Payment::create([
            'id_order' => $order->id_order,
            'kode_payment' => 'PAY-RESTRICT-0001',
            'metode_pembayaran' => 'qris',
            'jumlah_bayar' => 15000.00,
        ]);

        // Percobaan 1: Menghapus master order ketika memiliki sub_orders dan payments harus DITOLAK
        try {
            $order->delete();
            $this->fail('Master order seharusnya tidak dapat dihapus karena memiliki relasi historis (restrict).');
        } catch (QueryException $e) {
            $this->assertStringContainsString('Integrity constraint violation', $e->getMessage());
        }

        // Percobaan 2: Menghapus sub_order yang memiliki order_details harus DITOLAK
        $menu = Menu::create([
            'id_tenant' => $tenant->id_tenant,
            'nama_menu' => 'Nasi Goreng',
            'harga' => 15000.00,
        ]);
        OrderDetail::create([
            'id_suborder' => $subOrder->id_suborder,
            'id_menu' => $menu->id_menu,
            'nama_menu' => 'Nasi Goreng',
            'harga_satuan' => 15000.00,
            'jumlah' => 1,
            'subtotal' => 15000.00,
        ]);

        try {
            $subOrder->delete();
            $this->fail('Sub-order seharusnya tidak dapat dihapus karena memiliki rincian item (restrict).');
        } catch (QueryException $e) {
            $this->assertStringContainsString('Integrity constraint violation', $e->getMessage());
        }
    }

    /**
     * Memverifikasi integritas pembayaran:
     * payments.id_suborder wajib berasal dari master order yang sama dengan payments.id_order.
     */
    public function test_payment_id_suborder_must_belong_to_same_order(): void
    {
        $meja = Meja::create(['nomor_meja' => 'M-06']);
        $user = User::create(['username' => 'tenant_integrity', 'password' => 'secret', 'role' => 'tenant']);
        $tenant = Tenant::create(['id_user' => $user->id_user, 'nama_tenant' => 'Tenant Integrity']);

        // Order 1 & SubOrder 1
        $order1 = Order::create([
            'kode_order' => 'ORD-INTEG-0001',
            'id_meja' => $meja->id_meja,
            'metode_pembayaran' => 'tunai',
            'total_harga' => 20000.00,
            'waktu_pesan' => now(),
        ]);
        $subOrder1 = SubOrder::create([
            'id_order' => $order1->id_order,
            'id_tenant' => $tenant->id_tenant,
            'kode_suborder' => 'SUB-INTEG-0001',
            'subtotal' => 20000.00,
        ]);

        // Order 2 & SubOrder 2
        $order2 = Order::create([
            'kode_order' => 'ORD-INTEG-0002',
            'id_meja' => $meja->id_meja,
            'metode_pembayaran' => 'tunai',
            'total_harga' => 30000.00,
            'waktu_pesan' => now(),
        ]);
        $subOrder2 = SubOrder::create([
            'id_order' => $order2->id_order,
            'id_tenant' => $tenant->id_tenant,
            'kode_suborder' => 'SUB-INTEG-0002',
            'subtotal' => 30000.00,
        ]);

        // Uji anomali: Membayar Order 1 tapi mencantumkan id_suborder milik Order 2
        $this->expectException(\InvalidArgumentException::class);

        Payment::create([
            'id_order' => $order1->id_order,
            'id_suborder' => $subOrder2->id_suborder, // SALAH: subOrder2 milik order2!
            'kode_payment' => 'PAY-ILLEGAL-0001',
            'metode_pembayaran' => 'tunai',
            'jumlah_bayar' => 20000.00,
        ]);
    }
}
