# KuponGo

**KuponGo** adalah sistem pemesanan makanan dan minuman berbasis web yang dirancang khusus untuk Sentra Kuliner D'Kupon. Sistem ini mengintegrasikan pemesanan dari beberapa tenant dalam satu keranjang belanja (*multi-tenant single cart*), identifikasi meja menggunakan QR Code, fleksibilitas metode pembayaran (QRIS dan tunai), pengelolaan pesanan terdistribusi (*master order* dan *sub-order*), pencatatan transaksi terpisah per tenant, serta dashboard analitik penjualan terpusat.

Proyek ini dikembangkan oleh **Kelompok 38 – Program Studi Sistem Informasi, Telkom University Jakarta** sebagai bagian dari Capstone Design Project.

---

## Daftar Isi

- [Fitur yang Direncanakan](#fitur-yang-direncanakan)
  - [1. Pembeli](#1-pembeli)
  - [2. Tenant](#2-tenant)
  - [3. Pengelola](#3-pengelola)
  - [4. Pengelolaan Pesanan dan Pembayaran](#4-pengelolaan-pesanan-dan-pembayaran)
- [Kebutuhan Non-Fungsional](#kebutuhan-non-fungsional)
- [Metode Pengembangan](#metode-pengembangan)
- [Teknologi Pengembangan](#teknologi-pengembangan)
- [Panduan Instalasi dan Memulai Pengembangan](#panduan-instalasi-dan-memulai-pengembangan)
  - [1. Prasyarat Sistem](#1-prasyarat-sistem)
  - [2. Kloning Repository](#2-kloning-repository)
  - [3. Instalasi Dependensi Backend dan Frontend](#3-instalasi-dependensi-backend-dan-frontend)
  - [4. Konfigurasi Environment](#4-konfigurasi-environment)
  - [5. Konfigurasi Basis Data](#5-konfigurasi-basis-data)
  - [6. Kompilasi Aset Frontend](#6-kompilasi-aset-frontend)
  - [7. Menjalankan Server Pengembangan](#7-menjalankan-server-pengembangan)
  - [8. Pengujian Otomatis](#8-pengujian-otomatis)
- [Alur Kerja Git dan Panduan Kontribusi](#alur-kerja-git-dan-panduan-kontribusi)
  - [Aturan Utama](#aturan-utama)
  - [Langkah Pembuatan Branch dan Kontribusi](#langkah-pembuatan-branch-dan-kontribusi)
  - [Standar Penamaan Branch](#standar-penamaan-branch)
- [Keamanan Repository](#keamanan-repository)
- [Status Pengembangan](#status-pengembangan)

---

## Fitur yang Direncanakan

Pengembangan sistem mengacu pada Functional Requirement (FR), Non-Functional Requirement (NFR), Use Case Diagram, dan rancangan sistem pada dokumen proposal Capstone.

### 1. Pembeli

- Memindai QR Code meja untuk mengidentifikasi nomor meja pemesanan. (FR-01)
- Melihat daftar tenant dan menu beserta harga serta ketersediaannya. (FR-02, FR-03)
- Menyusun pesanan dari beberapa tenant dalam satu keranjang, mengatur jumlah menu, dan menambahkan catatan khusus. (FR-04, FR-05, FR-06)
- Melihat rincian pesanan dan total pembayaran berdasarkan tenant. (FR-07)
- Memilih pembayaran QRIS atau tunai dan menyelesaikan proses checkout. (FR-09–FR-13)
- Melihat status pengerjaan pesanan secara berkala. (FR-08)
- Mendapatkan pembatasan pemesanan secara otomatis pada tenant yang berstatus tutup. (FR-30)

### 2. Tenant

- Login menggunakan akun tenant yang telah terdaftar. (FR-21)
- Mengubah status operasional tenant menjadi buka atau tutup secara mandiri. (FR-22)
- Mengelola data menu, harga, foto, serta status ketersediaan stok menu. (FR-23)
- Mengonfirmasi penerimaan pembayaran tunai untuk sub-pesanan miliknya. (FR-14)
- Melihat sub-pesanan yang telah dibayar dan diteruskan ke tenant terkait. (FR-18)
- Memperbarui status pengerjaan pesanan (*Diproses*, *Siap Diambil/Disajikan*, *Selesai*). (FR-19)
- Menerima pemberitahuan pesanan baru sebagai fitur pengembangan sesuai prioritas kebutuhan. (FR-20)
- Melihat riwayat transaksi dan ringkasan penjualan harian/bulanan tenant. (FR-26, FR-27)

### 3. Pengelola

- Login menggunakan akun pengelola/administrator sentra kuliner. (FR-21)
- Mengelola master data dan akun tenant (pendaftaran, verifikasi, dan penonaktifan). (FR-24)
- Mengelola data meja fisik, menghasilkan (*generate*), serta mengunduh QR Code meja. (FR-29)
- Mengakses dashboard analitik penjualan seluruh tenant secara berkala untuk monitoring operasional sentra kuliner. (FR-28)

### 4. Pengelolaan Pesanan dan Pembayaran

- Menghasilkan satu kode pesanan utama (*master order*) untuk setiap transaksi checkout pembeli. (FR-12)
- Memisahkan (*splitting*) pesanan utama menjadi beberapa sub-pesanan berdasarkan tenant terkait. (FR-15)
- Mencatat dan mengelola status pembayaran setiap sub-pesanan secara independen. (FR-16)
- Memperbarui status pembayaran *master order* secara otomatis berdasarkan agregasi status seluruh sub-pesanan. (FR-17)
- Meneruskan notifikasi/antrean sub-pesanan yang telah valid pembayarannya kepada masing-masing tenant. (FR-18)
- Mencatat pembukuan transaksi penjualan terpisah per tenant. (FR-25)
- Memproses satu pembayaran QRIS untuk pesanan multi-tenant melalui layanan payment gateway yang diuji pada lingkungan pengujian (*sandbox*). (FR-10, FR-11)

> [!IMPORTANT]
> **Rencana Integrasi Payment Gateway & Pembagian Dana:**
> Sistem dirancang untuk menghitung alokasi pembayaran berdasarkan rincian tenant pada sub-order. Integrasi pembagian dana otomatis (*split payment*) saat ini masih memerlukan evaluasi dan pemilihan penyedia Payment Gateway pihak ketiga yang mendukung skema *marketplace/disbursement* multi-merchant. Pengujian mekanisme ini dilakukan bertahap pada lingkungan *sandbox* dan belum terverifikasi secara penuh untuk penyaluran otomatis ke rekening masing-masing tenant di tahap fondasi ini.
>
> Untuk opsi pembayaran tunai, pembeli melakukan pembayaran langsung ke kasir tenant terkait, dan tenant akan memvalidasi pembayaran sub-pesanan miliknya secara manual pada dashboard tenant.

---

## Kebutuhan Non-Fungsional

Implementasi sistem mengacu pada kebutuhan non-fungsional (NFR) dalam proposal:

- **Aksesibilitas:** Aplikasi web dapat diakses optimal melalui peramban web pada perangkat *mobile* (smartphone) maupun *desktop*. (NFR-01, NFR-02)
- **Keandalan:** Menjamin integritas dan konsistensi data antara *master order* dan *sub-order*, mencegah terjadinya pencatatan pembayaran ganda (*idempotency*), serta memastikan data sesi keranjang dan transaksi tidak hilang saat halaman dimuat ulang. (NFR-03–NFR-06)
- **Kinerja:** Waktu pemisahan pesanan utama menjadi sub-pesanan kurang dari dua detik pada kondisi pengujian standar, serta pembaruan data pada dashboard analitik terjadi paling lambat lima menit setelah transaksi berhasil. (NFR-07, NFR-08)
- **Keamanan:** Penyimpanan kata sandi akun wajib menggunakan algoritma *hashing* yang aman (Bcrypt) dan pembatasan hak akses berbasis peran (*Role-Based Access Control* / RBAC). (NFR-09, NFR-10)
- **Antarmuka Pengguna:** Antarmuka menggunakan Bahasa Indonesia, mengusung desain responsif, konsisten, serta menyediakan dialog konfirmasi untuk tindakan kritis (seperti penutupan tenant atau pembatalan menu). (NFR-11–NFR-15)

---

## Metode Pengembangan

Proyek KuponGo dikembangkan menggunakan metode **Prototype** yang bersifat iteratif, mencakup tahapan:

1. **Analisis Kebutuhan:** Identifikasi kebutuhan fungsional dan operasional Sentra Kuliner D'Kupon.
2. **Perancangan Sistem:** Pemodelan arsitektur perangkat lunak menggunakan UML (Use Case Diagram, Activity Diagram, Class Diagram, Sequence Diagram) dan Entity Relationship Diagram (ERD).
3. **Pembuatan Prototype:** Pembangunan fondasi arsitektur awal dan modul antarmuka pengguna interaktif.
4. **Evaluasi Pengguna:** Pengujian awal antarmuka bersama perwakilan tenant dan pengelola.
5. **Penyempurnaan Prototype:** Penyesuaian rancangan berdasarkan umpan balik evaluasi.
6. **Implementasi Sistem:** Pengembangan fungsionalitas penuh berbasis Laravel dan integrasi basis data.
7. **Pengujian:** Verifikasi sistem secara komprehensif sebelum rilis akhir.

---

## Teknologi Pengembangan

Berikut adalah rincian tumpukan teknologi (*tech stack*) aktual yang terkonfigurasi pada repository KuponGo:

| Komponen | Teknologi | Keterangan |
|---|---|---|
| **Framework Backend** | [Laravel 12](https://laravel.com/) (`^12.0`) | Framework PHP modern dengan arsitektur MVC |
| **Bahasa Pemrograman** | [PHP](https://www.php.net/) (`^8.2`) | Versi PHP minimal 8.2 sesuai persyaratan Laravel 12 |
| **Basis Data Lokal** | [MariaDB](https://mariadb.org/) 10.11 (via XAMPP) | Layanan basis data lokal XAMPP yang kompatibel dengan protokol MySQL |
| **Koneksi Database Laravel** | Driver `mysql` | Konfigurasi `DB_CONNECTION=mysql` pada Laravel untuk koneksi ke MariaDB lokal |
| **Antarmuka & Styling** | [Laravel Blade](https://laravel.com/docs/12.x/blade) & [Tailwind CSS v4](https://tailwindcss.com/) (`@tailwindcss/vite`) | Template engine bawaan Laravel dipadukan dengan Tailwind CSS versi terbaru |
| **Frontend Tooling** | [Vite v6](https://vite.dev/) (`laravel-vite-plugin`) | Build tool cepat untuk bundling aset CSS dan JavaScript |
| **Version Control** | [Git](https://git-scm.com/) & [GitHub](https://github.com/) | Manajemen versi kode sumber dan alur kolaborasi berbasis Pull Request |
| **Development Tools** | Antigravity IDE, Composer, Node.js (npm), XAMPP Control Panel | Lingkungan pengembangan lokal terpadu |
| **Pengujian** | [PHPUnit 11](https://phpunit.de/) / Laravel Test Runner | Pengujian unit dan fitur otomatis (`php artisan test`), Black Box & UAT |
| **Payment Gateway** | Sandbox Environment | Lingkungan uji pembayaran digital terpadu (QRIS & transfer) |

### Catatan Konfigurasi Dasar Proyek

1. **Kompatibilitas MariaDB dan Driver Laravel:**
   Meskipun database lokal menggunakan MariaDB 10.11 melalui instalasi XAMPP, konfigurasi koneksi Laravel tetap menggunakan parameter standar `DB_CONNECTION=mysql`. MariaDB mengimplementasikan protokol jaringan yang sama dengan MySQL, sehingga koneksi melalui driver PDO MySQL Laravel berjalan optimal tanpa kendala kompatibilitas.
2. **Driver State Awal Sesi, Cache, dan Antrean:**
   Pada tahap fondasi awal ini, file `.env.example` dikonfigurasi menggunakan driver lokal sederhana agar aplikasi dapat langsung dijalankan oleh seluruh anggota tim tanpa dependensi layanan eksternal (seperti Redis) atau tabel database pendukung sebelum migrasi dijalankan:
   - `SESSION_DRIVER=file`
   - `CACHE_STORE=file`
   - `QUEUE_CONNECTION=sync`

---

## Panduan Instalasi dan Memulai Pengembangan

Panduan ini disusun sebagai acuan resmi bagi seluruh anggota **Kelompok 38** dalam menyiapkan lingkungan pengembangan lokal pada mesin masing-masing.

### 1. Prasyarat Sistem

Sebelum memulai, pastikan perangkat Anda telah terpasang:

- **Git** (versi terbaru)
- **PHP** minimal versi 8.2 dengan ekstensi aktif: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `curl`
- **Composer** (manajer paket PHP)
- **Node.js** (rekomendasi versi LTS 20.x atau 22.x) beserta **npm**
- **XAMPP** dengan paket **MariaDB / MySQL** dan **Apache** (untuk phpMyAdmin)
- **Antigravity IDE** atau editor kode yang didukung

### 2. Kloning Repository

Buka terminal (Command Prompt, PowerShell, atau Git Bash) dan jalankan:

```bash
git clone https://github.com/Farisnaufal10/kupongo.git
cd kupongo
```

### 3. Instalasi Dependensi Backend dan Frontend

Instal dependensi PHP melalui Composer:

```bash
composer install
```

Instal dependensi JavaScript dan CSS sesuai dengan `package-lock.json`:

```bash
npm ci
```

> [!TIP]
> Pada sistem operasi Windows (PowerShell), jika perintah `npm` terhalang oleh *execution policy*, jalankan perintah secara spesifik menggunakan `npm.cmd ci`.

### 4. Konfigurasi Environment

Salin template konfigurasi `.env.example` menjadi `.env`:

**Menggunakan PowerShell:**
```powershell
Copy-Item .env.example .env
```

**Menggunakan Bash / Command Prompt:**
```bash
cp .env.example .env
```

Buat kunci enkripsi aplikasi Laravel:

```bash
php artisan key:generate
```

### 5. Konfigurasi Basis Data

1. Buka **XAMPP Control Panel**, kemudian jalankan modul **Apache** dan **MySQL** (MariaDB).
2. Akses phpMyAdmin melalui peramban (`http://127.0.0.1/phpmyadmin`) atau gunakan MySQL CLI, lalu buat database baru dengan nama:
   ```sql
   CREATE DATABASE kupongo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Buka file `.env` di root proyek dan pastikan konfigurasi database sesuai dengan pengaturan lokal MariaDB Anda:
   ```dotenv
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=kupongo
   DB_USERNAME=root
   DB_PASSWORD=
   ```
   *(Kosongkan `DB_PASSWORD` jika menggunakan konfigurasi default instalasi XAMPP).*
4. Pastikan pula konfigurasi state awal tetap menggunakan driver berbasis file/sinkron:
   ```dotenv
   SESSION_DRIVER=file
   CACHE_STORE=file
   QUEUE_CONNECTION=sync
   ```
5. Bersihkan cache konfigurasi dan periksa konektivitas koneksi database:
   ```bash
   php artisan config:clear
   php artisan db:show
   ```

> [!IMPORTANT]
> **Status Migration KuponGo:**
> File migrasi khusus untuk entitas bisnis KuponGo (tabel tenant, menu, master order, sub-order, item pesanan, dan transaksi) **saat ini belum disertakan dalam repository** karena masih menunggu finalisasi Entity Relationship Diagram (ERD) dan kamus data dari anggota tim yang bertanggung jawab. File migrasi resmi akan dibuat dan dibagikan secara terpusat setelah struktur skema ERD disetujui bersama oleh Kelompok 38. Jangan membuat migrasi ad-hoc di branch `main`.

### 6. Kompilasi Aset Frontend

Lakukan build aset produksi menggunakan Vite:

```bash
npm run build
```

Untuk mode pengembangan aktif dengan fitur *Hot Module Replacement* (HMR), Anda dapat menjalankan perintah berikut pada terminal terpisah:

```bash
npm run dev
```

### 7. Menjalankan Server Pengembangan

Jalankan server pengembangan bawaan Laravel:

```bash
php artisan serve
```

Aplikasi KuponGo kini dapat diakses melalui peramban pada alamat:
```text
http://127.0.0.1:8000
```

### 8. Pengujian Otomatis

Jalankan rangkaian tes otomatis untuk memastikan fondasi aplikasi berjalan normal:

```bash
php artisan test
```

> [!NOTE]
> Pengujian saat ini menjalankan suite pengujian dasar bawaan Laravel (status respons HTTP dan arsitektur pengujian). Pengujian fitur logika bisnis KuponGo akan ditambahkan secara bertahap sejalan dengan pengembangan modul.

---

## Alur Kerja Git dan Panduan Kontribusi

Untuk menjaga stabilitas kode dan kelancaran kolaborasi 4 anggota tim, setiap anggota **Kelompok 38** wajib mematuhi panduan Git berikut:

### Aturan Utama

1. **DILARANG KERAS melakukan commit atau push langsung ke branch `main`.**
2. Branch `main` hanya diperuntukkan bagi kode yang stabil, telah diuji, dan telah disetujui melalui mekanisme **Pull Request (PR)**.
3. Seluruh pekerjaan fitur, perbaikan bug, atau penambahan dokumen harus dilakukan pada branch terpisah (*feature branch*).

### Langkah Pembuatan Branch dan Kontribusi

1. **Sinkronisasi branch `main` lokal:**
   Sebelum membuat branch baru, pastikan branch `main` lokal Anda selalu sinkron dengan repository pusat di GitHub:
   ```bash
   git switch main
   git pull origin main
   ```
2. **Buat branch fitur baru:**
   ```bash
   git switch -c feature/nama-fitur
   ```
3. **Kembangkan dan uji kode secara lokal:**
   Lakukan perubahan, pastikan aset berhasil dikompilasi (`npm run build`), dan pastikan tes tidak mengalami *error* (`php artisan test`).
4. **Lakukan commit secara bertahap dengan pesan yang deskriptif:**
   Gunakan pesan commit yang jelas dalam Bahasa Indonesia atau Inggris, misalnya:
   ```bash
   git add .
   git commit -m "feat: implementasi halaman keranjang belanja multi-tenant"
   ```
5. **Kirim branch ke GitHub:**
   ```bash
   git push -u origin feature/nama-fitur
   ```
6. **Buka Pull Request (PR):**
   - Buat PR dari branch fitur Anda menuju branch `main` di GitHub.
   - Cantumkan deskripsi perubahan yang jelas dan modul yang terdampak.
   - Minta minimal satu anggota kelompok lainnya untuk melakukan penelaahan kode (*code review*) sebelum digabungkan (*merge*).

### Standar Penamaan Branch

Gunakan konvensi penamaan branch yang konsisten:

- `feature/buyer` : Modul pemesanan pembeli, keranjang multi-tenant, dan katalog menu.
- `feature/tenant` : Modul dashboard tenant, manajemen menu, stok, dan konfirmasi pesanan.
- `feature/pengelola` : Modul dashboard admin pengelola, analitik, dan manajemen QR meja.
- `feature/order-management` : Modul mekanisme master order dan pemisahan sub-order.
- `feature/payment` : Integrasi payment gateway sandbox (QRIS) dan konfirmasi tunai.
- `fix/nama-perbaikan` : Perbaikan bug atau kendala teknis spesifik.

---

## Keamanan Repository

Seluruh anggota tim wajib mematuhi protokol keamanan berikut:

- **Jangan pernah melakukan commit terhadap file `.env`** atau file kredensial lokal lainnya. File `.env` telah tercatat di `.gitignore` untuk mencegah kebocoran data.
- **Dilarang mencantumkan *hardcoded credentials*** (seperti kata sandi basis data lokal, *server key* payment gateway, atau token rahasia) ke dalam source code. Gunakan fungsi `env()` Laravel.
- Gunakan file `.env.example` sebagai referensi tunggal nama variabel lingkungan yang dibutuhkan oleh sistem. Jika Anda menambahkan variabel baru, perbarui `.env.example` tanpa menyertakan nilai sensitif.
- Seluruh pengujian pembayaran wajib menggunakan kredensial dan lingkungan **Sandbox**, bukan data transaksi riil.

---

## Status Pengembangan

| Status | Tahap |
|---|---|
| **In Development — Initial Setup (Sprint 0 / Fondasi Proyek)** | Persiapan repositori, standarisasi lingkungan Laravel 12, dan penyusunan modul arsitektur. |

> [!WARNING]
> Seluruh fitur fungsional KuponGo yang tercantum dalam dokumen ini merupakan spesifikasi kebutuhan hasil analisis proposal Capstone dan **belum diklaim selesai diimplementasikan**. Repositori saat ini berada pada tahap inisialisasi fondasi sistem agar masing-masing anggota kelompok dapat memulai pengembangan modul secara paralel dan terstruktur.

---

**Capstone Design Project — Kelompok 38**  
Program Studi Sistem Informasi  
Telkom University Jakarta — 2026
