# KuponGo

**KuponGo** adalah sistem pemesanan makanan dan minuman berbasis web yang dirancang untuk Sentra Kuliner D'Kupon. Sistem ini mengintegrasikan pemesanan dari beberapa tenant melalui satu keranjang, identifikasi meja menggunakan QR Code, pembayaran, pengelolaan pesanan, pencatatan transaksi, serta dashboard analitik penjualan.

Proyek ini dikembangkan oleh **Kelompok 38 – Program Studi Sistem Informasi, Telkom University Jakarta** sebagai bagian dari Capstone Design Project.

## Fitur yang Direncanakan

Pengembangan sistem mengacu pada Functional Requirement (FR), Non-Functional Requirement (NFR), Use Case Diagram, dan rancangan sistem dalam proposal Capstone.

### 1. Pembeli

- Memindai QR Code meja untuk mengidentifikasi nomor meja pemesanan. (FR-01)
- Melihat daftar tenant dan menu beserta harga serta ketersediaannya. (FR-02, FR-03)
- Menyusun pesanan dari beberapa tenant dalam satu keranjang, mengatur jumlah menu, dan menambahkan catatan khusus. (FR-04, FR-05, FR-06)
- Melihat rincian pesanan dan total pembayaran berdasarkan tenant. (FR-07)
- Memilih pembayaran QRIS atau tunai dan menyelesaikan proses checkout. (FR-09–FR-13)
- Melihat status pengerjaan pesanan. (FR-08)
- Mendapatkan pembatasan pemesanan pada tenant yang berstatus tutup. (FR-30)

### 2. Tenant

- Login menggunakan akun tenant yang terdaftar. (FR-21)
- Mengubah status operasional tenant menjadi buka atau tutup. (FR-22)
- Mengelola data menu, harga, serta status ketersediaan menu. (FR-23)
- Mengonfirmasi pembayaran tunai untuk sub-pesanan miliknya. (FR-14)
- Melihat sub-pesanan yang telah dibayar dan diteruskan ke tenant terkait. (FR-18)
- Memperbarui status pengerjaan pesanan. (FR-19)
- Menerima pemberitahuan pesanan baru sebagai fitur pengembangan sesuai prioritas kebutuhan. (FR-20)
- Melihat riwayat transaksi dan ringkasan penjualan tenant. (FR-26, FR-27)

### 3. Pengelola

- Login menggunakan akun pengelola yang terdaftar. (FR-21)
- Mengelola data dan akun tenant. (FR-24)
- Mengelola data meja, menghasilkan, serta mengunduh QR Code meja. (FR-29)
- Melihat dashboard analitik penjualan seluruh tenant. (FR-28)

### 4. Pengelolaan Pesanan dan Pembayaran

- Menghasilkan satu kode pesanan utama (*master order*) untuk setiap checkout. (FR-12)
- Memisahkan pesanan menjadi sub-pesanan berdasarkan tenant. (FR-15)
- Mencatat status pembayaran setiap sub-pesanan secara terpisah. (FR-16)
- Memperbarui status pembayaran master order berdasarkan status seluruh sub-pesanan. (FR-17)
- Meneruskan sub-pesanan yang telah dibayar kepada tenant terkait. (FR-18)
- Mencatat transaksi penjualan berdasarkan tenant. (FR-25)
- Memproses satu pembayaran QRIS untuk pesanan gabungan melalui layanan pembayaran yang diuji pada lingkungan sandbox. (FR-10, FR-11)

**Rencana integrasi Payment Gateway:** Sistem akan menghitung alokasi pembayaran berdasarkan kode tenant. Integrasi pembagian dana otomatis masih memerlukan pemilihan penyedia Payment Gateway yang mendukung mekanisme tersebut dan akan diverifikasi dalam lingkungan pengujian.

Untuk pembayaran tunai, pembeli melakukan pembayaran langsung kepada tenant terkait. Setiap tenant mengonfirmasi pembayaran sub-pesanan miliknya melalui sistem.

## Kebutuhan Non-Fungsional

Implementasi sistem mengacu pada kebutuhan non-fungsional dalam proposal, dengan fokus pada:

- **Aksesibilitas:** Website dapat diakses melalui browser perangkat mobile maupun desktop. (NFR-01, NFR-02)
- **Keandalan:** Konsistensi data master order dan sub-order, pencegahan pencatatan pembayaran ganda, serta penyimpanan transaksi yang tetap terjaga saat halaman dimuat ulang. (NFR-03–NFR-06)
- **Kinerja:** Pemisahan pesanan kurang dari dua detik pada kondisi pengujian yang ditetapkan dan pembaruan informasi dashboard paling lambat lima menit setelah transaksi tercatat. (NFR-07, NFR-08)
- **Keamanan:** Penyimpanan kata sandi menggunakan hash dan pembatasan hak akses berdasarkan peran pengguna. (NFR-09, NFR-10)
- **Antarmuka:** Bahasa Indonesia, desain responsif, tampilan konsisten, serta konfirmasi sebelum tindakan penonaktifan tertentu. (NFR-11–NFR-15)

## Teknologi Pengembangan

| Komponen | Teknologi |
|---|---|
| Backend | PHP, Laravel 12 |
| Arsitektur | Model-View-Controller (MVC) |
| Database | MySQL |
| Frontend | Blade, Tailwind CSS, Alpine.js |
| Build Tool | Vite |
| Run Time | PHP 8.5.12, Node.js 24.22.3 |
| Depedency Manager | Composer 2.11.2, npm |
| Payment Integration | Payment Gateway (Sandbox) |
| Version Control | Git & GitHub |
| Development Tools | Google Antigravity IDE |
| Pengujian | Black Box Testing & User Acceptance Testing (UAT) |

Framework atau library frontend tambahan akan ditetapkan pada tahap persiapan implementasi.

## Metode Pengembangan

KuponGo menggunakan metode **Prototype** dengan tahapan analisis kebutuhan, perancangan sistem, pembuatan prototype, evaluasi pengguna, penyempurnaan, implementasi, dan pengujian.

Perancangan sistem didokumentasikan melalui Use Case Diagram, Use Case Description, Activity Diagram, Class Diagram, Entity Relationship Diagram (ERD), dan Sequence Diagram.

## Memulai Pengembangan

Repository ini digunakan sebagai pusat kolaborasi source code KuponGo.

### Clone Repository

Pastikan Git sudah terpasang dan akun GitHub memiliki akses ke repository.

```bash
git clone https://github.com/OWNER/kupongo.git
cd kupongo
```

Ganti `OWNER` dengan username atau nama organisasi GitHub pemilik repository.

### Konfigurasi Lokal

Panduan instalasi Laravel, konfigurasi MySQL, dan perintah menjalankan aplikasi akan ditambahkan setelah struktur awal proyek tersedia.

## Panduan Kontribusi

Pengembangan dilakukan melalui branch terpisah berdasarkan fitur yang dikerjakan.

1. Mengambil perubahan terbaru dari branch `main`.
2. Membuat branch baru sesuai fitur.
3. Mengembangkan dan menguji perubahan.
4. Melakukan commit dengan pesan yang jelas.
5. Membuat Pull Request untuk menggabungkan perubahan.
6. Melakukan pemeriksaan kode sebelum perubahan digabungkan ke `main`.

Contoh penamaan branch:

- `feature/buyer`
- `feature/tenant`
- `feature/pengelola`
- `feature/payment`
- `fix/nama-perbaikan`

Selama pengembangan fitur, hindari melakukan perubahan langsung pada branch `main`.

## Keamanan Repository

- Jangan mengunggah file `.env` atau data kredensial.
- Jangan menyimpan password, API key, maupun token Payment Gateway di source code.
- Gunakan `.env.example` sebagai contoh konfigurasi lingkungan.
- Bagikan perubahan struktur database melalui Laravel Migration.
- Gunakan data pengujian dan lingkungan sandbox untuk integrasi pembayaran.

## Status Pengembangan

**In Development — Initial Setup**

Repository sedang dipersiapkan untuk pengembangan sistem KuponGo. Seluruh fitur dalam dokumentasi ini merupakan ruang lingkup yang direncanakan dan belum dinyatakan selesai diimplementasikan.

---

**Capstone Design Project — Kelompok 38**  
Program Studi Sistem Informasi  
Telkom University Jakarta — 2026
