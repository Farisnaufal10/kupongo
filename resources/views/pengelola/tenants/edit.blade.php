@extends('layouts.pengelola')

@section('title', 'Edit Tenant')
@section('page_title', 'Edit Data Tenant')
@section('page_subtitle', 'Perbarui profil gerai kuliner dan kredensial login untuk ' . $tenant->nama_tenant . '.')

@section('page_actions')
    <a
        href="{{ route('pengelola.tenants.index') }}"
        class="inline-flex items-center justify-center min-h-[44px] px-4 rounded-xl border border-ui-border bg-surface-default text-sm font-semibold text-ui-text hover:bg-brand-ivory transition-colors"
    >
        &larr; Kembali ke Daftar
    </a>
@endsection

@section('content')
    <div class="max-w-2xl bg-surface-default border border-ui-border rounded-2xl shadow-xs overflow-hidden">
        <div class="h-1 bg-brand-gold" aria-hidden="true"></div>

        <form method="POST" action="{{ route('pengelola.tenants.update', $tenant) }}" class="p-6 sm:p-8 space-y-5" novalidate>
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="nama_tenant" class="block text-sm font-semibold text-ui-text mb-1.5">
                        Nama Tenant / Gerai <span class="text-state-danger">*</span>
                    </label>
                    <input
                        id="nama_tenant"
                        name="nama_tenant"
                        type="text"
                        value="{{ old('nama_tenant', $tenant->nama_tenant) }}"
                        required
                        class="w-full h-12 px-3.5 rounded-xl border {{ $errors->has('nama_tenant') ? 'border-state-danger' : 'border-ui-border focus:border-brand-forest' }} bg-surface-default text-sm text-ui-text focus:outline-none focus:ring-2 focus:ring-brand-forest/20"
                    >
                    @error('nama_tenant')
                        <p class="mt-1.5 text-xs font-medium text-state-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="nama_pemilik" class="block text-sm font-semibold text-ui-text mb-1.5">
                        Nama Penanggung Jawab
                    </label>
                    <input
                        id="nama_pemilik"
                        name="nama_pemilik"
                        type="text"
                        value="{{ old('nama_pemilik', $tenant->user?->name) }}"
                        class="w-full h-12 px-3.5 rounded-xl border border-ui-border focus:border-brand-forest bg-surface-default text-sm text-ui-text focus:outline-none focus:ring-2 focus:ring-brand-forest/20"
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="nomor_kontak" class="block text-sm font-semibold text-ui-text mb-1.5">
                        Nomor Kontak / WhatsApp
                    </label>
                    <input
                        id="nomor_kontak"
                        name="nomor_kontak"
                        type="text"
                        value="{{ old('nomor_kontak', $tenant->nomor_kontak) }}"
                        class="w-full h-12 px-3.5 rounded-xl border border-ui-border focus:border-brand-forest bg-surface-default text-sm text-ui-text focus:outline-none focus:ring-2 focus:ring-brand-forest/20"
                    >
                </div>

                <div>
                    <label for="status_operasional" class="block text-sm font-semibold text-ui-text mb-1.5">
                        Status Operasional
                    </label>
                    <select
                        id="status_operasional"
                        name="status_operasional"
                        class="w-full h-12 px-3.5 rounded-xl border border-ui-border focus:border-brand-forest bg-surface-default text-sm text-ui-text focus:outline-none focus:ring-2 focus:ring-brand-forest/20"
                    >
                        <option value="buka" @selected(old('status_operasional', $tenant->status_operasional) === 'buka')>Buka</option>
                        <option value="tutup" @selected(old('status_operasional', $tenant->status_operasional) === 'tutup')>Tutup</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="deskripsi" class="block text-sm font-semibold text-ui-text mb-1.5">
                    Deskripsi Singkat Gerai
                </label>
                <textarea
                    id="deskripsi"
                    name="deskripsi"
                    rows="3"
                    class="w-full px-3.5 py-3 rounded-xl border border-ui-border focus:border-brand-forest bg-surface-default text-sm text-ui-text focus:outline-none focus:ring-2 focus:ring-brand-forest/20"
                >{{ old('deskripsi', $tenant->deskripsi) }}</textarea>
            </div>

            <div class="pt-4 border-t border-ui-border space-y-4">
                <h2 class="text-base font-bold text-ui-text">Kredensial Akun Login Tenant</h2>

                <div>
                    <label for="username" class="block text-sm font-semibold text-ui-text mb-1.5">
                        Username / Email Login <span class="text-state-danger">*</span>
                    </label>
                    <input
                        id="username"
                        name="username"
                        type="text"
                        value="{{ old('username', $tenant->user?->username) }}"
                        required
                        class="w-full h-12 px-3.5 rounded-xl border {{ $errors->has('username') ? 'border-state-danger' : 'border-ui-border focus:border-brand-forest' }} bg-surface-default text-sm text-ui-text focus:outline-none focus:ring-2 focus:ring-brand-forest/20"
                    >
                    @error('username')
                        <p class="mt-1.5 text-xs font-medium text-state-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="password" class="block text-sm font-semibold text-ui-text mb-1.5">
                            Kata Sandi Baru (Opsional)
                        </label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            placeholder="Kosongkan jika tidak diubah"
                            class="w-full h-12 px-3.5 rounded-xl border {{ $errors->has('password') ? 'border-state-danger' : 'border-ui-border focus:border-brand-forest' }} bg-surface-default text-sm text-ui-text focus:outline-none focus:ring-2 focus:ring-brand-forest/20"
                        >
                        @error('password')
                            <p class="mt-1.5 text-xs font-medium text-state-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-ui-text mb-1.5">
                            Konfirmasi Kata Sandi Baru
                        </label>
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            placeholder="Ulangi kata sandi baru"
                            class="w-full h-12 px-3.5 rounded-xl border border-ui-border focus:border-brand-forest bg-surface-default text-sm text-ui-text focus:outline-none focus:ring-2 focus:ring-brand-forest/20"
                        >
                    </div>
                </div>
            </div>

            <div class="pt-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                <a
                    href="{{ route('pengelola.tenants.index') }}"
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
@endsection
