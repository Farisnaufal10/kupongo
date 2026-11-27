@extends('layouts.pengelola')

@section('title', 'Edit Meja')
@section('page_title', 'Edit Meja ' . $meja->nomor_meja)
@section('page_subtitle', 'Perbarui informasi meja. Jika nomor meja diubah, token & gambar QR akan otomatis diperbarui.')

@section('page_actions')
    <a
        href="{{ route('pengelola.meja.index') }}"
        class="inline-flex items-center justify-center min-h-[44px] px-4 rounded-xl border border-ui-border bg-surface-default text-sm font-semibold text-ui-text hover:bg-brand-ivory transition-colors"
    >
        &larr; Kembali ke Daftar
    </a>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-surface-default border border-ui-border rounded-2xl shadow-xs overflow-hidden">
            <div class="h-1 bg-brand-gold" aria-hidden="true"></div>

            <form method="POST" action="{{ route('pengelola.meja.update', $meja) }}" class="p-6 sm:p-8 space-y-5" novalidate>
                @csrf
                @method('PUT')

                <div>
                    <label for="nomor_meja" class="block text-sm font-semibold text-ui-text mb-1.5">
                        Nomor / Label Meja <span class="text-state-danger">*</span>
                    </label>
                    <input
                        id="nomor_meja"
                        name="nomor_meja"
                        type="text"
                        value="{{ old('nomor_meja', $meja->nomor_meja) }}"
                        required
                        class="w-full h-12 px-3.5 rounded-xl border {{ $errors->has('nomor_meja') ? 'border-state-danger' : 'border-ui-border focus:border-brand-forest' }} bg-surface-default text-sm text-ui-text focus:outline-none focus:ring-2 focus:ring-brand-forest/20"
                    >
                    @error('nomor_meja')
                        <p class="mt-1.5 text-xs font-medium text-state-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="kapasitas" class="block text-sm font-semibold text-ui-text mb-1.5">
                            Kapasitas Kursi
                        </label>
                        <input
                            id="kapasitas"
                            name="kapasitas"
                            type="number"
                            min="1"
                            max="50"
                            value="{{ old('kapasitas', $meja->kapasitas) }}"
                            class="w-full h-12 px-3.5 rounded-xl border border-ui-border focus:border-brand-forest bg-surface-default text-sm text-ui-text focus:outline-none focus:ring-2 focus:ring-brand-forest/20"
                        >
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-semibold text-ui-text mb-1.5">
                            Status Meja <span class="text-state-danger">*</span>
                        </label>
                        <select
                            id="status"
                            name="status"
                            class="w-full h-12 px-3.5 rounded-xl border border-ui-border focus:border-brand-forest bg-surface-default text-sm text-ui-text focus:outline-none focus:ring-2 focus:ring-brand-forest/20"
                        >
                            <option value="tersedia" @selected(old('status', $meja->status) === 'tersedia')>Tersedia</option>
                            <option value="terisi" @selected(old('status', $meja->status) === 'terisi')>Terisi</option>
                            <option value="tidak_aktif" @selected(old('status', $meja->status) === 'tidak_aktif')>Tidak Aktif</option>
                        </select>
                    </div>
                </div>

                <div class="pt-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                    <a
                        href="{{ route('pengelola.meja.index') }}"
                        class="inline-flex items-center justify-center min-h-[46px] px-5 rounded-xl border border-ui-border bg-surface-default text-sm font-semibold text-ui-text hover:bg-brand-ivory transition-colors"
                    >
                        Batal
                    </a>
                    <button
                        type="submit"
                        class="min-h-[46px] px-6 rounded-xl bg-brand-forest hover:bg-brand-forest-hover text-white text-sm font-semibold transition-colors cursor-pointer"
                    >
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        {{-- Kartu Preview QR Meja --}}
        <div class="bg-surface-default border border-ui-border rounded-2xl p-6 flex flex-col items-center text-center gap-4">
            <h2 class="text-base font-bold text-ui-text">QR Code Aktif</h2>
            <img
                src="{{ route('pengelola.meja.qr.preview', $meja) }}"
                alt="QR Code Meja {{ $meja->nomor_meja }}"
                width="180"
                height="180"
                class="w-44 h-44 rounded-xl border border-ui-border bg-white p-2"
            >
            <div class="w-full rounded-xl bg-brand-ivory border border-ui-border p-3 text-left space-y-1">
                <p class="text-[11px] font-semibold text-ui-muted uppercase">Token QR</p>
                <p class="text-xs font-mono text-ui-text break-all">{{ $qrToken }}</p>
                <p class="pt-1 text-[11px] font-semibold text-ui-muted uppercase">URL Scan</p>
                <p class="text-xs font-mono text-ui-text break-all">{{ $scanUrl }}</p>
            </div>
            <a
                href="{{ route('pengelola.meja.qr.download', $meja) }}"
                class="w-full inline-flex items-center justify-center min-h-[44px] px-4 rounded-xl bg-brand-forest hover:bg-brand-forest-hover text-white text-xs font-semibold transition-colors"
            >
                Unduh Stiker QR Siap Cetak
            </a>
        </div>
    </div>
@endsection
