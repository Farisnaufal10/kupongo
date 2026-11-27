<?php

namespace App\Services;

use App\Models\Meja;
use App\Support\QrCodeSvgGenerator;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Service pembuat dan validator token QR Meja KuponGo.
 *
 * Dirancang menggunakan kolom aktual tabel `meja` tanpa mengubah skema migrasi inti:
 * - id_meja (unsignedBigInteger, PK)
 * - nomor_meja (varchar(20), UNIQUE)
 * - qr_code_path (varchar(255), nullable)
 * - kapasitas (unsignedInteger, nullable)
 * - status (enum: 'tersedia', 'terisi', 'tidak_aktif')
 *
 * Format Token QR:
 *   M{id_meja}.{hmac_sha256_16_hex}
 *   Contoh: M12.a1b2c3d4e5f60718
 *
 * Signature dihitung dari `kupongo:meja:{id_meja}:{nomor_meja_normalized}` menggunakan `config('app.key')`,
 * sehingga token tidak dapat dipalsukan dan otomatis kedaluwarsa jika nomor_meja diubah.
 *
 * Referensi kontrak integrasi: docs/KONTRAK-AUTH-DAN-QR-MEJA.md
 */
class MejaQrService
{
    public const SESSION_KEY = 'kupongo.meja_context';

    public const REASON_TOKEN_KOSONG = 'TOKEN_KOSONG';
    public const REASON_FORMAT_TIDAK_VALID = 'FORMAT_TIDAK_VALID';
    public const REASON_MEJA_TIDAK_DITEMUKAN = 'MEJA_TIDAK_DITEMUKAN';
    public const REASON_SIGNATURE_TIDAK_VALID = 'SIGNATURE_TIDAK_VALID';
    public const REASON_MEJA_TIDAK_AKTIF = 'MEJA_TIDAK_AKTIF';

    private const TOKEN_PATTERN = '/^M(\d+)\.([a-f0-9]{16})$/';

    /**
     * Menghasilkan token QR bertanda tangan (signed stateless token) untuk sebuah Meja.
     */
    public function generateToken(Meja $meja): string
    {
        $signature = $this->computeSignature((int) $meja->id_meja, (string) $meja->nomor_meja);

        return "M{$meja->id_meja}.{$signature}";
    }

    /**
     * Menghasilkan URL lengkap yang ditanamkan ke dalam gambar QR Code meja.
     *
     * Jika Anggota 1 (Pembeli) sudah mendaftarkan route bernama `pembeli.meja`
     * dengan parameter `token`, URL akan menggunakan route tersebut; jika belum,
     * fallback ke `/meja/{token}`.
     */
    public function generateScanUrl(Meja $meja): string
    {
        $token = $this->generateToken($meja);

        if (Route::has('pembeli.meja')) {
            return route('pembeli.meja', ['token' => $token]);
        }

        return url('/meja/'.$token);
    }

    /**
     * Menghasilkan path standar penyimpanan berkas gambar QR pada kolom `meja.qr_code_path`.
     */
    public function buildQrCodePath(Meja $meja, string $extension = 'svg'): string
    {
        $slugNomor = Str::slug((string) $meja->nomor_meja, '-');
        $ext = ltrim(strtolower($extension), '.');

        return "qr/meja/meja-{$meja->id_meja}-{$slugNomor}.{$ext}";
    }

    /**
     * Merender gambar QR Code SVG bersih untuk meja tertentu.
     */
    public function renderQrSvg(Meja $meja, int $pixelSize = 240): string
    {
        $generator = new QrCodeSvgGenerator();

        return $generator->renderSvg($this->generateScanUrl($meja), $pixelSize);
    }

    /**
     * Menghasilkan file SVG QR Code, menyimpannya ke storage `public`,
     * serta memperbarui kolom `meja.qr_code_path`.
     */
    public function generateAndStoreQrSvg(Meja $meja): string
    {
        $oldPath = $meja->qr_code_path;
        $newPath = $this->buildQrCodePath($meja, 'svg');
        $svg = $this->renderQrSvg($meja, 260);

        Storage::disk('public')->put($newPath, $svg);

        if ($oldPath && $oldPath !== $newPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        if ($meja->qr_code_path !== $newPath) {
            $meja->qr_code_path = $newPath;
            $meja->save();
        }

        return $newPath;
    }

    /**
     * Merender Stiker QR Meja siap cetak dalam format SVG bergaya Modern Culinary KuponGo.
     */
    public function renderStickerSvg(Meja $meja): string
    {
        $generator = new QrCodeSvgGenerator();
        $scanUrl = $this->generateScanUrl($meja);
        $token = $this->generateToken($meja);
        $qrGroup = $generator->renderEmbeddedGroup($scanUrl, 75, 148, 270, '#103B2C');

        $nomorMeja = htmlspecialchars((string) $meja->nomor_meja, ENT_QUOTES, 'UTF-8');
        $kapasitasText = $meja->kapasitas
            ? 'Kapasitas ' . (int) $meja->kapasitas . ' kursi'
            : 'Sentra Kuliner D&#39;Kupon';
        $safeToken = htmlspecialchars($token, ENT_QUOTES, 'UTF-8');

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="420" height="580" viewBox="0 0 420 580" role="img" aria-label="Stiker QR Meja {$nomorMeja} KuponGo">
  <defs>
    <clipPath id="card-clip">
      <rect x="8" y="8" width="404" height="564" rx="20" ry="20"/>
    </clipPath>
  </defs>

  <!-- Latar kartu Warm Ivory -->
  <rect x="8" y="8" width="404" height="564" rx="20" ry="20" fill="#F8F7F2" stroke="#E2E5DE" stroke-width="2"/>

  <g clip-path="url(#card-clip)">
    <!-- Header Forest Green -->
    <rect x="8" y="8" width="404" height="104" fill="#103B2C"/>
    <!-- Garis aksen Champagne Gold -->
    <rect x="8" y="112" width="404" height="6" fill="#C5A46D"/>

    <text x="210" y="54" text-anchor="middle" font-family="Plus Jakarta Sans, Inter, Arial, sans-serif" font-size="28" font-weight="700" fill="#FFFFFF" letter-spacing="0.5">KuponGo</text>
    <text x="210" y="84" text-anchor="middle" font-family="Plus Jakarta Sans, Inter, Arial, sans-serif" font-size="13" font-weight="500" fill="#C5A46D">SENTRA KULINER D&#39;KUPON</text>
  </g>

  <!-- Bingkai putih untuk QR Code -->
  <rect x="55" y="132" width="310" height="302" rx="16" ry="16" fill="#FFFFFF" stroke="#E2E5DE" stroke-width="1.5"/>
  {$qrGroup}

  <!-- Label Nomor Meja -->
  <rect x="85" y="452" width="250" height="46" rx="12" ry="12" fill="#103B2C"/>
  <text x="210" y="482" text-anchor="middle" font-family="Plus Jakarta Sans, Inter, Arial, sans-serif" font-size="22" font-weight="700" fill="#FFFFFF">MEJA {$nomorMeja}</text>

  <!-- Instruksi Pembeli -->
  <text x="210" y="522" text-anchor="middle" font-family="Plus Jakarta Sans, Inter, Arial, sans-serif" font-size="13" font-weight="600" fill="#202923">Pindai QR untuk Pesan Makanan &amp; Minuman</text>
  <text x="210" y="543" text-anchor="middle" font-family="Plus Jakarta Sans, Inter, Arial, sans-serif" font-size="11" font-weight="500" fill="#67736B">{$kapasitasText} &#8226; Tanpa Login &#8226; {$safeToken}</text>
</svg>
SVG;
    }

    /**
     * Memvalidasi token QR meja yang dipindai Pembeli.
     *
     * Status meja yang diperbolehkan untuk pemesanan:
     * - `tersedia` : valid (meja siap ditempati)
     * - `terisi`   : valid (pembeli di meja tersebut dapat memindai ulang untuk menambah pesanan)
     * - `tidak_aktif` : DITOLAK (`MEJA_TIDAK_AKTIF`)
     *
     * @return array{
     *     valid: bool,
     *     reason: string|null,
     *     message: string,
     *     meja: array{
     *         id_meja: int,
     *         nomor_meja: string,
     *         kapasitas: int|null,
     *         status: string,
     *         qr_code_path: string|null
     *     }|null,
     *     model: Meja|null
     * }
     */
    public function validateToken(?string $token): array
    {
        $normalizedToken = trim((string) $token);

        if ($normalizedToken === '') {
            return $this->invalidResult(
                self::REASON_TOKEN_KOSONG,
                'Token QR meja tidak boleh kosong.'
            );
        }

        if (! preg_match(self::TOKEN_PATTERN, $normalizedToken, $matches)) {
            return $this->invalidResult(
                self::REASON_FORMAT_TIDAK_VALID,
                'Format QR meja tidak valid. Silakan pindai ulang QR Code resmi pada meja.'
            );
        }

        $idMeja = (int) $matches[1];
        $providedSignature = $matches[2];

        $meja = Meja::find($idMeja);

        if (! $meja) {
            return $this->invalidResult(
                self::REASON_MEJA_TIDAK_DITEMUKAN,
                'Data meja tidak ditemukan. Silakan pindai ulang QR Code pada meja lain atau hubungi petugas.'
            );
        }

        $expectedSignature = $this->computeSignature((int) $meja->id_meja, (string) $meja->nomor_meja);

        if (! hash_equals($expectedSignature, $providedSignature)) {
            return $this->invalidResult(
                self::REASON_SIGNATURE_TIDAK_VALID,
                'QR meja tidak valid atau sudah diperbarui. Silakan pindai QR Code terbaru pada meja.'
            );
        }

        if ($meja->status === Meja::STATUS_TIDAK_AKTIF) {
            return $this->invalidResult(
                self::REASON_MEJA_TIDAK_AKTIF,
                "Meja {$meja->nomor_meja} sedang tidak aktif dan tidak dapat digunakan untuk pemesanan."
            );
        }

        return [
            'valid' => true,
            'reason' => null,
            'message' => "Meja {$meja->nomor_meja} berhasil diverifikasi.",
            'meja' => $this->formatMejaPayload($meja),
            'model' => $meja,
        ];
    }

    /**
     * Menyimpan konteks meja tervalidasi ke sesi Pembeli (untuk digunakan Anggota 1).
     *
     * @return array{
     *     id_meja: int,
     *     nomor_meja: string,
     *     kapasitas: int|null,
     *     status: string,
     *     token: string,
     *     verified_at: string
     * }
     */
    public function storeSessionContext(Session $session, Meja $meja, string $token): array
    {
        $context = [
            'id_meja' => (int) $meja->id_meja,
            'nomor_meja' => (string) $meja->nomor_meja,
            'kapasitas' => $meja->kapasitas !== null ? (int) $meja->kapasitas : null,
            'status' => (string) $meja->status,
            'token' => $token,
            'verified_at' => now()->toIso8601String(),
        ];

        $session->put(self::SESSION_KEY, $context);

        return $context;
    }

    /**
     * Membaca konteks meja tersimpan dari sesi Pembeli (null jika belum memindai QR).
     *
     * @return array<string, mixed>|null
     */
    public function getSessionContext(Session $session): ?array
    {
        $context = $session->get(self::SESSION_KEY);

        return is_array($context) ? $context : null;
    }

    /**
     * Memvalidasi ulang konteks meja dari sesi Pembeli terhadap database aktual.
     * Jika meja sudah tidak ada atau dinonaktifkan, sesi otomatis dibersihkan dan mengembalikan null.
     */
    public function resolveActiveMejaFromSession(Session $session): ?Meja
    {
        $context = $this->getSessionContext($session);

        if ($context === null || ! isset($context['token']) || ! is_string($context['token'])) {
            return null;
        }

        $result = $this->validateToken($context['token']);

        if (! $result['valid'] || ! $result['model'] instanceof Meja) {
            $this->clearSessionContext($session);

            return null;
        }

        return $result['model'];
    }

    /**
     * Menghapus konteks meja dari sesi Pembeli.
     */
    public function clearSessionContext(Session $session): void
    {
        $session->forget(self::SESSION_KEY);
    }

    /**
     * Menghitung HMAC-SHA256 ringkas (16 karakter hex) berbasis APP_KEY.
     */
    private function computeSignature(int $idMeja, string $nomorMeja): string
    {
        $normalizedNomor = strtoupper(trim($nomorMeja));
        $payload = "kupongo:meja:{$idMeja}:{$normalizedNomor}";
        $secret = (string) config('app.key', 'kupongo-fallback-key');

        return substr(hash_hmac('sha256', $payload, $secret), 0, 16);
    }

    /**
     * @return array{
     *     id_meja: int,
     *     nomor_meja: string,
     *     kapasitas: int|null,
     *     status: string,
     *     qr_code_path: string|null
     * }
     */
    private function formatMejaPayload(Meja $meja): array
    {
        return [
            'id_meja' => (int) $meja->id_meja,
            'nomor_meja' => (string) $meja->nomor_meja,
            'kapasitas' => $meja->kapasitas !== null ? (int) $meja->kapasitas : null,
            'status' => (string) $meja->status,
            'qr_code_path' => $meja->qr_code_path,
        ];
    }

    /**
     * @return array{
     *     valid: false,
     *     reason: string,
     *     message: string,
     *     meja: null,
     *     model: null
     * }
     */
    private function invalidResult(string $reason, string $message): array
    {
        return [
            'valid' => false,
            'reason' => $reason,
            'message' => $message,
            'meja' => null,
            'model' => null,
        ];
    }
}
