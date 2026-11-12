KUPONGO — DESIGN SYSTEM v1.0
Panduan visual dan implementasi untuk Kelompok 38
Status: Usulan standar bersama — siap disepakati tim
Proyek: KuponGo | Laravel 12 · Blade · Tailwind CSS 4 · MariaDB
Arah desain: Modern Culinary — warm, trustworthy, mobile-first
Cara memakai dokumen ini: Salin file Markdown ini ke docs/DESIGN-SYSTEM-KUPONGO.md di repository setelah disepakati tim. Setiap anggota cukup menambahkan instruksi “Baca dan ikuti docs/DESIGN-SYSTEM-KUPONGO.md” pada prompt Antigravity modulnya. Dokumen ini hanya mengatur tampilan dan interaksi UI; tidak mengubah lingkup jobdesk atau kontrak database.

1. Keputusan desain dalam satu menit
Elemen	Standar KuponGo
Identitas	Modern Culinary: modern, hangat, bersih, tidak berlebihan
Warna utama	Forest Green #103B2C
Warna aksen	Champagne Gold #C5A46D
Latar aplikasi	Warm Ivory #F8F7F2
Permukaan kartu	White #FFFFFF
Teks utama	Charcoal #202923
Font	Plus Jakarta Sans / Inter / system sans-serif (fallback)
Kerangka layout	Mobile-first, maksimal konten pembeli ± 480 px pada layar besar
Dasar spacing	Kelipatan 4 px; jarak umum 8/12/16/24/32 px
Sudut komponen	Card 16 px, tombol/input 12 px
Aksesibilitas	Teks kontras, navigasi keyboard, target sentuh minimal 44 × 44 px


Prinsip utama: KuponGo merupakan aplikasi pemesanan multi-tenant, bukan website satu restoran. Pengguna harus mudah melihat tenant, memilih menu, mengelola keranjang gabungan, dan mengecek status pesanan. Desain referensi hanya inspirasi suasana visual, bukan daftar fitur yang wajib ditiru.
2. Prinsip pengalaman pengguna
1. Tugas utama lebih dulu. Alur pembeli diprioritaskan: konteks meja → tenant → menu → keranjang → checkout → status.
2. Jelas sebelum indah. Status buka/tutup, habis/tersedia, dan status pembayaran harus terbaca tanpa hanya bergantung pada warna.
3. Satu identitas, tiga pengalaman. Pembeli boleh memakai navigasi bawah; dashboard Tenant/Pengelola boleh memakai sidebar. Token warna dan bentuk komponen tetap konsisten.
4. Mobile-first. Optimalkan layar ponsel dan perangkat sentuh sebelum desktop.
5. Tidak membuat fitur fiktif. Jangan menambahkan reservasi meja, ulasan/rating, diskon, kategori, promo, atau fitur akun pembeli jika tidak didukung kebutuhan dan data.
6. Menggunakan data sebenarnya. Ketika data kosong, tampilkan empty state. Gunakan data tiruan hanya di komponen demonstrasi/test, bukan mengisi database produksi secara diam-diam.
3. Design tokens: warna
Token	Hex	Penggunaan
brand.forest	#103B2C	Header tertentu, tombol utama, navigasi aktif, judul identitas
brand.forestHover	#0B2B20	Hover/pressed tombol utama
brand.gold	#C5A46D	Garis aksen, ornamen kecil, highlight lembut
brand.ivory	#F8F7F2	Latar halaman
surface.default	#FFFFFF	Kartu, form, dialog, panel
text.primary	#202923	Judul, isi utama
text.secondary	#67736B	Keterangan, metadata, caption
border.default	#E2E5DE	Outline kartu, divider, input
state.success	#15803D	Status berhasil, tenant buka
state.warning	#A16207	Perlu perhatian, menunggu jika relevan
state.danger	#B91C1C	Error, gagal, status tutup atau dibatalkan
state.info	#1D4ED8	Informasi netral yang membutuhkan penekanan


Aturan kontras: Gunakan putih pada tombol Forest Green, bukan pada tombol Gold. Gunakan teks Forest Green/Charcoal di atas Gold. Sebagai acuan, putih–Forest Green memiliki rasio sekitar 12,47:1; Forest Green–Gold sekitar 5,29:1; putih–Gold hanya 2,36:1 dan tidak cukup untuk teks normal. Pastikan kontras teks biasa minimal 4,5:1 dan teks besar minimal 3:1 (WCAG AA), serta cek lagi ketika warna diubah.

Pemakaian praktis: 70% latar netral, 25% Forest Green/teks gelap, dan 5% Gold sebagai aksen adalah arah komposisi visual, bukan aturan CSS yang harus dihitung secara literal. Jangan menjadikan semua tombol berwarna emas.
4. Typography, spacing, dan bentuk
Elemen	Spesifikasi awal
Font antarmuka	"Plus Jakarta Sans", "Inter", ui-sans-serif, system-ui, sans-serif
Page title	24 px / 700 / line-height ± 1.3
Section title	18–20 px / 600–700
Card title	16 px / 600
Body	14–16 px / 400–500 / line-height ± 1.5
Helper/caption	12–13 px / 400–500
Label tombol	14–16 px / 600
Spacing scale	4, 8, 12, 16, 20, 24, 32, 40, 48 px
Card radius	16 px; modal 20 px
Button/input radius	12 px
Border	1 px #E2E5DE
Shadow	Ringan, untuk elevasi; jangan mengandalkan shadow sebagai batas tunggal
Minimum touch target	44 × 44 px


Hindari terlalu banyak jenis font. Jika font kustom belum dimuat dalam proyek, gunakan fallback sistem agar UI tetap konsisten dan cepat. Untuk nominal rupiah, gunakan pemformatan Indonesia seperti Rp 25.000; angka pada tabel laporan sebaiknya rata kanan.
5. Komponen bersama
5.1 Tombol
- Primary: latar brand.forest, teks putih; untuk tindakan utama seperti Lihat Menu, Lanjut ke Keranjang, atau Konfirmasi sesuai hak akses.
- Secondary: permukaan putih, outline Forest Green, teks Forest Green; untuk tindakan sekunder.
- Tertiary/text: tanpa latar dominan, untuk Kembali atau Lihat detail.
- Destructive: danger; hanya untuk tindakan yang benar-benar merusak/menghapus.
- Disabled: redup + tidak bisa diklik; jangan hanya mengubah warna, tambahkan atribut disabled.
- Semua tombol wajib memiliki hover, focus-visible, loading, dan disabled state ketika relevan.
5.2 Input dan pencarian
- Label eksplisit; placeholder bukan pengganti label.
- Input 44–48 px tinggi, border border.default, radius 12 px.
- Error ditulis di bawah field dengan pesan spesifik, tidak hanya border merah.
- Pencarian/filter hanya ditampilkan ketika memang tersedia dalam cakupan modul dan data mendukung.
5.3 Tenant card
- Menampilkan nama tenant, status buka/tutup, informasi pelengkap yang benar-benar tersedia, serta aksi Lihat Menu.
- Aksi pemesanan tidak tersedia jika tenant tutup. Status Tutup juga harus tertulis jelas.
- Jika foto/logo tenant tidak tersedia, gunakan placeholder ikon konsisten, bukan foto restoran acak.
5.4 Menu card
- Menampilkan nama, harga Rp, ketersediaan, dan gambar hanya jika sumber datanya tersedia.
- Tombol + Tambah atau Pesan dinonaktifkan bila habis atau tenant tutup.
- Jangan menampilkan rating, badge bestseller, atau diskon tanpa data/requirement yang sah.
- Nama menu panjang boleh sampai dua baris; harga tetap mudah dibaca.
5.5 Badge status
Badge selalu memakai warna + teks. Peta status final mengikuti docs/ERD-KuponGo.md. Contoh penggunaan:
Konteks	Teks yang terlihat	Warna semantik
Tenant operasional	Buka / Tutup	Success / Danger
Menu	Tersedia / Habis	Success / Danger
Pembayaran	Menunggu Pembayaran / Sudah Dibayar / Gagal / Kedaluwarsa	Warning / Success / Danger / Danger
Sub-order	Menunggu / Diproses / Siap Disajikan / Selesai / Dibatalkan	Neutral / Info / Warning / Success / Danger


Jangan membuat nilai status baru di database hanya karena menambahkan badge visual. Pemetaan teks dari status backend dilakukan pada lapisan presentasi.
5.6 Empty, loading, error, dan dialog
- Empty: ikon sederhana, judul spesifik, penjelasan singkat, aksi pemulihan jika tersedia. Contoh: “Belum ada tenant tersedia.”
- Loading: skeleton/spinner secukupnya; cegah klik ganda saat penyimpanan/pembayaran sedang diproses.
- Error: jelaskan apa yang gagal dan tindakan berikutnya, misalnya “Gagal memuat menu. Coba lagi.”
- Modal konfirmasi: judul jelas, efek tindakan dijelaskan, tombol Batal dan aksi utama; untuk penghapusan/konfirmasi penting.
- Notifikasi: tampilkan sukses/gagal secara ringkas dan dapat dibaca screen reader.
5.7 Navigasi
- Pembeli: header ringan dan navigasi yang membantu jalur Tenant/Menu → Keranjang → Status Pesanan. Bottom navigation boleh digunakan bila halaman-halamannya benar-benar tersedia, tidak menutup CTA atau konten.
- Tenant: navigasi dashboard/menu/pesanan/riwayat sesuai fitur yang sudah dibuat; akses selalu dibatasi tenant yang login.
- Pengelola: navigasi untuk tenant, meja/QR, dan dashboard sesuai hak akses.
- Jangan menggunakan ikon saja untuk navigasi penting: sertakan label teks.
6. Pola layout per modul
Modul	Prioritas tata letak	Catatan dependensi
Pembeli (Anggota 1)	Header brand + konteks meja, daftar tenant, katalog menu, keranjang, checkout, pelacakan	Pembeli tanpa login; QR meja menunggu format dari Anggota 3; checkout dan pembayaran nyata dari Anggota 4
Tenant (Anggota 2)	Dashboard ringkas, daftar sub-order yang bisa dibaca cepat, CRUD menu, status operasional	Login/role dari Anggota 3; data order dan verifikasi tunai melalui layanan Anggota 4
Pengelola & Auth (Anggota 3)	Halaman login sederhana, navigasi administrasi, tabel/filter, QR meja, statistik ringkas	Dashboard analitik mengambil data transaksi dari layanan yang tersedia; tidak membuat ulang pembayaran
Pemesanan & Pembayaran (Anggota 4)	Komponen status dan instruksi pembayaran yang jelas, feedback transaksi, tampilan gagal/ulang	Pemilik layanan order dan payment; UI pembeli utama tetap milik Anggota 1


Catatan khusus Modul Pembeli
Referensi desain restoran premium diterjemahkan menjadi food-court ordering experience; bukan reservasi restoran. Bila halaman dibuka langsung pada /menu tanpa konteks QR meja, jangan mengarang nomor meja. Pada pengembangan awal, tampilkan konteks meja hanya jika sudah tervalidasi. Perlakukan contoh tenant/menu, rating, promo, dan gambar makanan sebagai placeholder desain, bukan data asli.
7. Panduan implementasi Laravel + Tailwind CSS
Langkah aman untuk Antigravity:
1. Baca README.md, docs/ERD-KuponGo.md, struktur resources/css/, layouts Blade, dan pengaturan Vite/Tailwind aktual.
2. Gunakan design tokens yang sama di setiap modul, idealnya dideklarasikan sekali dalam stylesheet bersama setelah disepakati dan di-merge ke main.
3. Karena KuponGo menggunakan Tailwind CSS 4, pertimbangkan @theme pada stylesheet yang memang dipakai Vite. Contoh konseptual:
@theme {
  --color-brand-forest: #103B2C;
  --color-brand-forest-hover: #0B2B20;
  --color-brand-gold: #C5A46D;
  --color-brand-ivory: #F8F7F2;
  --color-ui-text: #202923;
  --color-ui-muted: #67736B;
  --color-ui-border: #E2E5DE;
}
4. Contoh nama utility sesudah token dikompilasi: bg-brand-forest, hover:bg-brand-forest-hover, bg-brand-ivory, text-ui-text, border-ui-border.
5. Jangan mengedit CSS global secara bersamaan pada empat branch. Jika tokens belum tersedia di main, pemilik perubahan mengajukan PR kecil dahulu; anggota lain dapat menyiapkan komponen masing-masing sembari menunggu.
6. Pakai Blade components hanya untuk pola yang benar-benar berulang; hindari melakukan refactor besar pada layout milik anggota lain.
7. Uji npm.cmd run build, php artisan test yang relevan, dan tampilan mobile/desktop sebelum PR. Di Windows gunakan perintah sesuai lingkungan masing-masing.
Peringatan: Snippet CSS adalah pedoman penerapan, bukan instruksi menimpa file stylesheet yang sudah ada. Periksa struktur nyata sebelum membuat perubahan global.
8. Aturan kolaborasi desain
- Satu sumber standar: berkas Markdown di docs/DESIGN-SYSTEM-KUPONGO.md setelah disetujui dan di-merge ke main.
- Bukan proyek desain terpisah: masing-masing anggota hanya menerapkan standar pada modul miliknya.
- Tidak mengambil alih jobdesk: Pembeli tidak mengimplementasikan service payment; Tenant tidak mengubah role/auth; Pengelola tidak membuat checkout; Order/Payment tidak membangun ulang halaman pembeli.
- Perubahan visual global: usulkan lewat PR kecil agar tidak memicu konflik empat branch.
- Pengujian sebelum merge: cek state normal, kosong, memuat, gagal, nonaktif; cek ukuran 360 px dan desktop; cek kontras dan klik keyboard.
- Jika ketentuan belum dapat dipenuhi: jelaskan alasannya pada PR. Jangan diam-diam membuat data atau fitur baru.
9. Prompt siap salin untuk Antigravity
Petunjuk: Setiap anggota menyalin prompt singkat berikut lalu menambahkan daftar fitur/jobdesk modulnya dari dokumen pembagian tugas. Jangan salin jobdesk anggota lain.
Prompt universal — untuk semua anggota
Saya mengembangkan KuponGo pada branch modul saya sendiri. Sebelum mulai,
baca README.md, docs/ERD-KuponGo.md, dan docs/DESIGN-SYSTEM-KUPONGO.md.
Ikuti visual Modern Culinary: Forest Green #103B2C, Champagne Gold #C5A46D,
Warm Ivory #F8F7F2, teks Charcoal #202923, komponen mobile-first.

Implementasikan HANYA fitur yang saya lampirkan pada bagian jobdesk modul saya.
Gunakan komponen dan status sesuai Design System, termasuk empty/loading/error
state dan aksesibilitas. Jangan membuat fitur yang tidak ada pada requirement,
jangan mengarang data database, dan jangan mengambil alih modul anggota lain.

Periksa file/route/model aktual sebelum membuat kode. Jika butuh perubahan
migration, model, CSS global, atau route bersama, jelaskan dahulu kebutuhan
tersebut; jangan ubah sepihak. Jika fitur bergantung pada anggota lain,
kerjakan bagian independen dan laporkan integrasi yang masih menunggu.
Jangan commit, push, atau merge otomatis.

Di akhir, laporkan file yang berubah, hasil pengujian, screenshot/route yang
perlu dicek, dan dependensi yang belum selesai.

JOBDESK MODUL SAYA:
[Tempel poin-poin lingkup kerja dan "Tidak menjadi tanggung jawab" di sini.]
Tambahan khusus Anggota 1 — Pembeli
Fokus saya adalah pengalaman Pembeli TANPA LOGIN: konteks QR meja tervalidasi,
katalog tenant/menu, keranjang multi-tenant, UI checkout, dan status pesanan.
Terapkan gaya mobile-first dengan card menu/tenant serta aksi utama yang jelas.
Jangan mengimplementasikan pembuatan master/sub-order maupun pembayaran backend;
keduanya milik Anggota 4. Jangan mengarang format QR meja milik Anggota 3.
Tambahan khusus Anggota 2 — Tenant
Fokus saya adalah operasional Tenant: dashboard, buka/tutup, CRUD menu,
sub-order milik sendiri, status pengerjaan, riwayat, dan rekap.
Gunakan layout dashboard efisien, badge status konsisten, serta form mudah dibaca.
Jangan membuat ulang login/role atau service pembayaran tunai.
Tambahan khusus Anggota 3 — Pengelola & Autentikasi
Fokus saya adalah login dan role bersama, CRUD tenant/meja, QR meja,
serta dashboard Pengelola. Gunakan tampilan administrasi konsisten dengan token
KuponGo tetapi prioritaskan keterbacaan tabel, form, dan ringkasan data.
Jangan membuat ulang checkout atau layanan pembayaran.
Tambahan khusus Anggota 4 — Pemesanan & Pembayaran
Fokus saya adalah master order, sub-order, detail transaksi, perhitungan final,
QRIS sandbox, pembayaran tunai, serta status dan keamanan transaksi.
Jika membuat UI terkait status/payment, ikuti warna dan pesan yang jelas.
Jangan membangun ulang halaman Pembeli, CRUD menu Tenant, atau dashboard Pengelola.
10. Checklist sebelum Pull Request
- [ ] Branch sesuai modul dan ruang lingkup jobdesk.
- [ ] Forest Green, Gold, Ivory, tipografi, spacing, serta radius konsisten.
- [ ] Teks pada tombol/status memiliki kontras memadai; Gold bukan latar teks putih.
- [ ] Layout diuji pada lebar layar sekitar 360 px dan desktop.
- [ ] Navigasi dan tombol penting berlabel; target sentuh minimal 44 × 44 px.
- [ ] Tersedia empty/loading/error/disabled state sesuai kebutuhan halaman.
- [ ] Status dibaca dari data aktual, tidak hanya ditampilkan sebagai warna.
- [ ] Tidak menambahkan reservasi, rating, promo, atau data contoh sebagai fitur produksi tanpa requirement.
- [ ] Tidak mengubah file bersama atau modul anggota lain tanpa koordinasi.
- [ ] Build/test terkait lulus; ringkasan hasil dilampirkan pada PR.
Catatan versi: v1.0 adalah baseline desain yang diusulkan berdasarkan referensi visual dan kebutuhan Capstone. Tim dapat menyetujui atau merevisi lewat satu perubahan dokumentasi di main. Penetapan Design System tidak mengubah struktur database, role, status transaksi, atau tanggung jawab fitur yang sudah dibagi.