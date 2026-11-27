@extends('layouts.pengelola')

@section('title', 'Tambah Meja')
@section('page_title', 'Tambah Meja Baru')
@section('page_subtitle', 'QR Code unik akan otomatis dibuat segera setelah meja disimpan.')

@section('page_actions')
    <a
        href="{{ route('pengelola.meja.index') }}"
        class="inline-flex items-center justify-center min-h-[44px] px-4 rounded-xl border border-ui-border bg-surface-default text-sm font-semibold text-ui-text hover:bg-brand-ivory transition-colors"
    >
        &larr; Kembali ke Daftar
    </a>
@endsection

@section('content')
    <div class="max-w-xl bg-surface-default border border-ui-border rounded-2xl shadow-xs overflow-hidden">
        <div class="h-1 bg-brand-gold" aria-hidden="true"></div>

        <form method="POST" action="{{ route('pengelola.meja.store') }}" class="p-6 sm:p-8 space-y-5" novalidate>
            @csrf

            <div>
                <label for="nomor_meja" class="block text-sm font-semibold text-ui-text mb-1.5">
                    Nomor / Label Meja <span class="text-state-danger">*</span>
                </label>
                <input
                    id="nomor_meja"
                    name="nomor_meja"
                    type="text"
                    value="{{ old('nomor_meja') }}"
                    required
                    placeholder="Contoh: M-01, A-12"
                    class="w-full h-12 px-3.5 rounded-xl border {{ $errors->has('nomor_meja') ? 'border-state-danger' : 'border-ui-border focus:border-brand-forest' }} bg-surface-default text-sm text-ui-text focus:outline-none focus:ring-2 focus:ring-brand-forest/20"
                >
                @error('nomor_meja')
                    <p class="mt-1.5 text-xs font-medium text-state-danger">{{ $message }}</p>
                @else
                    <p class="mt-1.5 text-xs text-ui-muted">Nomor meja bersifat unik dan akan tampil pada stiker QR cetak.</p>
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
                        value="{{ old('kapasitas', 4) }}"
                        class="w-full h-12 px-3.5 rounded-xl border {{ $errors->has('kapasitas') ? 'border-state-danger' : 'border-ui-border focus:border-brand-forest' }} bg-surface-default text-sm text-ui-text focus:outline-none focus:ring-2 focus:ring-brand-forest/20"
                    >
                    @error('kapasitas')
                        <p class="mt-1.5 text-xs font-medium text-state-danger">{{ $message }}</p>
                    @enderror
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
                        <option value="tersedia" @selected(old('status', 'tersedia') === 'tersedia')>Tersedia</option>
                        <option value="terisi" @selected(old('status') === 'terisi')>Terisi</option>
                        <option value="tidak_aktif" @selected(old('status') === 'tidak_aktif')>Tidak Aktif</option>
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
                    Simpan &amp; Buat QR
                </button>
            </div>
        </form>
    </div>
@endsection
