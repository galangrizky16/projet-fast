# IMPLEMENTATION GUIDE — FAST Academic Restructure
# ================================================
# Baca ini sampai habis sebelum mulai. Estimasi waktu: 30–45 menit.

## PRASYARAT

Sebelum mulai, pastikan:
- [ ] Project berjalan normal (tidak ada error)
- [ ] Sudah commit atau backup project (git commit -m "backup before restructure")
- [ ] Terminal terbuka di root project

---

## TAHAP 1 — Dry Run (preview tanpa mengubah file)

Letakkan file `restructure.php` di folder `scripts/` di root project Anda,
kemudian jalankan:

```bash
php scripts/restructure.php --dry-run
```

Baca output. Setiap baris `→` adalah file yang akan dipindah.
Setiap baris `~` adalah file yang isinya akan diubah.
Pastikan tidak ada error "SKIP (tidak ada)" pada file penting.

---

## TAHAP 2 — Copy semua file baru dari output

Sebelum menjalankan skrip, copy dulu file-file baru ke project:

### 2A. Layout baru
```
OUTPUT: resources/js/layouts/AdminLayout.vue
  → COPY ke: resources/js/layouts/AdminLayout.vue (file BARU)
```

### 2B. Components baru
```
OUTPUT: resources/js/components/admin/LetterStepIndicator.vue
  → COPY ke: resources/js/components/admin/LetterStepIndicator.vue (BARU)

OUTPUT: resources/js/components/admin/DynamicField.vue
  → COPY ke: resources/js/components/admin/DynamicField.vue
     REPLACE: resources/js/components/Fast/AdminSuratDynamicField.vue
     (tapi jangan hapus dulu yang lama — skrip akan handle)
```

### 2C. Pages baru
```
OUTPUT: resources/js/pages/admin/letters/Form.vue
  → COPY ke: resources/js/pages/admin/letters/Form.vue (BARU)
```

### 2D. Controllers baru
```
OUTPUT: app/Http/Controllers/Admin/DashboardController.php
  → COPY ke: app/Http/Controllers/Admin/DashboardController.php (BARU)

OUTPUT: app/Http/Controllers/Admin/LetterController.php
  → COPY ke: app/Http/Controllers/Admin/LetterController.php (BARU)
```

---

## TAHAP 3 — Jalankan skrip rename

```bash
php scripts/restructure.php
```

Skrip akan:
1. Memindahkan file Vue (layout, components, pages)
2. Memindahkan Controllers
3. Update namespace di Controllers
4. Update Inertia::render() path
5. Update Vue import statements
6. Update routes use statements
7. Regenerate Wayfinder (actions/ folder)
8. Clear Laravel cache

---

## TAHAP 4 — Update Routes Manual

Skrip otomatis sudah update use statements, tapi cek manual routes Anda.
Kemungkinan ada beberapa route file yang perlu disesuaikan:

### routes/web.php — redirect-dashboard
Cek bagian ini masih benar:
```php
// Tidak berubah — nama route tetap sama
return redirect()->route('admin.dashboard');
return redirect()->route('approval.dashboard');
return redirect()->route('fast.user.dashboard'); // ini bisa ikut diganti
```

### Cari semua route yang reference controller lama
Jalankan ini untuk verifikasi:
```bash
grep -r "Fast\\\\Administrasi" routes/
grep -r "Fast\\\\Approval" routes/
grep -r "AdminDashboardController" routes/
grep -r "AdminSuratController" routes/
grep -r "TemplateManagementController" routes/
```

Kalau masih ada hasil, update manual ke namespace baru:
- `Fast\Administrasi\AdminDashboardController` → `Admin\DashboardController`
- `Fast\Administrasi\AdminSuratController` → `Admin\LetterController`
- `Fast\Administrasi\TemplateManagementController` → `Admin\TemplateController`
- `Fast\Approval\DashboardController` → `Admin\ApprovalController`

---

## TAHAP 5 — Update field "Kepada Yth" di jenis surat

### 5A. Via UI (cara mudah)
Buka `/admin/templates`, pilih jenis surat yang perlu "Kepada Yth",
lalu di Field Dinamis tambahkan field baru:
- Label: `Kepada Yth.`
- Key: `kepada_yth`
- Tipe: `recipient` ← tipe baru yang sudah kita buat
- Wajib: Ya/Tidak sesuai kebutuhan

### 5B. Via database (untuk semua jenis surat sekaligus)
Jalankan query ini di database untuk menambahkan field ke semua jenis surat:
```sql
-- Preview dulu
SELECT id, nama, JSON_LENGTH(field_config) as jumlah_field FROM jenis_surats WHERE is_active = 1;

-- Update per jenis surat (ganti ID sesuai kebutuhan)
UPDATE jenis_surats
SET field_config = JSON_ARRAY_APPEND(
  field_config,
  '$',
  JSON_OBJECT(
    'name', 'kepada_yth',
    'label', 'Kepada Yth.',
    'type', 'recipient',
    'required', true,
    'placeholder', 'Pilih atau ketik manual penerima surat',
    'help', 'Nama/jabatan penerima surat resmi'
  )
)
WHERE id IN (5, 7, 9, 10, 11, 12, 13); -- sesuaikan ID
```

### 5C. Update template body surat
Di template body, tambahkan placeholder:
```html
<p>Kepada Yth.</p>
<p>{{kepada_yth}}</p>
<p>di Tempat</p>
```

---

## TAHAP 6 — Update SuratWorkflowService untuk field recipient

File: `app/Services/SuratWorkflowService.php`

Di method `validateDynamicData`, tambahkan case untuk tipe 'recipient':

```php
// Cari bagian switch ($field['type']) dan tambahkan:
case 'recipient':
    $fieldRules[] = 'string';
    $fieldRules[] = 'max:500';
    break;
```

---

## TAHAP 7 — Update ApprovalController (manual)

File yang di-copy oleh skrip: `app/Http/Controllers/Admin/ApprovalController.php`

Cek file ini dan pastikan:
1. Namespace sudah `App\Http\Controllers\Admin`
2. Class name sudah `ApprovalController`
3. Inertia::render sudah `'admin/approval/Index'`

Kalau belum, update manual.

---

## TAHAP 8 — Rebuild frontend

```bash
npm run dev
# atau untuk production:
npm run build
```

---

## TAHAP 9 — Test semua halaman

Cek satu per satu:

| URL | Halaman | Status |
|-----|---------|--------|
| /admin/dashboard | Dashboard admin | |
| /admin/surat/create | Pilih jenis surat | |
| /admin/surat/form/{id} | Form isi surat | |
| /admin/surat/preview | Preview surat | |
| /admin/templates | Setting template | |
| /approval/dashboard | Antrian approval | |
| /verifikasi-qr/{token} | QR verifikasi | |

---

## TROUBLESHOOTING

### Error: "View [admin/dashboard/Index] not found"
→ File Vue belum ada di `resources/js/pages/admin/dashboard/Index.vue`
→ Cek apakah skrip berhasil memindahkan `DashboardAdmin.vue`

### Error: "Class App\Http\Controllers\Fast\... not found"
→ Ada route yang belum diupdate
→ Jalankan: `grep -r "Fast\\\\" routes/`

### Error di Wayfinder (actions/ folder)
→ Jalankan manual: `php artisan wayfinder:generate`

### Import Vue masih pakai path lama
→ Jalankan: `grep -r "AdminWorkspaceLayout" resources/js/`
→ Update manual file yang masih muncul

### TypeError: Cannot read property di Vue component
→ Kemungkinan komponen lama masih di-import
→ Cek: `grep -r "AdminSuratStepIndicator\|AdminSuratDynamicField" resources/js/`

---

## SETELAH SELESAI

Cleanup folder lama (opsional, lakukan setelah semua test pass):
```bash
# Hapus folder Fast jika sudah kosong
rm -rf app/Http/Controllers/Fast/Administrasi
rm -rf app/Http/Controllers/Fast/Approval
rm -rf resources/js/layouts/fast
rm -rf resources/js/components/Fast
rm -rf resources/js/pages/fast/administrasi
rm -rf resources/js/pages/fast/approval
```

Final commit:
```bash
git add -A
git commit -m "refactor: restructure admin folder to professional naming convention"
```
