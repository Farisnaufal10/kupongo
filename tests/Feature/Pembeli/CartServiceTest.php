<?php

namespace Tests\Feature\Pembeli;

use App\Models\Menu;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Pembeli\CartService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * CartServiceTest — pengujian CartService secara langsung.
 *
 * Menggunakan DatabaseTransactions agar data uji tidak tersisa di kupongo_test.
 * Session di-reset sebelum setiap test via withSession([]).
 */
class CartServiceTest extends TestCase
{
    use DatabaseTransactions;

    private CartService $cart;

    // -------------------------------------------------------------------------
    // Helper factories
    // -------------------------------------------------------------------------

    private function buatTenantBuka(string $nama = 'Tenant Buka'): Tenant
    {
        $user = User::create([
            'username'  => 'u_' . uniqid(),
            'name'      => $nama,
            'password'  => bcrypt('pw'),
            'role'      => 'tenant',
            'is_active' => true,
        ]);
        return Tenant::create([
            'id_user'            => $user->id_user,
            'nama_tenant'        => $nama,
            'status_operasional' => Tenant::STATUS_OPERASIONAL_BUKA,
            'is_active'          => true,
        ]);
    }

    private function buatTenantTutup(string $nama = 'Tenant Tutup'): Tenant
    {
        $user = User::create([
            'username'  => 'u_' . uniqid(),
            'name'      => $nama,
            'password'  => bcrypt('pw'),
            'role'      => 'tenant',
            'is_active' => true,
        ]);
        return Tenant::create([
            'id_user'            => $user->id_user,
            'nama_tenant'        => $nama,
            'status_operasional' => Tenant::STATUS_OPERASIONAL_TUTUP,
            'is_active'          => true,
        ]);
    }

    private function buatMenu(Tenant $tenant, bool $tersedia = true, float $harga = 25000): Menu
    {
        return Menu::create([
            'id_tenant'   => $tenant->id_tenant,
            'nama_menu'   => 'Menu ' . uniqid(),
            'harga'       => $harga,
            'is_tersedia' => $tersedia,
            'is_active'   => true,
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        // Gunakan session array agar terisolasi per test
        config(['session.driver' => 'array']);
        $this->cart = new CartService();
    }

    // =========================================================================
    // CartService — tambah
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function tambah_menu_tersedia_berhasil(): void
    {
        $tenant = $this->buatTenantBuka('T1');
        $menu   = $this->buatMenu($tenant);

        $result = $this->cart->tambah($menu->id_menu);

        $this->assertTrue($result['ok']);
        $this->assertCount(1, $this->cart->all());
        $this->assertSame(1, $this->cart->totalUnit());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function tambah_menu_yang_sama_menambah_jumlah(): void
    {
        $tenant = $this->buatTenantBuka('T2');
        $menu   = $this->buatMenu($tenant);

        $this->cart->tambah($menu->id_menu);
        $this->cart->tambah($menu->id_menu);

        $this->assertSame(2, $this->cart->all()[$menu->id_menu]['jumlah']);
        $this->assertSame(2, $this->cart->totalUnit());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function tambah_menu_dari_dua_tenant_berbeda(): void
    {
        $t1  = $this->buatTenantBuka('Tenant A');
        $t2  = $this->buatTenantBuka('Tenant B');
        $m1  = $this->buatMenu($t1);
        $m2  = $this->buatMenu($t2);

        $this->cart->tambah($m1->id_menu);
        $this->cart->tambah($m2->id_menu);

        $groups = $this->cart->groupedByTenant();

        $this->assertCount(2, $groups);
        $this->assertSame(2, $this->cart->totalUnit());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function tambah_menu_habis_ditolak(): void
    {
        $tenant = $this->buatTenantBuka('T3');
        $menu   = $this->buatMenu($tenant, false); // is_tersedia = false

        $result = $this->cart->tambah($menu->id_menu);

        $this->assertFalse($result['ok']);
        $this->assertEmpty($this->cart->all());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function tambah_menu_tenant_tutup_ditolak(): void
    {
        $tenant = $this->buatTenantTutup('T4');
        $menu   = $this->buatMenu($tenant, true);

        $result = $this->cart->tambah($menu->id_menu);

        $this->assertFalse($result['ok']);
        $this->assertEmpty($this->cart->all());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function harga_diambil_dari_database_bukan_input(): void
    {
        $tenant = $this->buatTenantBuka('T5');
        $menu   = $this->buatMenu($tenant, true, 50000);

        $this->cart->tambah($menu->id_menu);

        $item = $this->cart->all()[$menu->id_menu];
        $this->assertSame(50000.0, $item['harga']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function tambah_menu_dengan_catatan(): void
    {
        $tenant = $this->buatTenantBuka('T6');
        $menu   = $this->buatMenu($tenant);

        $this->cart->tambah($menu->id_menu, 'tidak pedas');

        $item = $this->cart->all()[$menu->id_menu];
        $this->assertSame('tidak pedas', $item['catatan']);
    }

    // =========================================================================
    // CartService — setJumlah / kurangi
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function set_jumlah_memperbarui_jumlah_item(): void
    {
        $tenant = $this->buatTenantBuka('T7');
        $menu   = $this->buatMenu($tenant);
        $this->cart->tambah($menu->id_menu);

        $result = $this->cart->setJumlah($menu->id_menu, 5);

        $this->assertTrue($result['ok']);
        $this->assertSame(5, $this->cart->all()[$menu->id_menu]['jumlah']);
        $this->assertSame(5, $this->cart->totalUnit());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function set_jumlah_nol_menghapus_item(): void
    {
        $tenant = $this->buatTenantBuka('T8');
        $menu   = $this->buatMenu($tenant);
        $this->cart->tambah($menu->id_menu);

        $result = $this->cart->setJumlah($menu->id_menu, 0);

        $this->assertTrue($result['ok']);
        $this->assertEmpty($this->cart->all());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function kurangi_mengurangi_jumlah_item(): void
    {
        $tenant = $this->buatTenantBuka('T9');
        $menu   = $this->buatMenu($tenant);
        $this->cart->tambah($menu->id_menu);
        $this->cart->tambah($menu->id_menu);

        $this->cart->kurangi($menu->id_menu);

        $this->assertSame(1, $this->cart->all()[$menu->id_menu]['jumlah']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function kurangi_saat_jumlah_satu_menghapus_item(): void
    {
        $tenant = $this->buatTenantBuka('T10');
        $menu   = $this->buatMenu($tenant);
        $this->cart->tambah($menu->id_menu);

        $this->cart->kurangi($menu->id_menu);

        $this->assertEmpty($this->cart->all());
        $this->assertTrue($this->cart->isEmpty());
    }

    // =========================================================================
    // CartService — hapus
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function hapus_item_dari_keranjang(): void
    {
        $tenant = $this->buatTenantBuka('T11');
        $menu   = $this->buatMenu($tenant);
        $this->cart->tambah($menu->id_menu);

        $result = $this->cart->hapus($menu->id_menu);

        $this->assertTrue($result['ok']);
        $this->assertEmpty($this->cart->all());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function hapus_item_yang_tidak_ada_mengembalikan_error(): void
    {
        $result = $this->cart->hapus(999999);

        $this->assertFalse($result['ok']);
    }

    // =========================================================================
    // CartService — catatan
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function update_catatan_item(): void
    {
        $tenant = $this->buatTenantBuka('T12');
        $menu   = $this->buatMenu($tenant);
        $this->cart->tambah($menu->id_menu);

        $result = $this->cart->updateCatatan($menu->id_menu, 'extra pedas');

        $this->assertTrue($result['ok']);
        $this->assertSame('extra pedas', $this->cart->all()[$menu->id_menu]['catatan']);
    }

    // =========================================================================
    // CartService — perhitungan
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function grand_total_dihitung_dengan_benar(): void
    {
        $t1  = $this->buatTenantBuka('Hitung A');
        $t2  = $this->buatTenantBuka('Hitung B');
        $m1  = $this->buatMenu($t1, true, 20000);
        $m2  = $this->buatMenu($t2, true, 15000);

        $this->cart->tambah($m1->id_menu);
        $this->cart->tambah($m1->id_menu); // 2x = 40.000
        $this->cart->tambah($m2->id_menu); // 1x = 15.000

        $this->assertSame(55000.0, $this->cart->grandTotal());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function subtotal_per_tenant_dihitung_dengan_benar(): void
    {
        $t1  = $this->buatTenantBuka('Subtotal A');
        $t2  = $this->buatTenantBuka('Subtotal B');
        $m1  = $this->buatMenu($t1, true, 10000);
        $m2  = $this->buatMenu($t2, true, 30000);

        $this->cart->tambah($m1->id_menu);
        $this->cart->tambah($m1->id_menu); // subtotal A = 20.000
        $this->cart->tambah($m2->id_menu); // subtotal B = 30.000

        $groups = $this->cart->groupedByTenant();

        $subtotalA = $groups[$t1->id_tenant]['subtotal'];
        $subtotalB = $groups[$t2->id_tenant]['subtotal'];

        $this->assertSame(20000.0, $subtotalA);
        $this->assertSame(30000.0, $subtotalB);
    }

    // =========================================================================
    // CartService — kosongkan
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function kosongkan_menghapus_semua_item(): void
    {
        $tenant = $this->buatTenantBuka('T13');
        $m1     = $this->buatMenu($tenant);
        $m2     = $this->buatMenu($tenant);
        $this->cart->tambah($m1->id_menu);
        $this->cart->tambah($m2->id_menu);

        $this->cart->kosongkan();

        $this->assertTrue($this->cart->isEmpty());
        $this->assertSame(0.0, $this->cart->grandTotal());
    }
}
