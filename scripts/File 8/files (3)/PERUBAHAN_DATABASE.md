# PENJELASAN PERUBAHAN DATABASE & CARA MIGRASI
# ================================================

## Cara Migrasi

Jalankan perintah berikut dari root project:

```bash
php artisan migrate
```

Hanya ada 1 file migration baru. Aman dijalankan karena semua
perubahan pakai `if (!Schema::hasColumn(...))` sehingga tidak error
jika sudah ada.

---

## Perubahan Per Tabel

### 1. surat_categories (TAMBAH kolom)

| Kolom Baru   | Tipe         | Fungsi |
|---|---|---|
| deskripsi    | text, null   | Deskripsi kategori |
| icon         | string(50)   | Nama icon Lucide, contoh: "mail", "file-text" |
| warna        | string(20)   | Warna Tailwind, contoh: "indigo", "emerald" |
| urutan       | smallint     | Urutan tampil di UI (drag-sort nanti) |
| is_active    | boolean      | Aktif/nonaktif kategori |

→ Tidak ada kolom yang dihapus. Aman.

---

### 2. jenis_surats (TAMBAH kolom)

| Kolom Baru   | Tipe                      | Fungsi |
|---|---|---|
| kode_surat   | string(50), null          | Kode untuk nomor surat otomatis, contoh: UNDANGAN-PKL |
| qr_mode      | enum(immediate/after_approval) | Kapan QR aktif |
| allowed_roles | json, null               | Array slug role yang boleh buat surat ini |
| urutan       | smallint                  | Urutan di UI |

Contoh isi `allowed_roles`:
```json
["admin", "sekretaris-fakultas"]
```

Contoh `qr_mode`:
- `immediate` → surat undangan, edaran, pengumuman (QR langsung aktif)
- `after_approval` → surat pengajuan mahasiswa (QR aktif setelah disetujui)

---

### 3. surat_templates (TAMBAH kolom)

| Kolom Baru   | Tipe       | Fungsi |
|---|---|---|
| docx_path    | string     | Path file .docx yang diupload admin |
| kop_surat    | text       | HTML kop surat override (jika berbeda dari global) |
| footer_surat | text       | HTML footer override |
| css_style    | text       | CSS kustom untuk template ini |
| deskripsi    | text       | Deskripsi template |
| created_by   | FK users   | Siapa yang buat |
| updated_by   | FK users   | Siapa yang update terakhir |

---

### 4. surats (TAMBAH kolom)

| Kolom Baru          | Tipe        | Fungsi |
|---|---|---|
| kepada_yth          | json, null  | Array penerima surat (bisa multiple) |
| lampiran_keterangan | string      | Keterangan lampiran, "1 (satu) lembar" |
| revisi_ke           | tinyint     | Berapa kali direvisi (default 0) |
| catatan_revisi      | text        | Catatan revisi terakhir |
| print_count         | smallint    | Berapa kali dicetak |
| last_printed_at     | timestamp   | Kapan terakhir dicetak |

Contoh isi `kepada_yth`:
```json
[
    "Dekan Fakultas Matematika dan Ilmu Komputer",
    "Ketua Program Studi Informatika",
    "Bapak Dr. Ahmad Fauzi, M.Kom."
]
```

---

### 5. surat_qr_codes (TABEL BARU)

Tabel ini mengelola QR code secara terpisah dari tabel surats.
Memungkinkan fitur revoke QR tanpa menghapus surat.

| Kolom        | Tipe                          | Fungsi |
|---|---|---|
| id           | bigint PK                     | |
| surat_id     | FK surats                     | Surat terkait |
| token        | string(64) unique             | UUID token QR |
| status       | enum(active/revoked/expired)  | Status QR |
| revoked_reason | string, null               | Alasan dicabut |
| revoked_by   | FK users, null                | Admin yang mencabut |
| revoked_at   | timestamp, null               | Kapan dicabut |
| activated_at | timestamp, null               | Kapan QR mulai aktif |

Index: `(token, status)` dan `surat_id`

---

### 6. surat_histories (TABEL BARU)

Audit trail semua aksi pada surat. Berguna untuk:
- Halaman riwayat admin
- Debug jika ada masalah
- Bukti hukum aktivitas dokumen

| Kolom       | Tipe      | Fungsi |
|---|---|---|
| id          | bigint PK | |
| surat_id    | FK surats | |
| user_id     | FK users  | Siapa melakukan aksi |
| action      | string(50) | Kode aksi: created, submitted, validated, approved, rejected, generated, printed, qr_scanned, qr_revoked, revised |
| action_label | string   | Label ramah: "Surat dibuat", "Surat disetujui Kaprodi" |
| keterangan  | text      | Detail tambahan |
| meta        | json      | Data tambahan, contoh: {"status_lama":"pending","status_baru":"validated_admin"} |
| ip_address  | string(45) | IP address yang melakukan aksi |

Index: `(surat_id, created_at)` dan `user_id`

---

### 7. template_global_settings (TABEL BARU)

Pengaturan global yang berlaku untuk semua template.
Admin bisa ubah via UI tanpa edit kode.

| Key             | Label               | Default |
|---|---|---|
| kop_html        | Kop Surat (HTML)    | null (pakai template docx) |
| footer_html     | Footer Surat (HTML) | null |
| logo_path       | Logo Universitas    | null |
| nama_instansi   | Nama Instansi       | "Universitas NU Al Ghazali Cilacap" |
| nama_fakultas   | Nama Fakultas       | "Fakultas Matematika dan Ilmu Komputer" |
| alamat          | Alamat              | "Jl. Kemerdekaan Barat No. 17..." |
| telepon         | Telepon             | null |
| website         | Website             | null |
| email           | Email               | null |
| warna_primer    | Warna Primer Kop    | "#1B5E20" |
| format_nomor    | Format Nomor Surat  | "{kode}/{urutan}/{bulan_romawi}/{tahun}" |
| kota_surat      | Kota Default        | "Cilacap" |

---

### 8. nomor_surat_sequences (TABEL BARU)

Mengelola urutan nomor surat per kode per bulan/tahun.
Menggantikan `$surat->id` sebagai dasar nomor surat.

| Kolom     | Tipe        | Fungsi |
|---|---|---|
| kode_surat | string(50) | Kode jenis surat |
| tahun     | smallint    | Tahun surat |
| bulan     | smallint    | Bulan surat |
| urutan    | int         | Nomor urut saat ini |

Unique: `(kode_surat, tahun, bulan)`

Contoh generate nomor surat otomatis:
```
Format: {kode}/{urutan_4digit}/{bulan_romawi}/{tahun}
Hasil:  CUTI-MHS/0042/IV/2026
```

---

## Urutan Implementasi yang Disarankan

1. Jalankan migration: `php artisan migrate`
2. Copy semua file Vue baru ke project
3. Update DashboardController untuk kirim `recentHistory`
4. Update TemplateController untuk handle `globalSettings`
5. Buat `SuratHistoryService` untuk record semua aksi
6. Buat `NomorSuratService` untuk generate nomor otomatis
7. Update `SuratDocumentGeneratorService` untuk pakai `surat_qr_codes`
8. Test semua flow
