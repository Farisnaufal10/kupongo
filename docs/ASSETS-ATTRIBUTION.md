# Dokumentasi Aset Visual — Modul Pembeli KuponGo

Dokumen ini mencatat daftar aset gambar, ikon, logo, sumber, lisensi, dan atribusi yang digunakan pada antarmuka mobile Modul Pembeli KuponGo.

---

## 1. Identitas Brand & Logo
- **Tipe**: Wordmark Text `KuponGo`
- **Tipografi**: `Plus Jakarta Sans` (Google Fonts, Open Font License / OFL)
- **Aksen Warna**: Forest Green (`#103B2C`), Champagne Gold (`#C5A46D`), Charcoal (`#202923`), Warm Ivory (`#F8F7F2`)
- **Ketentuan**: Tanpa simbol, ilustrasi, atau ornamen tambahan pada wordmark utama sesuai spesifikasi proyek.

---

## 2. Ikonografi
- **Sumber**: Lucide Icons / SVG Outlined Icons
- **Lisensi**: ISC License (bebas digunakan untuk keperluan komersial dan non-komersial)
- **Spesifikasi**:
  - Stroke width: 2px / 2.5px
  - Ukuran standar: 16px (kecil), 20–24px (navigasi & aksi)
  - Target sentuh minimal: 44 × 44 px (nyaman untuk jempol smartphone segala usia)
  - Tanpa emoji sebagai representasi status atau kontrol utama.

---

## 3. Foto Makanan (Food Photography)
Seluruh aset visual disimpan secara lokal di `public/images/pembeli/` untuk efisiensi akses dan tidak membebani kuota pengguna mobile:

| Nama File | Ukuran | Penempatan & Fungsi | Sumber / Fotografer | Lisensi |
|---|---|---|---|---|
| `hero-banner.jpg` | ~172 KB | Visual utama Hero Banner ("Satu Meja, Banyak Pilihan.") — Foto meja kuliner penuh hidangan beragam | [Unsplash - Photo by Lily Banse](https://images.unsplash.com/photo-1504674900247-0877df9cc836) | Unsplash License (Free to use, non-exclusive) |
| `menu-default.jpg` | ~70 KB | Placeholder foto menu makanan pada food card | [Unsplash - Photo by Eiliv Aceron](https://images.unsplash.com/photo-1546069901-ba9599a7e63c) | Unsplash License (Free to use, non-exclusive) |
| `tenant-banner.jpg` | ~147 KB | Cover card tenant Sentra Kuliner D'Kupon | [Unsplash - Photo by Jay Wennington](https://images.unsplash.com/photo-1555396273-367ea4eb4db5) | Unsplash License (Free to use, non-exclusive) |
| `banner-multi.jpg` | ~50 KB | Banner highlight sekunder ("Multi-Tenant KuponGo") | [Unsplash - Photo by American Heritage Chocolate](https://images.unsplash.com/photo-1563729784474-d77dbb933a9e) | Unsplash License (Free to use, non-exclusive) |

---

## 4. Ketentuan Teknis & Fallback
- Jika foto menu dari database kosong/null, sistem menampilkan `menu-default.jpg` sebagai fallback visual.
- Tidak ada modifikasi struktur database atau penambahan kolom hanya untuk kebutuhan desain visual.
- Gambar telah dioptimalkan secara lokal dengan kompresi web untuk akses responsif di smartphone (360–430px).
