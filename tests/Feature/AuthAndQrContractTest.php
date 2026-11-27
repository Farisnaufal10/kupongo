<?php

namespace Tests\Feature;

use App\Models\Meja;
use App\Models\Pengelola;
use App\Models\Tenant;
use App\Models\User;
use App\Services\MejaQrService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Pengujian fitur Tahap 1 Anggota 3 (Pengelola & Autentikasi):
 * 1. Login & Logout terpadu berbasis username/email untuk role pengelola & tenant.
 * 2. Proteksi middleware lintas-role dan isolasi data antar-tenant.
 * 3. Kontrak validasi token QR Meja (MejaQrService).
 */
class AuthAndQrContractTest extends TestCase
{
    use DatabaseTransactions;

    // -------------------------------------------------------------------------
    // Helper pembuat entitas uji
    // -------------------------------------------------------------------------

    private function buatPengelola(
        ?string $username = null,
        string $password = 'rahasia123',
        bool $isActive = true
    ): User {
        $user = User::create([
            'username' => $username ?? 'pengelola_' . uniqid(),
            'name' => 'Pengelola Sentra',
            'password' => Hash::make($password),
            'role' => 'pengelola',
            'is_active' => $isActive,
        ]);

        Pengelola::create([
            'id_user' => $user->id_user,
            'nama' => 'Admin D Kupon',
            'no_hp' => '081234567890',
        ]);

        return $user;
    }

    /**
     * @return array{user: User, tenant: Tenant}
     */
    private function buatTenant(
        ?string $username = null,
        string $password = 'rahasia123',
        bool $userActive = true,
        bool $tenantActive = true,
        string $namaTenant = 'Kedai Nusantara'
    ): array {
        $user = User::create([
            'username' => $username ?? 'tenant_' . uniqid(),
            'name' => $namaTenant,
            'password' => Hash::make($password),
            'role' => 'tenant',
            'is_active' => $userActive,
        ]);

        $tenant = Tenant::create([
            'id_user' => $user->id_user,
            'nama_tenant' => $namaTenant,
            'status_operasional' => Tenant::STATUS_OPERASIONAL_BUKA,
            'is_active' => $tenantActive,
        ]);

        return ['user' => $user, 'tenant' => $tenant];
    }

    private function buatMeja(string $status = Meja::STATUS_TERSEDIA, ?string $nomor = null): Meja
    {
        return Meja::create([
            'nomor_meja' => $nomor ?? 'M-' . strtoupper(substr(uniqid(), -6)),
            'kapasitas' => 4,
            'status' => $status,
        ]);
    }

    // =========================================================================
    // 1. Pengujian Autentikasi (Login & Logout)
    // =========================================================================

    #[Test]
    public function halaman_login_dapat_diakses_oleh_tamu_dan_pembeli_tetap_tanpa_login(): void
    {
        $this->withoutVite();

        $this->get(route('login'))
            ->assertStatus(200)
            ->assertSee('Masuk ke KuponGo')
            ->assertSee('Username atau Email');

        // Pastikan modul Pembeli tetap dapat diakses tanpa login
        $this->get(route('pembeli.index'))
            ->assertStatus(200);
    }

    #[Test]
    public function login_pengelola_valid_mengarahkan_ke_dashboard_pengelola(): void
    {
        $pengelola = $this->buatPengelola('admin_dkp_' . uniqid(), 'secret123');

        $response = $this->post(route('login.submit'), [
            'username' => $pengelola->username,
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('pengelola.dashboard'));
        $this->assertAuthenticatedAs($pengelola);

        $this->get(route('pengelola.dashboard'))
            ->assertStatus(200)
            ->assertSee('Dashboard Analitik Pengelola');
    }

    #[Test]
    public function login_tenant_valid_dengan_username_berformat_email_mengarahkan_ke_dashboard_tenant(): void
    {
        $emailStyleUsername = 'kedai_' . substr(uniqid(), -6) . '@kupongo.id';
        ['user' => $tenantUser, 'tenant' => $tenant] = $this->buatTenant(
            username: $emailStyleUsername,
            password: 'tenantPassword123',
            namaTenant: 'Soto Betawi D Kupon'
        );

        $response = $this->post(route('login.submit'), [
            'username' => $emailStyleUsername,
            'password' => 'tenantPassword123',
        ]);

        $response->assertRedirect(route('tenant.dashboard'));
        $this->assertAuthenticatedAs($tenantUser);

        $this->get(route('tenant.dashboard'))
            ->assertStatus(200)
            ->assertSee('Dashboard Tenant')
            ->assertSee($tenant->nama_tenant);
    }

    #[Test]
    public function login_gagal_jika_password_salah_atau_username_tidak_ditemukan(): void
    {
        $pengelola = $this->buatPengelola('admin_err_' . uniqid(), 'benar123');

        // Password salah
        $this->from(route('login'))
            ->post(route('login.submit'), [
                'username' => $pengelola->username,
                'password' => 'salah999',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('username');

        $this->assertGuest();

        // Username tidak ditemukan
        $this->from(route('login'))
            ->post(route('login.submit'), [
                'username' => 'tidak_terdaftar_' . uniqid(),
                'password' => 'apapun123',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('username');

        $this->assertGuest();

        // Field kosong
        $this->from(route('login'))
            ->post(route('login.submit'), [
                'username' => '',
                'password' => '',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['username', 'password']);
    }

    #[Test]
    public function login_ditolak_jika_akun_user_atau_profil_tenant_tidak_aktif(): void
    {
        $inactivePengelola = $this->buatPengelola(
            username: 'nonaktif_pengelola_' . uniqid(),
            password: 'password123',
            isActive: false
        );

        $this->from(route('login'))
            ->post(route('login.submit'), [
                'username' => $inactivePengelola->username,
                'password' => 'password123',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('username');

        $this->assertGuest();

        ['user' => $inactiveTenantUser] = $this->buatTenant(
            username: 'nonaktif_tenant_' . uniqid(),
            password: 'password123',
            userActive: true,
            tenantActive: false
        );

        $this->from(route('login'))
            ->post(route('login.submit'), [
                'username' => $inactiveTenantUser->username,
                'password' => 'password123',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    #[Test]
    public function logout_menghapus_sesi_dan_mengarahkan_ke_halaman_login(): void
    {
        $pengelola = $this->buatPengelola();

        $this->actingAs($pengelola)
            ->post(route('logout'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $this->assertGuest();
    }

    #[Test]
    public function pengguna_yang_sudah_login_diarahkan_otomatis_saat_membuka_halaman_login(): void
    {
        $pengelola = $this->buatPengelola();
        $this->actingAs($pengelola)
            ->get(route('login'))
            ->assertRedirect(route('pengelola.dashboard'));

        ['user' => $tenantUser] = $this->buatTenant();
        $this->actingAs($tenantUser)
            ->get(route('login'))
            ->assertRedirect(route('tenant.dashboard'));
    }

    // =========================================================================
    // 2. Pengujian Middleware Otorisasi Lintas-Role & Isolasi Tenant
    // =========================================================================

    #[Test]
    public function tamu_tidak_dapat_mengakses_route_pengelola_maupun_tenant(): void
    {
        $this->get(route('pengelola.dashboard'))
            ->assertRedirect(route('login'));

        $this->get(route('tenant.dashboard'))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function tenant_tidak_dapat_mengakses_route_pengelola(): void
    {
        ['user' => $tenantUser] = $this->buatTenant();

        $this->actingAs($tenantUser)
            ->get(route('pengelola.dashboard'))
            ->assertStatus(403);
    }

    #[Test]
    public function pengelola_tidak_dapat_mengakses_route_tenant(): void
    {
        $pengelola = $this->buatPengelola();

        $this->actingAs($pengelola)
            ->get(route('tenant.dashboard'))
            ->assertStatus(403);
    }

    #[Test]
    public function tenant_tidak_dapat_mengakses_data_tenant_lain_namun_bisa_mengakses_miliknya(): void
    {
        ['user' => $userA, 'tenant' => $tenantA] = $this->buatTenant(namaTenant: 'Kedai A');
        ['tenant' => $tenantB] = $this->buatTenant(namaTenant: 'Kedai B');

        // Akses profil milik sendiri diizinkan
        $this->actingAs($userA)
            ->get(route('tenant.profil', ['tenant' => $tenantA->id_tenant]))
            ->assertStatus(200)
            ->assertSee('Kedai A');

        // Akses profil milik tenant lain melalui route parameter ditolak (403)
        $this->actingAs($userA)
            ->get(route('tenant.profil', ['tenant' => $tenantB->id_tenant]))
            ->assertStatus(403);

        // Manipulasi query/input id_tenant milik tenant lain ditolak (403)
        $this->actingAs($userA)
            ->get(route('tenant.dashboard', ['id_tenant' => $tenantB->id_tenant]))
            ->assertStatus(403);
    }

    #[Test]
    public function akun_yang_dinonaktifkan_saat_sesi_berjalan_dipaksa_logout_oleh_middleware(): void
    {
        $pengelola = $this->buatPengelola();

        $this->actingAs($pengelola);

        $pengelola->update(['is_active' => false]);

        $this->get(route('pengelola.dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    // =========================================================================
    // 3. Pengujian Kontrak Validasi Token QR Meja (MejaQrService)
    // =========================================================================

    #[Test]
    public function qr_token_valid_untuk_meja_tersedia_dan_terisi(): void
    {
        $service = app(MejaQrService::class);

        $mejaTersedia = $this->buatMeja(Meja::STATUS_TERSEDIA, 'M-T01');
        $tokenTersedia = $service->generateToken($mejaTersedia);

        $this->assertMatchesRegularExpression('/^M\d+\.[a-f0-9]{16}$/', $tokenTersedia);
        $this->assertStringEndsWith('/meja/' . $tokenTersedia, $service->generateScanUrl($mejaTersedia));

        $resultTersedia = $service->validateToken($tokenTersedia);
        $this->assertTrue($resultTersedia['valid']);
        $this->assertNull($resultTersedia['reason']);
        $this->assertSame((int) $mejaTersedia->id_meja, $resultTersedia['meja']['id_meja']);
        $this->assertSame('M-T01', $resultTersedia['meja']['nomor_meja']);
        $this->assertSame(4, $resultTersedia['meja']['kapasitas']);
        $this->assertSame(Meja::STATUS_TERSEDIA, $resultTersedia['meja']['status']);
        $this->assertInstanceOf(Meja::class, $resultTersedia['model']);

        // Meja berstatus terisi tetap valid agar pembeli di meja bisa memindai ulang
        $mejaTerisi = $this->buatMeja(Meja::STATUS_TERISI, 'M-T02');
        $resultTerisi = $service->validateToken($service->generateToken($mejaTerisi));
        $this->assertTrue($resultTerisi['valid']);
        $this->assertSame(Meja::STATUS_TERISI, $resultTerisi['meja']['status']);
    }

    #[Test]
    public function qr_token_ditolak_jika_kosong_format_salah_signature_palsu_atau_meja_tidak_ada(): void
    {
        $service = app(MejaQrService::class);
        $meja = $this->buatMeja(Meja::STATUS_TERSEDIA, 'M-T03');

        // Token kosong
        $emptyResult = $service->validateToken('   ');
        $this->assertFalse($emptyResult['valid']);
        $this->assertSame(MejaQrService::REASON_TOKEN_KOSONG, $emptyResult['reason']);
        $this->assertNull($emptyResult['meja']);

        // Format salah
        $badFormat = $service->validateToken('INVALID-QR-123');
        $this->assertFalse($badFormat['valid']);
        $this->assertSame(MejaQrService::REASON_FORMAT_TIDAK_VALID, $badFormat['reason']);

        // Signature dimodifikasi / palsu
        $forgedToken = "M{$meja->id_meja}.0000000000000000";
        $forgedResult = $service->validateToken($forgedToken);
        $this->assertFalse($forgedResult['valid']);
        $this->assertSame(MejaQrService::REASON_SIGNATURE_TIDAK_VALID, $forgedResult['reason']);

        // ID meja tidak ditemukan
        $missingResult = $service->validateToken('M999999999.abcdef0123456789');
        $this->assertFalse($missingResult['valid']);
        $this->assertSame(MejaQrService::REASON_MEJA_TIDAK_DITEMUKAN, $missingResult['reason']);
    }

    #[Test]
    public function qr_token_ditolak_jika_meja_tidak_aktif_atau_nomor_meja_berubah(): void
    {
        $service = app(MejaQrService::class);

        // Meja tidak aktif
        $mejaNonaktif = $this->buatMeja(Meja::STATUS_TIDAK_AKTIF, 'M-T04');
        $tokenNonaktif = $service->generateToken($mejaNonaktif);

        $inactiveResult = $service->validateToken($tokenNonaktif);
        $this->assertFalse($inactiveResult['valid']);
        $this->assertSame(MejaQrService::REASON_MEJA_TIDAK_AKTIF, $inactiveResult['reason']);

        // Nomor meja diubah setelah QR dibuat -> token lama tidak lagi valid
        $mejaAktif = $this->buatMeja(Meja::STATUS_TERSEDIA, 'M-T05');
        $oldToken = $service->generateToken($mejaAktif);
        $mejaAktif->update(['nomor_meja' => 'M-T05-NEW']);

        $staleResult = $service->validateToken($oldToken);
        $this->assertFalse($staleResult['valid']);
        $this->assertSame(MejaQrService::REASON_SIGNATURE_TIDAK_VALID, $staleResult['reason']);
    }

    #[Test]
    public function session_context_meja_dapat_disimpan_divalidasi_ulang_dan_dibersihkan_otomatis(): void
    {
        $service = app(MejaQrService::class);
        $meja = $this->buatMeja(Meja::STATUS_TERSEDIA, 'M-S01');
        $token = $service->generateToken($meja);
        $session = $this->app['session']->driver();

        $stored = $service->storeSessionContext($session, $meja, $token);
        $this->assertSame((int) $meja->id_meja, $stored['id_meja']);
        $this->assertSame('M-S01', $service->getSessionContext($session)['nomor_meja']);

        // Resolusi sesi saat meja aktif mengembalikan model Meja
        $resolved = $service->resolveActiveMejaFromSession($session);
        $this->assertNotNull($resolved);
        $this->assertSame((int) $meja->id_meja, (int) $resolved->id_meja);

        // Jika meja dinonaktifkan, resolveActiveMejaFromSession mengembalikan null & membersihkan sesi
        $meja->update(['status' => Meja::STATUS_TIDAK_AKTIF]);
        $this->assertNull($service->resolveActiveMejaFromSession($session));
        $this->assertNull($service->getSessionContext($session));
    }
}
