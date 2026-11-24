<?php

namespace Tests\Feature\Pembeli;

use App\Models\Menu;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * CartHttpTest — pengujian HTTP endpoint CartController.
 *
 * Semua request JSON, menggunakan DatabaseTransactions + array session.
 */
class CartHttpTest extends TestCase
{
    use DatabaseTransactions;

    private function buatTenantBuka(): Tenant
    {
        $user = User::create([
            'username'  => 'u_' . uniqid(),
            'name'      => 'T',
            'password'  => bcrypt('pw'),
            'role'      => 'tenant',
            'is_active' => true,
        ]);
        return Tenant::create([
            'id_user'            => $user->id_user,
            'nama_tenant'        => 'Tenant ' . uniqid(),
            'status_operasional' => Tenant::STATUS_OPERASIONAL_BUKA,
            'is_active'          => true,
        ]);
    }

    private function buatTenantTutup(): Tenant
    {
        $user = User::create([
            'username'  => 'u_' . uniqid(),
            'name'      => 'T',
            'password'  => bcrypt('pw'),
            'role'      => 'tenant',
            'is_active' => true,
        ]);
        return Tenant::create([
            'id_user'            => $user->id_user,
            'nama_tenant'        => 'Tenant Tutup ' . uniqid(),
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

    // =========================================================================
    // GET /keranjang
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function halaman_keranjang_mengembalikan_200(): void
    {
        $response = $this->get(route('pembeli.keranjang'));
        $response->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function halaman_keranjang_kosong_menampilkan_empty_state(): void
    {
        $response = $this->get(route('pembeli.keranjang'));
        $response->assertStatus(200);
        $response->assertSee('Keranjang kosong');
    }

    // =========================================================================
    // POST /keranjang/tambah
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function tambah_menu_valid_mengembalikan_ok(): void
    {
        $tenant = $this->buatTenantBuka();
        $menu   = $this->buatMenu($tenant);

        $response = $this->postJson(route('pembeli.keranjang.tambah'), [
            'id_menu' => $menu->id_menu,
        ]);

        $response->assertStatus(200)
                 ->assertJson(['ok' => true])
                 ->assertJsonStructure(['ok', 'message', 'cart_count']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function tambah_menu_habis_mengembalikan_422(): void
    {
        $tenant = $this->buatTenantBuka();
        $menu   = $this->buatMenu($tenant, false);

        $response = $this->postJson(route('pembeli.keranjang.tambah'), [
            'id_menu' => $menu->id_menu,
        ]);

        $response->assertStatus(422)->assertJson(['ok' => false]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function tambah_menu_tenant_tutup_mengembalikan_422(): void
    {
        $tenant = $this->buatTenantTutup();
        $menu   = $this->buatMenu($tenant, true);

        $response = $this->postJson(route('pembeli.keranjang.tambah'), [
            'id_menu' => $menu->id_menu,
        ]);

        $response->assertStatus(422)->assertJson(['ok' => false]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function tambah_tanpa_id_menu_mengembalikan_validation_error(): void
    {
        $response = $this->postJson(route('pembeli.keranjang.tambah'), []);
        $response->assertStatus(422)->assertJsonValidationErrors(['id_menu']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function tambah_menu_dengan_catatan(): void
    {
        $tenant = $this->buatTenantBuka();
        $menu   = $this->buatMenu($tenant);

        $response = $this->postJson(route('pembeli.keranjang.tambah'), [
            'id_menu' => $menu->id_menu,
            'catatan' => 'tidak pedas',
        ]);

        $response->assertStatus(200)->assertJson(['ok' => true]);
    }

    // =========================================================================
    // PATCH /keranjang/jumlah
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function set_jumlah_item_berhasil(): void
    {
        $tenant = $this->buatTenantBuka();
        $menu   = $this->buatMenu($tenant);

        // Tambah dulu
        $this->postJson(route('pembeli.keranjang.tambah'), ['id_menu' => $menu->id_menu]);

        $response = $this->patchJson(route('pembeli.keranjang.jumlah'), [
            'id_menu' => $menu->id_menu,
            'jumlah'  => 3,
        ]);

        $response->assertStatus(200)
                 ->assertJson(['ok' => true])
                 ->assertJsonStructure(['cart_count', 'grand_total']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function set_jumlah_melebihi_99_ditolak(): void
    {
        $tenant = $this->buatTenantBuka();
        $menu   = $this->buatMenu($tenant);
        $this->postJson(route('pembeli.keranjang.tambah'), ['id_menu' => $menu->id_menu]);

        $response = $this->patchJson(route('pembeli.keranjang.jumlah'), [
            'id_menu' => $menu->id_menu,
            'jumlah'  => 100,
        ]);

        $response->assertStatus(422);
    }

    // =========================================================================
    // DELETE /keranjang/hapus
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function hapus_item_berhasil(): void
    {
        $tenant = $this->buatTenantBuka();
        $menu   = $this->buatMenu($tenant);

        $this->postJson(route('pembeli.keranjang.tambah'), ['id_menu' => $menu->id_menu]);

        $response = $this->deleteJson(route('pembeli.keranjang.hapus'), [
            'id_menu' => $menu->id_menu,
        ]);

        $response->assertStatus(200)
                 ->assertJson(['ok' => true])
                 ->assertJsonStructure(['cart_count', 'grand_total', 'is_empty']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function hapus_item_tidak_ada_mengembalikan_422(): void
    {
        $response = $this->deleteJson(route('pembeli.keranjang.hapus'), [
            'id_menu' => 999999,
        ]);
        $response->assertStatus(422)->assertJson(['ok' => false]);
    }

    // =========================================================================
    // PATCH /keranjang/catatan
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function update_catatan_item_berhasil(): void
    {
        $tenant = $this->buatTenantBuka();
        $menu   = $this->buatMenu($tenant);
        $this->postJson(route('pembeli.keranjang.tambah'), ['id_menu' => $menu->id_menu]);

        $response = $this->patchJson(route('pembeli.keranjang.catatan'), [
            'id_menu' => $menu->id_menu,
            'catatan' => 'extra keju',
        ]);

        $response->assertStatus(200)->assertJson(['ok' => true]);
    }

    // =========================================================================
    // DELETE /keranjang — Kosongkan
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function kosongkan_keranjang_redirect_dengan_session_success(): void
    {
        $tenant = $this->buatTenantBuka();
        $menu   = $this->buatMenu($tenant);
        $this->postJson(route('pembeli.keranjang.tambah'), ['id_menu' => $menu->id_menu]);

        $response = $this->delete(route('pembeli.keranjang.kosongkan'));

        $response->assertRedirect(route('pembeli.keranjang'))
                 ->assertSessionHas('success');
    }

    // =========================================================================
    // Persistensi session
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function keranjang_persisten_setelah_navigasi(): void
    {
        $tenant = $this->buatTenantBuka();
        $menu   = $this->buatMenu($tenant);

        // Tambah via POST
        $this->postJson(route('pembeli.keranjang.tambah'), ['id_menu' => $menu->id_menu]);

        // Akses halaman keranjang — session harus tetap ada
        $response = $this->get(route('pembeli.keranjang'));
        $response->assertStatus(200);
        $response->assertSee($menu->nama_menu);
    }

    // =========================================================================
    // Halaman Sprint 1 tetap berfungsi
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function halaman_daftar_tenant_masih_berfungsi(): void
    {
        $response = $this->get(route('pembeli.index'));
        $response->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function halaman_katalog_tenant_masih_berfungsi(): void
    {
        $tenant = $this->buatTenantBuka();
        $response = $this->get(route('pembeli.tenant', $tenant->id_tenant));
        $response->assertStatus(200);
    }
}
