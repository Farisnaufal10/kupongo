<?php

namespace App\Http\Controllers\Pengelola;

use App\Http\Controllers\Controller;
use App\Models\Meja;
use App\Services\MejaQrService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Manajemen Meja Fisik & Generator QR Code oleh Pengelola (FR-29).
 */
class MejaController extends Controller
{
    public function __construct(
        private readonly MejaQrService $qrService
    ) {}

    /**
     * Menampilkan daftar meja fisik beserta status & aksi QR Code.
     */
    public function index(Request $request): View
    {
        $statusFilter = (string) $request->query('status', 'semua');

        $query = Meja::query()->orderBy('nomor_meja');

        if (in_array($statusFilter, [Meja::STATUS_TERSEDIA, Meja::STATUS_TERISI, Meja::STATUS_TIDAK_AKTIF], true)) {
            $query->where('status', $statusFilter);
        }

        $mejas = $query->get();

        $ringkasan = [
            'total' => Meja::count(),
            'tersedia' => Meja::where('status', Meja::STATUS_TERSEDIA)->count(),
            'terisi' => Meja::where('status', Meja::STATUS_TERISI)->count(),
            'tidak_aktif' => Meja::where('status', Meja::STATUS_TIDAK_AKTIF)->count(),
        ];

        return view('pengelola.meja.index', [
            'mejas' => $mejas,
            'statusFilter' => $statusFilter,
            'ringkasan' => $ringkasan,
            'qrService' => $this->qrService,
        ]);
    }

    /**
     * Menampilkan form tambah meja baru.
     */
    public function create(): View
    {
        return view('pengelola.meja.create');
    }

    /**
     * Menyimpan meja baru dan langsung men-generate QR Code uniknya.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nomor_meja' => ['required', 'string', 'max:20', 'unique:meja,nomor_meja'],
            'kapasitas' => ['nullable', 'integer', 'min:1', 'max:50'],
            'status' => ['required', Rule::in([Meja::STATUS_TERSEDIA, Meja::STATUS_TERISI, Meja::STATUS_TIDAK_AKTIF])],
        ], [
            'nomor_meja.required' => 'Nomor meja wajib diisi.',
            'nomor_meja.unique' => 'Nomor meja tersebut sudah terdaftar.',
            'kapasitas.min' => 'Kapasitas kursi minimal 1.',
            'kapasitas.max' => 'Kapasitas kursi maksimal 50.',
        ]);

        $meja = Meja::create([
            'nomor_meja' => strtoupper(trim($validated['nomor_meja'])),
            'kapasitas' => $validated['kapasitas'] ?? null,
            'status' => $validated['status'],
        ]);

        $this->qrService->generateAndStoreQrSvg($meja);

        return redirect()
            ->route('pengelola.meja.index')
            ->with('success', "Meja \"{$meja->nomor_meja}\" berhasil ditambahkan dan QR Code otomatis dibuat.");
    }

    /**
     * Menampilkan form edit meja.
     */
    public function edit(Meja $meja): View
    {
        return view('pengelola.meja.edit', [
            'meja' => $meja,
            'qrToken' => $this->qrService->generateToken($meja),
            'scanUrl' => $this->qrService->generateScanUrl($meja),
        ]);
    }

    /**
     * Memperbarui data meja dan otomatis menyinkronkan QR Code jika nomor meja berubah.
     */
    public function update(Request $request, Meja $meja): RedirectResponse
    {
        $validated = $request->validate([
            'nomor_meja' => [
                'required',
                'string',
                'max:20',
                Rule::unique('meja', 'nomor_meja')->ignore($meja->id_meja, 'id_meja'),
            ],
            'kapasitas' => ['nullable', 'integer', 'min:1', 'max:50'],
            'status' => ['required', Rule::in([Meja::STATUS_TERSEDIA, Meja::STATUS_TERISI, Meja::STATUS_TIDAK_AKTIF])],
        ], [
            'nomor_meja.required' => 'Nomor meja wajib diisi.',
            'nomor_meja.unique' => 'Nomor meja tersebut sudah digunakan meja lain.',
        ]);

        $nomorBaru = strtoupper(trim($validated['nomor_meja']));
        $nomorBerubah = $nomorBaru !== $meja->nomor_meja;

        $meja->update([
            'nomor_meja' => $nomorBaru,
            'kapasitas' => $validated['kapasitas'] ?? null,
            'status' => $validated['status'],
        ]);

        if ($nomorBerubah || empty($meja->qr_code_path)) {
            $this->qrService->generateAndStoreQrSvg($meja);
        }

        return redirect()
            ->route('pengelola.meja.index')
            ->with('success', "Data meja \"{$meja->nomor_meja}\" berhasil diperbarui.");
    }

    /**
     * Men-generate ulang file QR Code meja secara manual.
     */
    public function regenerateQr(Meja $meja): RedirectResponse
    {
        $this->qrService->generateAndStoreQrSvg($meja);

        return redirect()
            ->route('pengelola.meja.index')
            ->with('success', "QR Code untuk meja \"{$meja->nomor_meja}\" berhasil diperbarui.");
    }

    /**
     * Menampilkan preview gambar QR Code SVG (digunakan inline pada daftar/detail meja).
     */
    public function previewQr(Meja $meja): Response
    {
        $svg = $this->qrService->renderQrSvg($meja, 220);

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml; charset=utf-8',
            'Cache-Control' => 'no-store, max-age=0',
        ]);
    }

    /**
     * Mengunduh gambar stiker QR Meja siap cetak (SVG Modern Culinary).
     */
    public function downloadQr(Meja $meja): Response
    {
        // Pastikan file QR dasar juga tersimpan di storage
        if (empty($meja->qr_code_path)) {
            $this->qrService->generateAndStoreQrSvg($meja);
        }

        $stickerSvg = $this->qrService->renderStickerSvg($meja);
        $slug = Str::slug((string) $meja->nomor_meja, '-');
        $filename = "stiker-qr-meja-{$slug}.svg";

        return response($stickerSvg, 200, [
            'Content-Type' => 'image/svg+xml; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
