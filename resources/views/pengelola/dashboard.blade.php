@extends('layouts.pengelola')

@section('title', 'Dashboard Analitik')
@section('page_title', 'Dashboard Analitik Pengelola')
@section('page_subtitle', 'Ringkasan omzet valid, transaksi lunas, dan kontribusi penjualan seluruh tenant Sentra Kuliner D\'Kupon.')

@section('page_actions')
    <div class="inline-flex flex-wrap items-center gap-1.5 bg-brand-ivory p-1 rounded-xl border border-ui-border">
        @foreach (['semua' => 'Semua Waktu', 'hari_ini' => 'Hari Ini', '7_hari' => '7 Hari', 'bulan_ini' => 'Bulan Ini'] as $key => $label)
            <a
                href="{{ route('pengelola.dashboard', $key === 'semua' ? [] : ['periode' => $key]) }}"
                class="inline-flex items-center justify-center min-h-[38px] px-3 rounded-lg text-xs font-semibold transition-colors {{ $periode === $key ? 'bg-brand-forest text-white shadow-xs' : 'text-ui-text hover:bg-surface-default' }}"
            >
                {{ $label }}
            </a>
        @endforeach
    </div>
@endsection

@section('content')
    {{-- Kartu Metrik Utama --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-surface-default border border-ui-border rounded-2xl p-5 shadow-xs relative overflow-hidden">
            <div class="absolute inset-x-0 top-0 h-1 bg-brand-forest" aria-hidden="true"></div>
            <p class="text-xs font-semibold text-ui-muted">Total Omzet Valid</p>
            <p class="mt-2 text-2xl font-bold text-brand-forest tabular-nums">
                Rp {{ number_format($totalOmzetValid, 0, ',', '.') }}
            </p>
            <p class="mt-1.5 text-xs text-ui-muted">
                Hanya sub-order berstatus <span class="font-semibold text-state-success">Sudah Dibayar</span>
            </p>
        </div>

        <div class="bg-surface-default border border-ui-border rounded-2xl p-5 shadow-xs relative overflow-hidden">
            <div class="absolute inset-x-0 top-0 h-1 bg-brand-gold" aria-hidden="true"></div>
            <p class="text-xs font-semibold text-ui-muted">Total Transaksi Lunas</p>
            <p class="mt-2 text-2xl font-bold text-ui-text tabular-nums">
                {{ number_format($totalTransaksiLunas, 0, ',', '.') }}
            </p>
            <p class="mt-1.5 text-xs text-ui-muted">
                Master order berstatus lunas
            </p>
        </div>

        <div class="bg-surface-default border border-ui-border rounded-2xl p-5 shadow-xs relative overflow-hidden">
            <div class="absolute inset-x-0 top-0 h-1 bg-state-success" aria-hidden="true"></div>
            <p class="text-xs font-semibold text-ui-muted">Jumlah Tenant Aktif</p>
            <p class="mt-2 text-2xl font-bold text-ui-text tabular-nums">
                {{ number_format($jumlahTenantAktif, 0, ',', '.') }}
            </p>
            <p class="mt-1.5 text-xs text-ui-muted">
                {{ number_format($jumlahTenantBuka, 0, ',', '.') }} tenant sedang beroperasi buka
            </p>
        </div>

        <div class="bg-surface-default border border-ui-border rounded-2xl p-5 shadow-xs relative overflow-hidden">
            <div class="absolute inset-x-0 top-0 h-1 bg-brand-gold/60" aria-hidden="true"></div>
            <p class="text-xs font-semibold text-ui-muted">Meja Siap Pakai</p>
            <p class="mt-2 text-2xl font-bold text-ui-text tabular-nums">
                {{ number_format($jumlahMejaAktif, 0, ',', '.') }}
            </p>
            <p class="mt-1.5 text-xs text-ui-muted">
                Status tersedia atau terisi
            </p>
        </div>
    </div>

    {{-- Grafik Perbandingan Omzet per Tenant --}}
    <section class="bg-surface-default border border-ui-border rounded-2xl p-6 shadow-xs space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="text-lg font-bold text-ui-text">Grafik Omzet Valid per Tenant</h2>
                <p class="text-xs text-ui-muted">Perbandingan kontribusi pendapatan sah berdasarkan sub-order lunas.</p>
            </div>
            <a
                href="{{ route('pengelola.tenants.index') }}"
                class="text-xs font-semibold text-brand-forest hover:underline"
            >
                Kelola Tenant &rarr;
            </a>
        </div>

        @if ($rekapTenant->isEmpty() || $totalOmzetValid <= 0)
            <div class="rounded-xl border border-ui-border bg-brand-ivory/60 p-8 text-center">
                <p class="text-sm font-semibold text-ui-text">Belum ada transaksi lunas pada periode ini</p>
                <p class="mt-1 text-xs text-ui-muted">
                    Grafik omzet akan terisi otomatis setelah terdapat transaksi berstatus Sudah Dibayar.
                </p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($rekapTenant as $item)
                    @php
                        $barWidth = $maxOmzetTenant > 0 ? max(2, round(($item['total_omzet'] / $maxOmzetTenant) * 100, 1)) : 0;
                    @endphp
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="font-semibold text-ui-text truncate">{{ $item['nama_tenant'] }}</span>
                                @if (! $item['is_active'])
                                    <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-state-danger-bg text-state-danger">
                                        Nonaktif
                                    </span>
                                @endif
                            </div>
                            <span class="shrink-0 font-semibold text-ui-text tabular-nums">
                                Rp {{ number_format($item['total_omzet'], 0, ',', '.') }}
                                <span class="text-xs font-normal text-ui-muted">({{ number_format($item['kontribusi'], 1, ',', '.') }}%)</span>
                            </span>
                        </div>
                        <div class="w-full h-3 rounded-full bg-brand-ivory border border-ui-border overflow-hidden">
                            <div
                                class="h-full rounded-full {{ $loop->first ? 'bg-brand-forest' : 'bg-brand-gold' }}"
                                style="width: {{ $item['total_omzet'] > 0 ? $barWidth : 0 }}%"
                            ></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Tabel Rekap Penjualan per Tenant --}}
    <section class="bg-surface-default border border-ui-border rounded-2xl shadow-xs overflow-hidden">
        <div class="px-6 py-5 border-b border-ui-border flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="text-lg font-bold text-ui-text">Rekap Penjualan per Tenant</h2>
                <p class="text-xs text-ui-muted">Menampilkan seluruh tenant termasuk yang dinonaktifkan agar histori keuangan tetap utuh.</p>
            </div>
        </div>

        @if ($rekapTenant->isEmpty())
            <div class="p-8 text-center">
                <p class="text-sm font-semibold text-ui-text">Belum ada tenant terdaftar</p>
                <p class="mt-1 text-xs text-ui-muted">Tambahkan tenant terlebih dahulu melalui menu Kelola Tenant.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-ui-border bg-brand-ivory/70 text-xs font-semibold uppercase tracking-wide text-ui-muted">
                            <th class="px-6 py-3.5">Nama Tenant</th>
                            <th class="px-4 py-3.5">Status</th>
                            <th class="px-4 py-3.5 text-right">Sub-Order Lunas</th>
                            <th class="px-4 py-3.5 text-right">Kontribusi</th>
                            <th class="px-6 py-3.5 text-right">Total Omzet Valid</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ui-border text-sm">
                        @foreach ($rekapTenant as $item)
                            <tr class="hover:bg-brand-ivory/40 transition-colors">
                                <td class="px-6 py-4 font-semibold text-ui-text">
                                    {{ $item['nama_tenant'] }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    @if (! $item['is_active'])
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-state-danger-bg text-state-danger">
                                            Nonaktif
                                        </span>
                                    @elseif ($item['status_operasional'] === \App\Models\Tenant::STATUS_OPERASIONAL_BUKA)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-state-success-bg text-state-success">
                                            Aktif &middot; Buka
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-state-warning-bg text-state-warning">
                                            Aktif &middot; Tutup
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-right tabular-nums text-ui-text whitespace-nowrap">
                                    {{ number_format($item['jumlah_suborder_lunas'], 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-4 text-right tabular-nums text-ui-muted whitespace-nowrap">
                                    {{ number_format($item['kontribusi'], 1, ',', '.') }}%
                                </td>
                                <td class="px-6 py-4 text-right font-bold tabular-nums text-brand-forest whitespace-nowrap">
                                    Rp {{ number_format($item['total_omzet'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
