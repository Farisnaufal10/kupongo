<?php

namespace App\Http\Controllers\Pengelola;

use App\Http\Controllers\Controller;
use App\Models\Meja;
use App\Models\Order;
use App\Models\SubOrder;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Dashboard Analitik Pengelola Sentra Kuliner D'Kupon (FR-28).
 *
 * Prinsip akurasi keuangan:
 * - Hanya menghitung sub-order dengan status_pembayaran = 'sudah_dibayar'
 *   pada master order yang tidak dibatalkan/kedaluwarsa.
 * - Transaksi 'menunggu_pembayaran', 'dibatalkan', dan 'kedaluwarsa' TIDAK pernah dihitung
 *   ke dalam Total Omzet Valid maupun Total Transaksi Lunas.
 */
class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $periode = (string) $request->query('periode', 'semua');
        if (! in_array($periode, ['semua', 'hari_ini', '7_hari', 'bulan_ini'], true)) {
            $periode = 'semua';
        }

        $startDate = match ($periode) {
            'hari_ini' => Carbon::today(),
            '7_hari' => Carbon::now()->subDays(6)->startOfDay(),
            'bulan_ini' => Carbon::now()->startOfMonth(),
            default => null,
        };

        // 1. Total Omzet Valid (hanya sub_orders.status_pembayaran = 'sudah_dibayar')
        $omzetQuery = SubOrder::query()
            ->join('orders', 'orders.id_order', '=', 'sub_orders.id_order')
            ->where('sub_orders.status_pembayaran', SubOrder::STATUS_PEMBAYARAN_SUDAH)
            ->whereNotIn('orders.status_pembayaran', [
                Order::STATUS_DIBATALKAN,
                Order::STATUS_KEDALUWARSA,
            ]);

        if ($startDate !== null) {
            $omzetQuery->where('orders.waktu_pesan', '>=', $startDate);
        }

        $totalOmzetValid = (float) $omzetQuery->sum('sub_orders.subtotal');

        // 2. Total Transaksi Lunas (master orders dengan status_pembayaran = 'sudah_dibayar')
        $transaksiLunasQuery = Order::query()
            ->where('status_pembayaran', Order::STATUS_SUDAH_DIBAYAR);

        if ($startDate !== null) {
            $transaksiLunasQuery->where('waktu_pesan', '>=', $startDate);
        }

        $totalTransaksiLunas = (int) $transaksiLunasQuery->count();

        // 3. Jumlah Tenant Aktif & Meja Aktif
        $jumlahTenantAktif = Tenant::where('is_active', true)->count();
        $jumlahTenantBuka = Tenant::where('is_active', true)
            ->where('status_operasional', Tenant::STATUS_OPERASIONAL_BUKA)
            ->count();
        $jumlahMejaAktif = Meja::where('status', '!=', Meja::STATUS_TIDAK_AKTIF)->count();

        // 4. Rekap Penjualan per Tenant (hanya sub_orders 'sudah_dibayar')
        $salesByTenantQuery = DB::table('sub_orders')
            ->join('orders', 'orders.id_order', '=', 'sub_orders.id_order')
            ->where('sub_orders.status_pembayaran', SubOrder::STATUS_PEMBAYARAN_SUDAH)
            ->whereNotIn('orders.status_pembayaran', [
                Order::STATUS_DIBATALKAN,
                Order::STATUS_KEDALUWARSA,
            ])
            ->select(
                'sub_orders.id_tenant',
                DB::raw('COUNT(sub_orders.id_suborder) as jumlah_suborder_lunas'),
                DB::raw('COALESCE(SUM(sub_orders.subtotal), 0) as total_omzet')
            )
            ->groupBy('sub_orders.id_tenant');

        if ($startDate !== null) {
            $salesByTenantQuery->where('orders.waktu_pesan', '>=', $startDate);
        }

        $salesMap = $salesByTenantQuery->get()->keyBy('id_tenant');

        $rekapTenant = Tenant::query()
            ->orderBy('nama_tenant')
            ->get()
            ->map(function (Tenant $tenant) use ($salesMap, $totalOmzetValid) {
                $row = $salesMap->get($tenant->id_tenant);
                $omzet = $row ? (float) $row->total_omzet : 0.0;
                $subOrderCount = $row ? (int) $row->jumlah_suborder_lunas : 0;
                $kontribusi = $totalOmzetValid > 0
                    ? round(($omzet / $totalOmzetValid) * 100, 1)
                    : 0.0;

                return [
                    'id_tenant' => (int) $tenant->id_tenant,
                    'nama_tenant' => (string) $tenant->nama_tenant,
                    'is_active' => (bool) $tenant->is_active,
                    'status_operasional' => (string) $tenant->status_operasional,
                    'jumlah_suborder_lunas' => $subOrderCount,
                    'total_omzet' => $omzet,
                    'kontribusi' => $kontribusi,
                ];
            })
            ->sortByDesc('total_omzet')
            ->values();

        $maxOmzetTenant = (float) ($rekapTenant->max('total_omzet') ?: 0);

        return view('pengelola.dashboard', [
            'periode' => $periode,
            'totalOmzetValid' => $totalOmzetValid,
            'totalTransaksiLunas' => $totalTransaksiLunas,
            'jumlahTenantAktif' => $jumlahTenantAktif,
            'jumlahTenantBuka' => $jumlahTenantBuka,
            'jumlahMejaAktif' => $jumlahMejaAktif,
            'rekapTenant' => $rekapTenant,
            'maxOmzetTenant' => $maxOmzetTenant,
        ]);
    }
}
