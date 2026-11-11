# KuponGo

**KuponGo** adalah sistem pemesanan makanan dan minuman berbasis web yang dikembangkan untuk Sentra Kuliner D'Kupon. Sistem ini dirancang untuk mengintegrasikan pemesanan dari beberapa tenant melalui satu platform, dengan dukungan QR Code meja, pembayaran digital, pengelolaan pesanan, serta dashboard analitik penjualan.

Proyek ini merupakan bagian dari **Capstone Design Project Kelompok 38 – Program Studi Sistem Informasi, Telkom University Jakarta**.

## Fitur yang Direncanakan

Pengembangan KuponGo mencakup tiga peran utama dengan fungsi sebagai berikut.

### 1. Pembeli
- Mengakses halaman pemesanan melalui QR Code meja tanpa login.
- Melihat daftar tenant dan menu yang tersedia.
- Memilih menu dari beberapa tenant dalam satu keranjang.
- Mengatur jumlah pesanan dan menambahkan catatan khusus.
- Melakukan checkout dengan metode pembayaran QRIS atau tunai.
- Memantau status pengerjaan pesanan.

### 2. Tenant
- Login menggunakan akun tenant yang terdaftar.
- Mengelola data menu, harga, dan ketersediaan menu.
- Mengatur status operasional tenant.
- Menerima sub-pesanan sesuai tenant masing-masing.
- Mengonfirmasi pembayaran tunai yang diterima.
- Memperbarui status pengerjaan pesanan.
- Melihat riwayat transaksi dan ringkasan penjualan.

### 3. Pengelola
- Login menggunakan akun pengelola.
- Mengelola data dan akun tenant.
- Mengelola data meja dan menghasilkan QR Code.
- Memantau transaksi seluruh tenant.
- Melihat dashboard analitik penjualan, termasuk omzet, menu terlaris, dan tren penjualan.

## Mekanisme Pemesanan dan Pembayaran

KuponGo menggunakan konsep **single cart multi-tenant**, yaitu pembeli dapat memesan menu dari beberapa tenant dalam satu transaksi.

Setiap checkout menghasilkan satu *master order* yang memuat rincian sub-pesanan berdasarkan tenant. Sistem mengidentifikasi setiap sub-pesanan menggunakan kode tenant agar informasi pesanan dan transaksi tercatat secara terpisah.

Metode pembayaran yang direncanakan meliputi:

- **QRIS:** Pembeli melakukan satu pembayaran melalui Payment Gateway. Sistem menerima konfirmasi pembayaran dan mencatat alokasi dana berdasarkan kode tenant. Mekanisme penyaluran dana otomatis bergantung pada dukungan fitur pembagian dana dari penyedia Payment Gateway.
- **Tunai:** Pembeli melakukan pembayaran langsung kepada tenant terkait. Setiap tenant mengonfirmasi pembayaran pada sub-pesanan miliknya melalui sistem.

Integrasi pembayaran digital direncanakan menggunakan lingkungan **sandbox** untuk kebutuhan pengembangan dan pengujian.

## Teknologi Pengembangan

| Komponen | Teknologi |
|---|---|
| Backend | PHP, Laravel |
| Arsitektur | Model-View-Controller (MVC) |
| Database | MySQL |
| Frontend | Web responsif |
| Payment Integration | Payment Gateway (Sandbox) |
| Version Control | Git & GitHub |
| Development IDE | Google Antigravity (opsional bagi anggota) |
| Testing | Black Box Testing & User Acceptance Testing (UAT) |

Framework atau library frontend tambahan akan ditetapkan ketika struktur awal proyek Laravel disiapkan.

## Metode Pengembangan

KuponGo dikembangkan menggunakan metode **Prototype** melalui tahapan analisis kebutuhan, perancangan sistem, pembuatan prototype, evaluasi pengguna, penyempurnaan, implementasi, serta pengujian sistem.

Rancangan sistem mengacu pada kebutuhan fungsional, kebutuhan non-fungsional, UML, ERD, dan desain antarmuka yang telah disusun dalam proposal Capstone.

## Memulai Pengembangan

Repository ini digunakan sebagai pusat pengembangan dan kolaborasi source code KuponGo.

### Clone Repository

Pastikan Git sudah terpasang dan akun GitHub memiliki akses ke repository.

```bash
git clone https://github.com/OWNER/kupongo.git
cd kupongo
```

Ganti `OWNER` dengan username atau nama organisasi GitHub pemilik repository.

### Konfigurasi Lokal

Proyek sedang berada pada tahap persiapan implementasi. Panduan instalasi Laravel, konfigurasi MySQL, dan perintah menjalankan aplikasi akan dilengkapi setelah struktur awal source code tersedia.

## Panduan Kontribusi

Seluruh anggota kelompok menggunakan repository yang sama dan mengikuti alur kerja Git berikut:

1. Mengambil perubahan terbaru dari branch `main`.
2. Membuat branch baru sesuai fitur yang dikerjakan.
3. Mengembangkan dan menguji perubahan pada branch masing-masing.
4. Melakukan commit dengan pesan yang menjelaskan perubahan.
5. Membuat Pull Request untuk menggabungkan perubahan ke branch `main`.
6. Melakukan review sebelum Pull Request digabungkan.

Contoh penamaan branch:

- `feature/buyer`
- `feature/tenant`
- `feature/pengelola`
- `feature/payment`
- `fix/nama-perbaikan`

Hindari melakukan perubahan langsung pada branch `main` selama pengembangan fitur berlangsung.

## Keamanan Repository

- Jangan mengunggah file `.env` atau kredensial pribadi.
- Jangan menyimpan API key, password database, atau token Payment Gateway di source code.
- Gunakan `.env.example` sebagai referensi konfigurasi lingkungan pengembangan.
- Pastikan perubahan database dibagikan melalui Laravel Migration.
- Gunakan data pengujian untuk transaksi dan integrasi Payment Gateway.

## Status Proyek

**In Development — Initial Setup**

Repository sedang dipersiapkan untuk implementasi sistem KuponGo. Fitur-fitur yang dijelaskan dalam dokumentasi ini merupakan ruang lingkup pengembangan dan belum dinyatakan selesai.

---

**Capstone Design Project — Kelompok 38**  
Program Studi Sistem Informasi  
Telkom University Jakarta — 2026
