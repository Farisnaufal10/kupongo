<?php

namespace App\Http\Controllers\Pengelola;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Manajemen master data Tenant & akun login oleh Pengelola (FR-24).
 *
 * Menerapkan soft-deactivation (`is_active = false`) tanpa hard delete
 * demi menjaga integritas riwayat transaksi (`ON DELETE RESTRICT`).
 */
class TenantController extends Controller
{
    /**
     * Menampilkan daftar tenant dengan pencarian dan filter status aktif.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $statusFilter = (string) $request->query('status', 'semua');

        $query = Tenant::query()
            ->with('user')
            ->withCount('subOrders')
            ->orderByDesc('is_active')
            ->orderBy('nama_tenant');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('nama_tenant', 'like', "%{$search}%")
                    ->orWhere('nomor_kontak', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('username', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($statusFilter === 'aktif') {
            $query->where('is_active', true);
        } elseif ($statusFilter === 'nonaktif') {
            $query->where('is_active', false);
        }

        $tenants = $query->get();

        $ringkasan = [
            'total' => Tenant::count(),
            'aktif' => Tenant::where('is_active', true)->count(),
            'nonaktif' => Tenant::where('is_active', false)->count(),
            'buka' => Tenant::where('is_active', true)
                ->where('status_operasional', Tenant::STATUS_OPERASIONAL_BUKA)
                ->count(),
        ];

        return view('pengelola.tenants.index', [
            'tenants' => $tenants,
            'search' => $search,
            'statusFilter' => $statusFilter,
            'ringkasan' => $ringkasan,
        ]);
    }

    /**
     * Menampilkan form pendaftaran tenant baru beserta akun loginnya.
     */
    public function create(): View
    {
        return view('pengelola.tenants.create');
    }

    /**
     * Menyimpan akun User (role: tenant) dan profil Tenant secara atomik.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_tenant' => ['required', 'string', 'max:100'],
            'nama_pemilik' => ['nullable', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'nomor_kontak' => ['nullable', 'string', 'max:20'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'status_operasional' => ['required', Rule::in([Tenant::STATUS_OPERASIONAL_BUKA, Tenant::STATUS_OPERASIONAL_TUTUP])],
        ], [
            'nama_tenant.required' => 'Nama tenant wajib diisi.',
            'username.required' => 'Username login tenant wajib diisi.',
            'username.unique' => 'Username tersebut sudah digunakan oleh akun lain.',
            'password.required' => 'Kata sandi awal wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sesuai.',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'username' => trim($validated['username']),
                'name' => trim((string) ($validated['nama_pemilik'] ?: $validated['nama_tenant'])),
                'password' => Hash::make($validated['password']),
                'role' => 'tenant',
                'is_active' => true,
            ]);

            Tenant::create([
                'id_user' => $user->id_user,
                'nama_tenant' => trim($validated['nama_tenant']),
                'nomor_kontak' => $validated['nomor_kontak'] ? trim($validated['nomor_kontak']) : null,
                'deskripsi' => $validated['deskripsi'] ? trim($validated['deskripsi']) : null,
                'status_operasional' => $validated['status_operasional'],
                'is_active' => true,
            ]);
        });

        return redirect()
            ->route('pengelola.tenants.index')
            ->with('success', "Tenant \"{$validated['nama_tenant']}\" beserta akun loginnya berhasil ditambahkan.");
    }

    /**
     * Menampilkan form edit profil tenant dan akun loginnya.
     */
    public function edit(Tenant $tenant): View
    {
        $tenant->load('user');

        return view('pengelola.tenants.edit', [
            'tenant' => $tenant,
        ]);
    }

    /**
     * Memperbarui data profil tenant serta kredensial akun login (opsional ganti password).
     */
    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $tenant->load('user');
        $user = $tenant->user;

        $validated = $request->validate([
            'nama_tenant' => ['required', 'string', 'max:100'],
            'nama_pemilik' => ['nullable', 'string', 'max:100'],
            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'username')->ignore($user?->id_user, 'id_user'),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'nomor_kontak' => ['nullable', 'string', 'max:20'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'status_operasional' => ['required', Rule::in([Tenant::STATUS_OPERASIONAL_BUKA, Tenant::STATUS_OPERASIONAL_TUTUP])],
        ], [
            'nama_tenant.required' => 'Nama tenant wajib diisi.',
            'username.required' => 'Username login tenant wajib diisi.',
            'username.unique' => 'Username tersebut sudah digunakan oleh akun lain.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak sesuai.',
        ]);

        DB::transaction(function () use ($validated, $tenant, $user) {
            if ($user) {
                $userPayload = [
                    'username' => trim($validated['username']),
                    'name' => trim((string) ($validated['nama_pemilik'] ?: $validated['nama_tenant'])),
                ];

                if (! empty($validated['password'])) {
                    $userPayload['password'] = Hash::make($validated['password']);
                }

                $user->update($userPayload);
            }

            $tenant->update([
                'nama_tenant' => trim($validated['nama_tenant']),
                'nomor_kontak' => $validated['nomor_kontak'] ? trim($validated['nomor_kontak']) : null,
                'deskripsi' => $validated['deskripsi'] ? trim($validated['deskripsi']) : null,
                'status_operasional' => $tenant->is_active
                    ? $validated['status_operasional']
                    : Tenant::STATUS_OPERASIONAL_TUTUP,
            ]);
        });

        return redirect()
            ->route('pengelola.tenants.index')
            ->with('success', "Data tenant \"{$tenant->nama_tenant}\" berhasil diperbarui.");
    }

    /**
     * Aktivasi / Nonaktifkan akun tenant secara aman (soft-deactivation).
     *
     * Tidak pernah menghapus baris tenant/user agar seluruh riwayat transaksi
     * (`sub_orders`, `order_details`, `payments`) tetap utuh untuk analitik.
     */
    public function toggleStatus(Tenant $tenant): RedirectResponse
    {
        $tenant->load('user');
        $akanAktif = ! $tenant->is_active;

        DB::transaction(function () use ($tenant, $akanAktif) {
            $tenant->update([
                'is_active' => $akanAktif,
                'status_operasional' => $akanAktif
                    ? $tenant->status_operasional
                    : Tenant::STATUS_OPERASIONAL_TUTUP,
            ]);

            if ($tenant->user) {
                $tenant->user->update([
                    'is_active' => $akanAktif,
                ]);
            }
        });

        $pesan = $akanAktif
            ? "Akun dan gerai tenant \"{$tenant->nama_tenant}\" berhasil diaktifkan kembali."
            : "Akun dan gerai tenant \"{$tenant->nama_tenant}\" berhasil dinonaktifkan (riwayat transaksi tetap terjaga).";

        return redirect()
            ->route('pengelola.tenants.index')
            ->with('success', $pesan);
    }
}
