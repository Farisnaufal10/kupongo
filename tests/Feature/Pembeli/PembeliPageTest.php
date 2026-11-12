<?php

namespace Tests\Feature\Pembeli;

use App\Models\Menu;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Pengujian fitur halaman Modul Pembeli — Sprint 1.
 *
 * Menggunakan DatabaseTransactions agar setiap test rollback otomatis
 * dan tidak meninggalkan data di kupongo_test.
 */
class PembeliPageTest extends TestCase
{
    use DatabaseTransactions;

    // -------------------------------------------------------------------------
    // Helper: buat User + Tenant
    // -------------------------------------------------------------------------

    private function buatTenantBuka(string $nama = 'Tenant Buka'): Tenant
    {
        $user = User::create([
            'username'  => 'tenant_' . uniqid(),
            'name'      => $nama,
            'password'  => bcrypt('password'),
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
            'username'  => 'tenant_' . uniqid(),
            'name'      => $nama,
            'password'  => bcrypt('password'),
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

    private function buatMenu(Tenant $tenant, bool $tersedia = true, string $nama = 'Nasi Goreng'): Menu
    {
        return Menu::create([
            'id_tenant'  => $tenant->id_tenant,
            'nama_menu'  => $nama,
            'harga'      => 25000,
            'is_tersedia' => $tersedia,
            'is_active'  => true,
        ]);
    }

    // =========================================================================
    // Halaman Daftar Tenant (/menu)
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function halaman_daftar_tenant_mengembalikan_200(): void
    {
        $response = $this->get(route('pembeli.index'));

        $response->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function halaman_daftar_tenant_menampilkan_nama_tenant_aktif(): void
    {
        $tenant = $this->buatTenantBuka('Warung Baru Test');

        $response = $this->get(route('pembeli.index'));

        $response->assertStatus(200);
        $response->assertSee('Warung Baru Test');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function halaman_daftar_tenant_menampilkan_badge_buka_untuk_tenant_buka(): void
    {
        $this->buatTenantBuka('Tenant Buka Test');

        $response = $this->get(route('pembeli.index'));

        $response->assertStatus(200);
        $response->assertSee('Buka');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function halaman_daftar_tenant_menampilkan_badge_tutup_untuk_tenant_tutup(): void
    {
        $this->buatTenantTutup('Tenant Tutup Test');

        $response = $this->get(route('pembeli.index'));

        $response->assertStatus(200);
        $response->assertSee('Tutup');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function tombol_lihat_menu_dinonaktifkan_untuk_tenant_tutup(): void
    {
        $this->buatTenantTutup('Tenant Tutup Btn');

        $response = $this->get(route('pembeli.index'));

        $response->assertStatus(200);
        $response->assertSee('Sedang Tutup');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function halaman_daftar_tenant_menampilkan_empty_state_jika_tidak_ada_tenant(): void
    {
        // Nonaktifkan semua tenant yang sudah ada agar query kosong
        Tenant::where('is_active', true)->update(['is_active' => false]);

        $response = $this->get(route('pembeli.index'));

        $response->assertStatus(200);
        $response->assertSee('Belum ada tenant tersedia');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function tenant_tidak_aktif_tidak_tampil_di_halaman_daftar(): void
    {
        $user = User::create([
            'username'  => 'tenant_nonaktif_' . uniqid(),
            'name'      => 'Tenant Tidak Aktif',
            'password'  => bcrypt('password'),
            'role'      => 'tenant',
            'is_active' => true,
        ]);

        Tenant::create([
            'id_user'            => $user->id_user,
            'nama_tenant'        => 'Tenant Tidak Aktif Tersembunyi',
            'status_operasional' => Tenant::STATUS_OPERASIONAL_BUKA,
            'is_active'          => false, // sengaja tidak aktif
        ]);

        $response = $this->get(route('pembeli.index'));

        $response->assertStatus(200);
        $response->assertDontSee('Tenant Tidak Aktif Tersembunyi');
    }

    // =========================================================================
    // Halaman Menu Tenant (/menu/tenant/{id})
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function halaman_menu_tenant_mengembalikan_200_untuk_tenant_aktif(): void
    {
        $tenant = $this->buatTenantBuka('Tenant Detail Test');

        $response = $this->get(route('pembeli.tenant', $tenant->id_tenant));

        $response->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function halaman_menu_tenant_menampilkan_nama_menu_dan_harga(): void
    {
        $tenant = $this->buatTenantBuka('Tenant Menu Test');
        $this->buatMenu($tenant, true, 'Ayam Bakar Spesial');

        $response = $this->get(route('pembeli.tenant', $tenant->id_tenant));

        $response->assertStatus(200);
        $response->assertSee('Ayam Bakar Spesial');
        $response->assertSee('25.000'); // bagian dari Rp 25.000
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function halaman_menu_tenant_menampilkan_empty_state_jika_tidak_ada_menu(): void
    {
        $tenant = $this->buatTenantBuka('Tenant Tanpa Menu');

        $response = $this->get(route('pembeli.tenant', $tenant->id_tenant));

        $response->assertStatus(200);
        $response->assertSee('Belum ada menu tersedia');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function tombol_pesan_dinonaktifkan_untuk_menu_habis(): void
    {
        $tenant = $this->buatTenantBuka('Tenant Ada Habis');
        $this->buatMenu($tenant, false, 'Menu Habis Test');

        $response = $this->get(route('pembeli.tenant', $tenant->id_tenant));

        $response->assertStatus(200);
        $response->assertSee('Habis');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function tombol_pesan_dinonaktifkan_saat_tenant_tutup(): void
    {
        $tenant = $this->buatTenantTutup('Tenant Tutup Menu');
        $this->buatMenu($tenant, true, 'Menu Tersedia Tapi Tutup');

        $response = $this->get(route('pembeli.tenant', $tenant->id_tenant));

        $response->assertStatus(200);
        // Semua tombol harus menampilkan 'Tutup' bukan '+ Pesan'
        $response->assertSee('Tutup');
        $response->assertDontSee('+ Pesan');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function halaman_menu_tenant_mengembalikan_404_untuk_tenant_tidak_aktif(): void
    {
        $user = User::create([
            'username'  => 'tenant_404_' . uniqid(),
            'name'      => 'Tenant 404',
            'password'  => bcrypt('password'),
            'role'      => 'tenant',
            'is_active' => true,
        ]);

        $tenant = Tenant::create([
            'id_user'            => $user->id_user,
            'nama_tenant'        => 'Tenant Tidak Aktif 404',
            'status_operasional' => Tenant::STATUS_OPERASIONAL_BUKA,
            'is_active'          => false,
        ]);

        $response = $this->get(route('pembeli.tenant', $tenant->id_tenant));

        $response->assertStatus(404);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function halaman_menu_tenant_menampilkan_peringatan_saat_tenant_tutup(): void
    {
        $tenant = $this->buatTenantTutup('Tenant Pesan Tutup');
        $this->buatMenu($tenant, true, 'Menu Ada');

        $response = $this->get(route('pembeli.tenant', $tenant->id_tenant));

        $response->assertStatus(200);
        $response->assertSee('Tenant ini sedang tutup');
    }
}
