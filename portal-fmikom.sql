-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for portal-fmikom
DROP DATABASE IF EXISTS `portal-fmikom`;
CREATE DATABASE IF NOT EXISTS `portal-fmikom` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `portal-fmikom`;

-- Dumping structure for table portal-fmikom.absensi_magangs
DROP TABLE IF EXISTS `absensi_magangs`;
CREATE TABLE IF NOT EXISTS `absensi_magangs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pendaftaran_id` bigint unsigned NOT NULL,
  `tanggal` date NOT NULL,
  `waktu_masuk` time DEFAULT NULL,
  `waktu_keluar` time DEFAULT NULL,
  `latitude_masuk` decimal(10,8) DEFAULT NULL,
  `longitude_masuk` decimal(11,8) DEFAULT NULL,
  `latitude_keluar` decimal(10,8) DEFAULT NULL,
  `longitude_keluar` decimal(11,8) DEFAULT NULL,
  `lokasi_valid` tinyint(1) NOT NULL DEFAULT '0',
  `foto_bukti_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `absensi_magangs_pendaftaran_id_foreign` (`pendaftaran_id`),
  CONSTRAINT `absensi_magangs_pendaftaran_id_foreign` FOREIGN KEY (`pendaftaran_id`) REFERENCES `pendaftaran_magangs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.absensi_magangs: ~0 rows (approximately)

-- Dumping structure for table portal-fmikom.cache
DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.cache: ~22 rows (approximately)
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
	('laravel-cache-258e3afa7d15342dd27879f2bb33bd66', 'i:1;', 1777350929),
	('laravel-cache-258e3afa7d15342dd27879f2bb33bd66:timer', 'i:1777350929;', 1777350929),
	('laravel-cache-393e956bdde3251e9f583c4ed4a2a79c', 'i:1;', 1775662555),
	('laravel-cache-393e956bdde3251e9f583c4ed4a2a79c:timer', 'i:1775662555;', 1775662555),
	('laravel-cache-4a6482f7903135ba330e7145cfe29f7f', 'i:1;', 1777828662),
	('laravel-cache-4a6482f7903135ba330e7145cfe29f7f:timer', 'i:1777828661;', 1777828661),
	('laravel-cache-81769b147781020b2de65d961c1b7444', 'i:1;', 1774989115),
	('laravel-cache-81769b147781020b2de65d961c1b7444:timer', 'i:1774989115;', 1774989115),
	('laravel-cache-83fe1a0bcd98af4fde10f1ead3a98eb1', 'i:1;', 1776905059),
	('laravel-cache-83fe1a0bcd98af4fde10f1ead3a98eb1:timer', 'i:1776905059;', 1776905059),
	('laravel-cache-a8d2afb86f3a8f655319d240b3bfd763', 'i:1;', 1776902055),
	('laravel-cache-a8d2afb86f3a8f655319d240b3bfd763:timer', 'i:1776902055;', 1776902055),
	('laravel-cache-c73f0188fb61f87f81c7aac30e2a7a64', 'i:1;', 1774989566),
	('laravel-cache-c73f0188fb61f87f81c7aac30e2a7a64:timer', 'i:1774989566;', 1774989566),
	('laravel-cache-eae20c26987514ec04b47ca6b05a8029', 'i:1;', 1776905819),
	('laravel-cache-eae20c26987514ec04b47ca6b05a8029:timer', 'i:1776905819;', 1776905819),
	('laravel-cache-f06274183fa3d0c5751d4260f152c15d', 'i:1;', 1777881744),
	('laravel-cache-f06274183fa3d0c5751d4260f152c15d:timer', 'i:1777881743;', 1777881744),
	('laravel-cache-hellentiffany24@gmail.com|127.0.0.1', 'i:1;', 1775662555),
	('laravel-cache-hellentiffany24@gmail.com|127.0.0.1:timer', 'i:1775662555;', 1775662555),
	('laravel-cache-tiffanyhelen27@gmail.com|127.0.0.1', 'i:1;', 1777828662),
	('laravel-cache-tiffanyhelen27@gmail.com|127.0.0.1:timer', 'i:1777828662;', 1777828662);

-- Dumping structure for table portal-fmikom.cache_locks
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.cache_locks: ~0 rows (approximately)

-- Dumping structure for table portal-fmikom.detail_respons
DROP TABLE IF EXISTS `detail_respons`;
CREATE TABLE IF NOT EXISTS `detail_respons` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `respons_id` bigint unsigned NOT NULL,
  `pertanyaan_id` bigint unsigned NOT NULL,
  `pilihan_id` bigint unsigned DEFAULT NULL,
  `jawaban_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `detail_respons_respons_id_foreign` (`respons_id`),
  KEY `detail_respons_pertanyaan_id_foreign` (`pertanyaan_id`),
  KEY `detail_respons_pilihan_id_foreign` (`pilihan_id`),
  CONSTRAINT `detail_respons_pertanyaan_id_foreign` FOREIGN KEY (`pertanyaan_id`) REFERENCES `pertanyaan_kuesioners` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detail_respons_pilihan_id_foreign` FOREIGN KEY (`pilihan_id`) REFERENCES `pilihan_jawabans` (`id`) ON DELETE SET NULL,
  CONSTRAINT `detail_respons_respons_id_foreign` FOREIGN KEY (`respons_id`) REFERENCES `respons_kuesioners` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.detail_respons: ~0 rows (approximately)

-- Dumping structure for table portal-fmikom.failed_jobs
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.failed_jobs: ~0 rows (approximately)

-- Dumping structure for table portal-fmikom.fakultas
DROP TABLE IF EXISTS `fakultas`;
CREATE TABLE IF NOT EXISTS `fakultas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fakultas_kode_unique` (`kode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.fakultas: ~0 rows (approximately)

-- Dumping structure for table portal-fmikom.jenis_surats
DROP TABLE IF EXISTS `jenis_surats`;
CREATE TABLE IF NOT EXISTS `jenis_surats` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned DEFAULT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kode_surat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `field_config` json DEFAULT NULL,
  `template_file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `template_surat` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `allowed_role_id` bigint unsigned DEFAULT NULL,
  `approval_role_id` bigint unsigned DEFAULT NULL,
  `perlu_approval` tinyint(1) NOT NULL DEFAULT '1',
  `qr_mode` enum('immediate','after_approval') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'after_approval' COMMENT 'immediate=QR langsung aktif, after_approval=setelah disetujui',
  `allowed_roles` json DEFAULT NULL COMMENT 'Array slug role yang boleh buat surat ini, null=semua',
  `urutan` smallint unsigned NOT NULL DEFAULT '0',
  `alur_pengajuan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'submission',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `jenis_surats_allowed_role_id_foreign` (`allowed_role_id`),
  KEY `jenis_surats_approval_role_id_foreign` (`approval_role_id`),
  KEY `jenis_surats_category_id_foreign` (`category_id`),
  CONSTRAINT `jenis_surats_allowed_role_id_foreign` FOREIGN KEY (`allowed_role_id`) REFERENCES `roles` (`id`),
  CONSTRAINT `jenis_surats_approval_role_id_foreign` FOREIGN KEY (`approval_role_id`) REFERENCES `roles` (`id`),
  CONSTRAINT `jenis_surats_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `surat_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.jenis_surats: ~7 rows (approximately)
INSERT INTO `jenis_surats` (`id`, `category_id`, `nama`, `slug`, `kode_surat`, `deskripsi`, `field_config`, `template_file_path`, `template_surat`, `created_at`, `updated_at`, `allowed_role_id`, `approval_role_id`, `perlu_approval`, `qr_mode`, `allowed_roles`, `urutan`, `alur_pengajuan`, `is_active`) VALUES
	(5, 2, 'Surat Keterangan Lulus', 'surat-keterangan-lulus', 'SKL', 'Surat keterangan sementara bagi mahasiswa yang telah dinyatakan lulus.', '[{"name": "nim", "type": "text", "label": "NIM", "required": true, "placeholder": "Contoh: 202231001"}, {"name": "program_studi", "type": "text", "label": "Program Studi", "required": true, "placeholder": "Contoh: Sistem Informasi"}, {"name": "tanggal_yudisium", "type": "date", "label": "Tanggal Yudisium", "required": true}, {"name": "ipk_akhir", "type": "number", "label": "IPK Akhir", "required": true, "placeholder": "Contoh: 3.75"}, {"name": "judul_tugas_akhir", "type": "textarea", "label": "Judul Tugas Akhir", "required": false, "placeholder": "Opsional jika diperlukan pada surat"}]', NULL, NULL, '2026-04-18 04:27:36', '2026-04-27 21:42:37', 3, 7, 1, 'after_approval', NULL, 0, 'submission', 1),
	(7, NULL, 'Surat Keterangan Mengajar', NULL, NULL, 'Surat keterangan bahwa dosen aktif mengajar pada semester tertentu.', '[{"name": "nip", "type": "text", "label": "Nomor Induk Pegawai / NIDN", "required": true}, {"name": "semester", "type": "select", "label": "Semester", "options": ["Ganjil", "Genap"], "required": true}, {"name": "tahun_akademik", "type": "text", "label": "Tahun Akademik", "required": true, "placeholder": "Contoh: 2026/2027"}, {"name": "mata_kuliah", "type": "textarea", "label": "Mata Kuliah yang Diampu", "required": true, "placeholder": "Pisahkan dengan koma jika lebih dari satu"}]', NULL, NULL, '2026-04-18 04:27:36', '2026-04-22 11:53:26', 4, 6, 1, 'after_approval', NULL, 0, 'submission', 1),
	(9, 1, 'Permohonan Cuti Mahasiswa', 'permohonan-cuti-mahasiswa', 'CUTI-MHS', 'Pengajuan cuti akademik mahasiswa pada semester berjalan.', '[{"name": "nim", "type": "text", "label": "NIM", "required": true, "placeholder": "Contoh: 202231001"}, {"name": "semester", "type": "number", "label": "Semester Saat Ini", "required": true, "placeholder": "Contoh: 4"}, {"name": "program_studi", "type": "text", "label": "Program Studi", "required": true, "placeholder": "Contoh: Informatika"}, {"name": "tahun_akademik", "type": "text", "label": "Tahun Akademik", "required": true, "placeholder": "Contoh: 2026/2027"}, {"name": "semester_pengajuan", "type": "select", "label": "Semester Pengajuan", "options": ["Ganjil", "Genap"], "required": true}, {"name": "alasan_cuti", "type": "textarea", "label": "Alasan Cuti", "required": true, "placeholder": "Jelaskan alasan mengajukan cuti"}]', NULL, NULL, '2026-04-26 23:28:31', '2026-04-27 21:42:37', 3, 6, 1, 'after_approval', NULL, 0, 'submission', 1),
	(10, 1, 'Surat Permohonan Observasi', 'surat-permohonan-observasi', 'OBSERVASI', 'Surat pengantar untuk observasi atau pengambilan data ke instansi tujuan.', '[{"name": "nim", "type": "text", "label": "NIM", "required": true, "placeholder": "Contoh: 202231001"}, {"name": "program_studi", "type": "text", "label": "Program Studi", "required": true, "placeholder": "Contoh: Informatika"}, {"name": "instansi_tujuan", "type": "text", "label": "Instansi Tujuan", "required": true, "placeholder": "Contoh: Dinas Kominfo Kabupaten Cilacap"}, {"name": "alamat_instansi", "type": "textarea", "label": "Alamat Instansi", "required": true, "placeholder": "Alamat lengkap instansi tujuan"}, {"name": "topik_observasi", "type": "textarea", "label": "Topik Observasi", "required": true, "placeholder": "Jelaskan topik atau fokus observasi"}, {"name": "tanggal_mulai", "type": "date", "label": "Tanggal Mulai Observasi", "required": true}, {"name": "tanggal_selesai", "type": "date", "label": "Tanggal Selesai Observasi", "required": true}]', NULL, NULL, '2026-04-26 23:28:31', '2026-04-27 21:42:37', 3, 7, 1, 'after_approval', NULL, 0, 'submission', 1),
	(11, 1, 'Permohonan Dispensasi Mahasiswa', 'permohonan-dispensasi-mahasiswa', 'DISPENSASI-MHS', 'Surat permohonan dispensasi mahasiswa karena kegiatan atau kondisi tertentu.', '[{"name": "nim", "type": "text", "label": "NIM", "required": true, "placeholder": "Contoh: 202231001"}, {"name": "program_studi", "type": "text", "label": "Program Studi", "required": true, "placeholder": "Contoh: Informatika"}, {"name": "mata_kuliah", "type": "text", "label": "Mata Kuliah", "required": true, "placeholder": "Contoh: Basis Data"}, {"name": "kelas", "type": "text", "label": "Kelas", "required": false, "placeholder": "Contoh: IF-4A"}, {"name": "dosen_pengampu", "type": "text", "label": "Dosen Pengampu", "required": false, "placeholder": "Nama dosen pengampu"}, {"name": "tanggal_kegiatan", "type": "date", "label": "Tanggal Kegiatan / Dispensasi", "required": true}, {"name": "alasan_dispensasi", "type": "textarea", "label": "Alasan Dispensasi", "required": true, "placeholder": "Jelaskan alasan permohonan dispensasi"}]', NULL, NULL, '2026-04-26 23:28:31', '2026-04-27 21:42:37', 3, 6, 1, 'after_approval', NULL, 0, 'submission', 1),
	(12, 1, 'Permohonan Pindah Kelas', 'permohonan-pindah-kelas', 'PINDAH-KELAS', 'Surat permohonan perpindahan kelas pada mata kuliah tertentu.', '[{"name": "nim", "type": "text", "label": "NIM", "required": true, "placeholder": "Contoh: 202231001"}, {"name": "program_studi", "type": "text", "label": "Program Studi", "required": true, "placeholder": "Contoh: Sistem Informasi"}, {"name": "mata_kuliah", "type": "text", "label": "Mata Kuliah", "required": true, "placeholder": "Contoh: Pemrograman Web"}, {"name": "kelas_asal", "type": "text", "label": "Kelas Asal", "required": true, "placeholder": "Contoh: SI-2A"}, {"name": "kelas_tujuan", "type": "text", "label": "Kelas Tujuan", "required": true, "placeholder": "Contoh: SI-2B"}, {"name": "alasan_pindah", "type": "textarea", "label": "Alasan Pindah Kelas", "required": true, "placeholder": "Jelaskan alasan perpindahan kelas"}]', NULL, NULL, '2026-04-26 23:28:31', '2026-04-27 21:42:37', 3, 6, 1, 'after_approval', NULL, 0, 'submission', 1),
	(13, 3, 'Permohonan Menjadi Penguji Sidang', 'permohonan-menjadi-penguji-sidang', 'PENGUJI-SIDANG', 'Surat penugasan atau permohonan bagi dosen untuk menjadi penguji sidang.', '[{"name": "nip", "type": "text", "label": "NIP / NIDN", "required": true, "placeholder": "Contoh: 198812312020121001"}, {"name": "nama_dosen", "type": "text", "label": "Nama Dosen", "required": true, "placeholder": "Nama dosen yang diajukan"}, {"name": "nama_mahasiswa", "type": "text", "label": "Nama Mahasiswa", "required": true, "placeholder": "Nama mahasiswa yang akan sidang"}, {"name": "nim_mahasiswa", "type": "text", "label": "NIM Mahasiswa", "required": true, "placeholder": "Contoh: 202231001"}, {"name": "judul_skripsi", "type": "textarea", "label": "Judul Skripsi / Tugas Akhir", "required": true, "placeholder": "Masukkan judul skripsi atau tugas akhir"}, {"name": "tanggal_sidang", "type": "date", "label": "Tanggal Sidang", "required": true}, {"name": "ruang_sidang", "type": "text", "label": "Ruang Sidang", "required": false, "placeholder": "Contoh: Ruang Sidang 1"}]', NULL, NULL, '2026-04-26 23:28:31', '2026-05-04 01:33:56', 4, 7, 1, 'after_approval', NULL, 0, 'submission', 0);

-- Dumping structure for table portal-fmikom.jenis_surat_roles
DROP TABLE IF EXISTS `jenis_surat_roles`;
CREATE TABLE IF NOT EXISTS `jenis_surat_roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `jenis_surat_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jenis_surat_roles_jenis_surat_id_role_id_unique` (`jenis_surat_id`,`role_id`),
  KEY `jenis_surat_roles_role_id_foreign` (`role_id`),
  CONSTRAINT `jenis_surat_roles_jenis_surat_id_foreign` FOREIGN KEY (`jenis_surat_id`) REFERENCES `jenis_surats` (`id`) ON DELETE CASCADE,
  CONSTRAINT `jenis_surat_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.jenis_surat_roles: ~0 rows (approximately)

-- Dumping structure for table portal-fmikom.jobs
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_reserved_at_available_at_index` (`queue`,`reserved_at`,`available_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.jobs: ~0 rows (approximately)

-- Dumping structure for table portal-fmikom.job_batches
DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.job_batches: ~0 rows (approximately)

-- Dumping structure for table portal-fmikom.karir_alumnis
DROP TABLE IF EXISTS `karir_alumnis`;
CREATE TABLE IF NOT EXISTS `karir_alumnis` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `alumni_id` bigint unsigned NOT NULL,
  `nama_perusahaan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `jabatan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `bidang_industri` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kota` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `negara` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Indonesia',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `gaji_range` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_kerja` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `karir_alumnis_alumni_id_foreign` (`alumni_id`),
  CONSTRAINT `karir_alumnis_alumni_id_foreign` FOREIGN KEY (`alumni_id`) REFERENCES `profil_alumnis` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.karir_alumnis: ~0 rows (approximately)

-- Dumping structure for table portal-fmikom.kuesioners
DROP TABLE IF EXISTS `kuesioners`;
CREATE TABLE IF NOT EXISTS `kuesioners` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pembuat_id` bigint unsigned NOT NULL,
  `judul` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `periode_mulai` date NOT NULL,
  `periode_selesai` date NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `tujuan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kuesioners_pembuat_id_foreign` (`pembuat_id`),
  CONSTRAINT `kuesioners_pembuat_id_foreign` FOREIGN KEY (`pembuat_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.kuesioners: ~0 rows (approximately)

-- Dumping structure for table portal-fmikom.logbook_magangs
DROP TABLE IF EXISTS `logbook_magangs`;
CREATE TABLE IF NOT EXISTS `logbook_magangs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pendaftaran_id` bigint unsigned NOT NULL,
  `tanggal` date NOT NULL,
  `aktivitas_harian` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kompetensi_dicapai` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `catatan_dosen` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `logbook_magangs_pendaftaran_id_foreign` (`pendaftaran_id`),
  CONSTRAINT `logbook_magangs_pendaftaran_id_foreign` FOREIGN KEY (`pendaftaran_id`) REFERENCES `pendaftaran_magangs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.logbook_magangs: ~0 rows (approximately)

-- Dumping structure for table portal-fmikom.lowongan_infos
DROP TABLE IF EXISTS `lowongan_infos`;
CREATE TABLE IF NOT EXISTS `lowongan_infos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pembuat_id` bigint unsigned NOT NULL,
  `judul` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `tipe` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_perusahaan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `poster_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link_eksternal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lowongan_infos_pembuat_id_foreign` (`pembuat_id`),
  CONSTRAINT `lowongan_infos_pembuat_id_foreign` FOREIGN KEY (`pembuat_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.lowongan_infos: ~0 rows (approximately)

-- Dumping structure for table portal-fmikom.migrations
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.migrations: ~42 rows (approximately)
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '0001_01_01_000000_create_users_table', 1),
	(2, '0001_01_01_000001_create_cache_table', 1),
	(3, '0001_01_01_000002_create_jobs_table', 1),
	(4, '2025_08_14_170933_add_two_factor_columns_to_users_table', 1),
	(5, '2026_03_29_180950_create_roles_table', 1),
	(6, '2026_03_29_180951_create_fakultas_table', 1),
	(7, '2026_03_29_180954_create_program_studis_table', 1),
	(8, '2026_03_29_180955_create_perusahaan_mitras_table', 1),
	(9, '2026_03_29_180956_create_pembimbing_lapangans_table', 1),
	(10, '2026_03_29_180958_create_jenis_surats_table', 1),
	(11, '2026_03_29_181000_create_surats_table', 1),
	(12, '2026_03_29_181003_create_surat_approval_flows_table', 1),
	(13, '2026_03_29_181004_create_surat_lampirans_table', 1),
	(14, '2026_03_29_181005_create_pendaftaran_magangs_table', 1),
	(15, '2026_03_29_181007_create_absensi_magangs_table', 1),
	(16, '2026_03_29_181007_create_logbook_magangs_table', 1),
	(17, '2026_03_29_181008_create_penilaian_magangs_table', 1),
	(18, '2026_03_29_181009_create_profil_alumnis_table', 1),
	(19, '2026_03_29_181010_create_karir_alumnis_table', 1),
	(20, '2026_03_29_181011_create_kuesioners_table', 1),
	(21, '2026_03_29_181012_create_pertanyaan_kuesioners_table', 1),
	(22, '2026_03_29_181013_create_pilihan_jawabans_table', 1),
	(23, '2026_03_29_181013_create_respons_kuesioners_table', 1),
	(24, '2026_03_29_181014_create_detail_respons_table', 1),
	(25, '2026_03_29_181015_create_lowongan_infos_table', 1),
	(26, '2026_03_29_181016_add_columns_to_users_table', 1),
	(27, '2026_03_29_201237_add_otp_and_password_changed_to_users_table', 2),
	(28, '2026_03_29_214305_add_registration_fields_to_users_table', 3),
	(29, '2026_04_10_084236_add_fields_to_jenis_surats_table', 4),
	(30, '2026_04_10_084935_add_qr_token_to_surats_table', 4),
	(31, '2026_04_14_110300_add_tanggal_kebutuhan_to_surats_table', 5),
	(32, '2026_04_15_052519_create_jenis_surat_roles_table', 6),
	(33, '2026_04_15_054253_create_surat_data_table', 7),
	(34, '2026_04_15_055204_add_perlu_approval_to_jenis_surats_table', 8),
	(35, '2026_04_18_120000_add_field_config_to_jenis_surats_table', 9),
	(36, '2026_04_20_060000_add_workflow_columns_to_surat_approval_flows_table', 10),
	(37, '2026_04_27_130000_create_surat_categories_table', 11),
	(38, '2026_04_27_130100_add_category_fields_to_jenis_surats_table', 11),
	(39, '2026_04_27_140000_create_surat_templates_table', 12),
	(40, '2026_04_27_140100_create_surat_template_placeholders_table', 12),
	(41, '2026_04_27_140200_add_generated_document_fields_to_surats_table', 12),
	(42, '2026_04_28_110000_add_api_workflow_fields_to_surats_and_jenis_surats_table', 13),
	(43, '2026_05_03_000001_enhance_surat_system', 14);

-- Dumping structure for table portal-fmikom.nomor_surat_sequences
DROP TABLE IF EXISTS `nomor_surat_sequences`;
CREATE TABLE IF NOT EXISTS `nomor_surat_sequences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode_surat` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Kode jenis surat, e.g. CUTI-MHS',
  `tahun` smallint unsigned NOT NULL,
  `bulan` smallint unsigned NOT NULL,
  `urutan` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nomor_surat_sequences_kode_surat_tahun_bulan_unique` (`kode_surat`,`tahun`,`bulan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.nomor_surat_sequences: ~0 rows (approximately)

-- Dumping structure for table portal-fmikom.password_reset_tokens
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.password_reset_tokens: ~2 rows (approximately)
INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
	('muchlisinmaruf@gmail.com', '$2y$12$HV9sEnOHXdFBzBUffbISwuXVa2VHXeovZmgdJs1wFWRtymE.sKsJS', '2026-03-29 15:11:55'),
	('tiffanyhellen27@gmail.com', '$2y$12$BydpmQHppo7K8kWVv1wlYeQXW/2LpHVWT1YapiSdfa5SaYmNXnXYa', '2026-04-08 08:22:15');

-- Dumping structure for table portal-fmikom.pembimbing_lapangans
DROP TABLE IF EXISTS `pembimbing_lapangans`;
CREATE TABLE IF NOT EXISTS `pembimbing_lapangans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `perusahaan_id` bigint unsigned NOT NULL,
  `jabatan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pembimbing_lapangans_user_id_foreign` (`user_id`),
  KEY `pembimbing_lapangans_perusahaan_id_foreign` (`perusahaan_id`),
  CONSTRAINT `pembimbing_lapangans_perusahaan_id_foreign` FOREIGN KEY (`perusahaan_id`) REFERENCES `perusahaan_mitras` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pembimbing_lapangans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.pembimbing_lapangans: ~0 rows (approximately)

-- Dumping structure for table portal-fmikom.pendaftaran_magangs
DROP TABLE IF EXISTS `pendaftaran_magangs`;
CREATE TABLE IF NOT EXISTS `pendaftaran_magangs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `mahasiswa_id` bigint unsigned NOT NULL,
  `perusahaan_id` bigint unsigned NOT NULL,
  `dosen_pembimbing_id` bigint unsigned DEFAULT NULL,
  `pembimbing_lapangan_id` bigint unsigned DEFAULT NULL,
  `surat_tugas_id` bigint unsigned DEFAULT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pendaftaran_magangs_mahasiswa_id_foreign` (`mahasiswa_id`),
  KEY `pendaftaran_magangs_perusahaan_id_foreign` (`perusahaan_id`),
  KEY `pendaftaran_magangs_dosen_pembimbing_id_foreign` (`dosen_pembimbing_id`),
  KEY `pendaftaran_magangs_pembimbing_lapangan_id_foreign` (`pembimbing_lapangan_id`),
  KEY `pendaftaran_magangs_surat_tugas_id_foreign` (`surat_tugas_id`),
  CONSTRAINT `pendaftaran_magangs_dosen_pembimbing_id_foreign` FOREIGN KEY (`dosen_pembimbing_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pendaftaran_magangs_mahasiswa_id_foreign` FOREIGN KEY (`mahasiswa_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pendaftaran_magangs_pembimbing_lapangan_id_foreign` FOREIGN KEY (`pembimbing_lapangan_id`) REFERENCES `pembimbing_lapangans` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pendaftaran_magangs_perusahaan_id_foreign` FOREIGN KEY (`perusahaan_id`) REFERENCES `perusahaan_mitras` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pendaftaran_magangs_surat_tugas_id_foreign` FOREIGN KEY (`surat_tugas_id`) REFERENCES `surats` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.pendaftaran_magangs: ~0 rows (approximately)

-- Dumping structure for table portal-fmikom.penilaian_magangs
DROP TABLE IF EXISTS `penilaian_magangs`;
CREATE TABLE IF NOT EXISTS `penilaian_magangs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pendaftaran_id` bigint unsigned NOT NULL,
  `dosen_id` bigint unsigned DEFAULT NULL,
  `pembimbing_lapangan_id` bigint unsigned DEFAULT NULL,
  `nilai_logbook` decimal(5,2) DEFAULT NULL,
  `nilai_presentasi` decimal(5,2) DEFAULT NULL,
  `nilai_dari_pembimbing` decimal(5,2) DEFAULT NULL,
  `nilai_akhir` decimal(5,2) DEFAULT NULL,
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `tanggal_nilai` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `penilaian_magangs_pendaftaran_id_foreign` (`pendaftaran_id`),
  KEY `penilaian_magangs_dosen_id_foreign` (`dosen_id`),
  KEY `penilaian_magangs_pembimbing_lapangan_id_foreign` (`pembimbing_lapangan_id`),
  CONSTRAINT `penilaian_magangs_dosen_id_foreign` FOREIGN KEY (`dosen_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `penilaian_magangs_pembimbing_lapangan_id_foreign` FOREIGN KEY (`pembimbing_lapangan_id`) REFERENCES `pembimbing_lapangans` (`id`) ON DELETE SET NULL,
  CONSTRAINT `penilaian_magangs_pendaftaran_id_foreign` FOREIGN KEY (`pendaftaran_id`) REFERENCES `pendaftaran_magangs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.penilaian_magangs: ~0 rows (approximately)

-- Dumping structure for table portal-fmikom.pertanyaan_kuesioners
DROP TABLE IF EXISTS `pertanyaan_kuesioners`;
CREATE TABLE IF NOT EXISTS `pertanyaan_kuesioners` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kuesioner_id` bigint unsigned NOT NULL,
  `teks_pertanyaan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipe` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `urutan` int NOT NULL DEFAULT '0',
  `wajib_diisi` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pertanyaan_kuesioners_kuesioner_id_foreign` (`kuesioner_id`),
  CONSTRAINT `pertanyaan_kuesioners_kuesioner_id_foreign` FOREIGN KEY (`kuesioner_id`) REFERENCES `kuesioners` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.pertanyaan_kuesioners: ~0 rows (approximately)

-- Dumping structure for table portal-fmikom.perusahaan_mitras
DROP TABLE IF EXISTS `perusahaan_mitras`;
CREATE TABLE IF NOT EXISTS `perusahaan_mitras` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `kota` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `radius_valid_meter` decimal(10,2) DEFAULT NULL,
  `bidang_industri` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kontak_person` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telepon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.perusahaan_mitras: ~0 rows (approximately)

-- Dumping structure for table portal-fmikom.pilihan_jawabans
DROP TABLE IF EXISTS `pilihan_jawabans`;
CREATE TABLE IF NOT EXISTS `pilihan_jawabans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pertanyaan_id` bigint unsigned NOT NULL,
  `teks_pilihan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nilai` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pilihan_jawabans_pertanyaan_id_foreign` (`pertanyaan_id`),
  CONSTRAINT `pilihan_jawabans_pertanyaan_id_foreign` FOREIGN KEY (`pertanyaan_id`) REFERENCES `pertanyaan_kuesioners` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.pilihan_jawabans: ~0 rows (approximately)

-- Dumping structure for table portal-fmikom.profil_alumnis
DROP TABLE IF EXISTS `profil_alumnis`;
CREATE TABLE IF NOT EXISTS `profil_alumnis` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `program_studi_id` bigint unsigned NOT NULL,
  `nim` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tahun_lulus` int NOT NULL,
  `no_telepon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linkedin_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `profil_alumnis_nim_unique` (`nim`),
  KEY `profil_alumnis_user_id_foreign` (`user_id`),
  KEY `profil_alumnis_program_studi_id_foreign` (`program_studi_id`),
  CONSTRAINT `profil_alumnis_program_studi_id_foreign` FOREIGN KEY (`program_studi_id`) REFERENCES `program_studis` (`id`) ON DELETE CASCADE,
  CONSTRAINT `profil_alumnis_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.profil_alumnis: ~0 rows (approximately)

-- Dumping structure for table portal-fmikom.program_studis
DROP TABLE IF EXISTS `program_studis`;
CREATE TABLE IF NOT EXISTS `program_studis` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `fakultas_id` bigint unsigned NOT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `program_studis_kode_unique` (`kode`),
  KEY `program_studis_fakultas_id_foreign` (`fakultas_id`),
  CONSTRAINT `program_studis_fakultas_id_foreign` FOREIGN KEY (`fakultas_id`) REFERENCES `fakultas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.program_studis: ~0 rows (approximately)

-- Dumping structure for table portal-fmikom.respons_kuesioners
DROP TABLE IF EXISTS `respons_kuesioners`;
CREATE TABLE IF NOT EXISTS `respons_kuesioners` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kuesioner_id` bigint unsigned NOT NULL,
  `alumni_id` bigint unsigned NOT NULL,
  `is_complete` tinyint(1) NOT NULL DEFAULT '0',
  `tanggal_isi` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `respons_kuesioners_kuesioner_id_foreign` (`kuesioner_id`),
  KEY `respons_kuesioners_alumni_id_foreign` (`alumni_id`),
  CONSTRAINT `respons_kuesioners_alumni_id_foreign` FOREIGN KEY (`alumni_id`) REFERENCES `profil_alumnis` (`id`) ON DELETE CASCADE,
  CONSTRAINT `respons_kuesioners_kuesioner_id_foreign` FOREIGN KEY (`kuesioner_id`) REFERENCES `kuesioners` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.respons_kuesioners: ~0 rows (approximately)

-- Dumping structure for table portal-fmikom.roles
DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.roles: ~5 rows (approximately)
INSERT INTO `roles` (`id`, `nama`, `slug`, `deskripsi`, `created_at`, `updated_at`) VALUES
	(3, 'Mahasiswa', 'mahasiswa', 'Pengguna mahasiswa untuk layanan akademik FAST.', '2026-04-18 04:24:26', '2026-04-18 04:24:26'),
	(4, 'Dosen', 'dosen', 'Pengguna dosen untuk layanan akademik FAST.', '2026-04-18 04:24:26', '2026-04-18 04:24:26'),
	(5, 'Admin', 'admin', 'Pengelola validasi administrasi surat FAST.', '2026-04-18 04:24:26', '2026-04-18 04:24:26'),
	(6, 'Kaprodi', 'kaprodi', 'Pemberi persetujuan surat tingkat program studi.', '2026-04-22 17:06:37', '2026-04-22 17:06:37'),
	(7, 'Dekan', 'dekan', 'Pemberi persetujuan surat tingkat fakultas.', '2026-04-22 17:06:37', '2026-04-22 17:06:37');

-- Dumping structure for table portal-fmikom.sessions
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.sessions: ~1 rows (approximately)
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
	('otZ68116UEwamMF0Pc3XslmbbfTsZZxlQViKpBf6', 5, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiNUxRczBWMU5QWFhtZEp0OXoxQnFxeE1qVm5RTUJtVndPeEVINlpNcCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDg6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi90ZW1wbGF0ZXMvMTIvcHJldmlldyI7czo1OiJyb3V0ZSI7czoyMzoiYWRtaW4udGVtcGxhdGVzLnByZXZpZXciO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTo1O3M6MzoidXJsIjthOjE6e3M6ODoiaW50ZW5kZWQiO3M6Mzk6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9zZXR0aW5ncy9zZWN1cml0eSI7fX0=', 1777883984);

-- Dumping structure for table portal-fmikom.surats
DROP TABLE IF EXISTS `surats`;
CREATE TABLE IF NOT EXISTS `surats` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `jenis_surat_id` bigint unsigned NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pengajuan',
  `pemohon_id` bigint unsigned NOT NULL,
  `nomor_surat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keperluan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kepada_yth` json DEFAULT NULL COMMENT 'Array penerima surat, bisa multiple',
  `lampiran_keterangan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Contoh: 1 (satu) lembar',
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `isi_surat` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `admin_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `rejection_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `validated_by_admin_id` bigint unsigned DEFAULT NULL,
  `validated_by_admin_at` datetime DEFAULT NULL,
  `approved_by_id` bigint unsigned DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `generated_file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `generated_file_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `generated_at` datetime DEFAULT NULL,
  `template_version` int unsigned DEFAULT NULL,
  `revisi_ke` tinyint unsigned NOT NULL DEFAULT '0',
  `catatan_revisi` text COLLATE utf8mb4_unicode_ci,
  `print_count` smallint unsigned NOT NULL DEFAULT '0',
  `last_printed_at` timestamp NULL DEFAULT NULL,
  `rendered_snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `tanggal_pengajuan` datetime DEFAULT NULL,
  `tanggal_kebutuhan` date DEFAULT NULL,
  `tanggal_selesai` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `qr_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `surats_qr_token_unique` (`qr_token`),
  KEY `surats_jenis_surat_id_foreign` (`jenis_surat_id`),
  KEY `surats_pemohon_id_foreign` (`pemohon_id`),
  KEY `surats_validated_by_admin_id_foreign` (`validated_by_admin_id`),
  KEY `surats_approved_by_id_foreign` (`approved_by_id`),
  CONSTRAINT `surats_approved_by_id_foreign` FOREIGN KEY (`approved_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `surats_jenis_surat_id_foreign` FOREIGN KEY (`jenis_surat_id`) REFERENCES `jenis_surats` (`id`) ON DELETE CASCADE,
  CONSTRAINT `surats_pemohon_id_foreign` FOREIGN KEY (`pemohon_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `surats_validated_by_admin_id_foreign` FOREIGN KEY (`validated_by_admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.surats: ~11 rows (approximately)
INSERT INTO `surats` (`id`, `jenis_surat_id`, `type`, `pemohon_id`, `nomor_surat`, `keperluan`, `kepada_yth`, `lampiran_keterangan`, `status`, `isi_surat`, `admin_note`, `rejection_reason`, `validated_by_admin_id`, `validated_by_admin_at`, `approved_by_id`, `approved_at`, `generated_file_path`, `generated_file_type`, `generated_at`, `template_version`, `revisi_ke`, `catatan_revisi`, `print_count`, `last_printed_at`, `rendered_snapshot`, `tanggal_pengajuan`, `tanggal_kebutuhan`, `tanggal_selesai`, `created_at`, `updated_at`, `qr_token`) VALUES
	(81, 9, 'pengajuan', 8, NULL, 'Untuk mencari nafkah', NULL, NULL, 'validated_admin', '{"jenis_surat_id":9,"jenis_surat":"Permohonan Cuti Mahasiswa","keperluan":"Untuk mencari nafkah","tanggal_kebutuhan":"2026-04-28","data":{"nim":"22EO10001","semester":"4","program_studi":"Informatika","tahun_akademik":"2026\\/2027","semester_pengajuan":"Ganjil","alasan_cuti":"Kerja bu"}}', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, '2026-04-28 04:46:13', '2026-04-28', NULL, '2026-04-27 21:46:13', '2026-04-27 22:07:25', NULL),
	(87, 9, 'surat_keluar', 5, 'B/0087/CUTI-MHS/FMIKOM/2026', 'Tuntutan Pekerjaan', NULL, NULL, 'finished', '{"jenis_surat_id":9,"jenis_surat":"Permohonan Cuti Mahasiswa","keperluan":"Tuntutan Pekerjaan","data":{"nim":"22EO10001","semester":"4","program_studi":"Informatika","tahun_akademik":"2026\\/2027","semester_pengajuan":"Genap","alasan_cuti":"jiaaaaaa"},"created_by":{"id":5,"name":"Admin"}}', NULL, NULL, 5, '2026-04-28 09:45:25', NULL, NULL, 'fast/generated/surat-87-permohonan-cuti-mahasiswa.pdf', 'pdf', '2026-04-28 09:45:25', 1, 0, NULL, 0, NULL, '<div style="height: 24mm;"></div>\n<section class="surat surat-permohonan-cuti">\n    <h1 style="margin-bottom: 0; text-align: center;">PERMOHONAN CUTI MAHASISWA</h1>\n    <p style="margin-bottom: 12px; text-align: center;">Nomor: B/0087/CUTI-MHS/FMIKOM/2026</p>\n\n    <p style="margin-bottom: 10px; text-align: justify;">Yang bertanda tangan di bawah ini:</p>\n\n    <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">\n        <tbody>\n            <tr>\n                <td style="width: 126px; padding: 1px 0; vertical-align: top;">Nama</td>\n                <td style="width: 14px; padding: 1px 0; vertical-align: top;">:</td>\n                <td style="padding: 1px 0; vertical-align: top;">Admin</td>\n            </tr>\n            <tr>\n                <td style="padding: 1px 0; vertical-align: top;">NIM</td>\n                <td style="padding: 1px 0; vertical-align: top;">:</td>\n                <td style="padding: 1px 0; vertical-align: top;">22EO10001</td>\n            </tr>\n            <tr>\n                <td style="padding: 1px 0; vertical-align: top;">Semester</td>\n                <td style="padding: 1px 0; vertical-align: top;">:</td>\n                <td style="padding: 1px 0; vertical-align: top;">4 (Empat)</td>\n            </tr>\n            <tr>\n                <td style="padding: 1px 0; vertical-align: top;">Fak/Prodi</td>\n                <td style="padding: 1px 0; vertical-align: top;">:</td>\n                <td style="padding: 1px 0; vertical-align: top;">FMIKOM/Informatika</td>\n            </tr>\n        </tbody>\n    </table>\n\n    <p style="margin-bottom: 10px; text-align: justify;">\n        Dengan ini mengajukan permohonan cuti, dengan alasan jiaaaaaa.\n    </p>\n\n    <p style="text-align: justify;">\n        Demikian permohonan ini saya sampaikan, atas perhatian dan terkabulnya\n        permohonan ini disampaikan terima kasih.\n    </p>\n\n    <div style="margin-top: 18px; width: 100%;">\n        <div style="margin-left: auto; width: 245px;">\n            <p style="margin-bottom: 0;">Cilacap, 28 April 2026</p>\n            <p style="margin-bottom: 34px;">Pemohon,</p>\n            <p style="margin-bottom: 0;"><strong>Admin</strong></p>\n            <p>NIM. 22EO10001</p>\n        </div>\n    </div>\n\n    <p style="margin-top: 16px; margin-bottom: 8px; text-align: center;">Mengetahui,</p>\n\n    <table style="width: 100%; border-collapse: collapse; margin-bottom: 16px;">\n        <tbody>\n            <tr>\n                <td style="width: 50%; padding: 0 12px 0 0; vertical-align: top; text-align: center;">\n                    <p style="margin-bottom: 34px;">DPA</p>\n                    <p style="margin-bottom: 0;"><strong>Verry, M.Kom.</strong></p>\n                    <p>NIK. 41230714</p>\n                </td>\n                <td style="width: 50%; padding: 0 0 0 12px; vertical-align: top; text-align: center;">\n                    <p style="margin-bottom: 0;">Kaprodi Informatika</p>\n                    <p style="margin-bottom: 34px;">a.n Sekretaris Fakultas,</p>\n                    <p style="margin-bottom: 0;"><strong>Tri Anggoro, M.Kom.</strong></p>\n                    <p>NIK. 41230714268</p>\n                </td>\n            </tr>\n        </tbody>\n    </table>\n\n    <div>\n        <p style="margin-bottom: 4px;">Tembusan:</p>\n        <p style="margin-bottom: 0;">1. Ka. Prodi</p>\n        <p style="margin-bottom: 0;">2. Bagian Akademik</p>\n        <p style="margin-bottom: 0;">3. Bagian Keuangan</p>\n        <p style="margin-bottom: 0;">4. Fakultas</p>\n        <p style="margin-bottom: 0;">5. Mahasiswa</p>\n    </div>\n</section>', '2026-04-28 09:45:25', NULL, '2026-04-28 09:45:25', '2026-04-28 02:45:25', '2026-04-28 02:45:25', '5227580b-b1bc-4fec-94e2-a613ab87f7be'),
	(88, 9, 'surat_keluar', 5, 'B/0088/CUTI-MHS/FMIKOM/2026', 'Tuntutan Pekerjaan', NULL, NULL, 'finished', '{"jenis_surat_id":9,"jenis_surat":"Permohonan Cuti Mahasiswa","keperluan":"Tuntutan Pekerjaan","data":{"nim":"22EO10001","semester":"5","program_studi":"Informatika","tahun_akademik":"2026\\/2027","semester_pengajuan":"Ganjil","alasan_cuti":"jyyyyyyyy"},"created_by":{"id":5,"name":"Admin"}}', NULL, NULL, 5, '2026-04-28 09:55:11', NULL, NULL, 'fast/generated/surat-88-permohonan-cuti-mahasiswa.pdf', 'pdf', '2026-04-28 09:55:12', 1, 0, NULL, 0, NULL, '<section style="margin-bottom: 12px; text-align: center;">\n    <img src="data:image/png;base64,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" alt="Logo UNU Al Ghazali Cilacap" style="display: block; height: 78px; margin: 0 auto 4px;">\n    <div style="color: #00b050; font-family: \'Times New Roman\', serif; font-weight: 700; line-height: 1.08;">\n        <div style="font-size: 20pt;">UNIVERSITAS NAHDLATUL ULAMA AL GHAZALI CILACAP</div>\n        <div style="font-size: 19pt;">FAKULTAS MATEMATIKA DAN ILMU KOMPUTER</div>\n        <div style="font-size: 18pt;">(FMIKOM)</div>\n    </div>\n    <p style="color: #00b050; font-size: 11pt; margin: 4px 0 6px; text-align: center;">\n        Keputusan Kemendikbud RI Nomor : 264/E/O/2014 Tanggal 23 Juli 2014\n    </p>\n    <div style="border-top: 2px solid #00b050; border-bottom: 1px solid #00b050; height: 3px;"></div>\n</section>\n<section class="surat surat-permohonan-cuti">\n    <h1 style="margin-bottom: 0; text-align: center;">PERMOHONAN CUTI MAHASISWA</h1>\n    <p style="margin-bottom: 10px; text-align: center;">Nomor: B/0088/CUTI-MHS/FMIKOM/2026</p>\n\n    <p style="margin-bottom: 8px; text-align: justify;">Yang bertanda tangan di bawah ini:</p>\n\n    <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px;">\n        <tbody>\n            <tr>\n                <td style="width: 126px; padding: 1px 0; vertical-align: top;">Nama</td>\n                <td style="width: 14px; padding: 1px 0; vertical-align: top;">:</td>\n                <td style="padding: 1px 0; vertical-align: top;">Admin</td>\n            </tr>\n            <tr>\n                <td style="padding: 1px 0; vertical-align: top;">NIM</td>\n                <td style="padding: 1px 0; vertical-align: top;">:</td>\n                <td style="padding: 1px 0; vertical-align: top;">22EO10001</td>\n            </tr>\n            <tr>\n                <td style="padding: 1px 0; vertical-align: top;">Semester</td>\n                <td style="padding: 1px 0; vertical-align: top;">:</td>\n                <td style="padding: 1px 0; vertical-align: top;">5 (Lima)</td>\n            </tr>\n            <tr>\n                <td style="padding: 1px 0; vertical-align: top;">Fak/Prodi</td>\n                <td style="padding: 1px 0; vertical-align: top;">:</td>\n                <td style="padding: 1px 0; vertical-align: top;">FMIKOM/Informatika</td>\n            </tr>\n        </tbody>\n    </table>\n\n    <p style="margin-bottom: 8px; text-align: justify;">\n        Dengan ini mengajukan permohonan cuti, dengan alasan jyyyyyyyy.\n    </p>\n\n    <p style="text-align: justify;">\n        Demikian permohonan ini saya sampaikan, atas perhatian dan terkabulnya\n        permohonan ini disampaikan terima kasih.\n    </p>\n\n    <div style="margin-top: 14px; width: 100%;">\n        <div style="margin-left: auto; width: 245px;">\n            <p style="margin-bottom: 0;">Cilacap, 28 April 2026</p>\n            <p style="margin-bottom: 28px;">Pemohon,</p>\n            <p style="margin-bottom: 0;"><strong>Admin</strong></p>\n            <p>NIM. 22EO10001</p>\n        </div>\n    </div>\n\n    <p style="margin-top: 10px; margin-bottom: 6px; text-align: center;">Mengetahui,</p>\n\n    <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">\n        <tbody>\n            <tr>\n                <td style="width: 50%; padding: 0 12px 0 0; vertical-align: top; text-align: center;">\n                    <p style="margin-bottom: 28px;">DPA</p>\n                    <p style="margin-bottom: 0;"><strong>Verry, M.Kom.</strong></p>\n                    <p>NIK. 41230714</p>\n                </td>\n                <td style="width: 50%; padding: 0 0 0 12px; vertical-align: top; text-align: center;">\n                    <p style="margin-bottom: 0;">Kaprodi Informatika</p>\n                    <p style="margin-bottom: 28px;">a.n Sekretaris Fakultas,</p>\n                    <p style="margin-bottom: 0;"><strong>Tri Anggoro, M.Kom.</strong></p>\n                    <p>NIK. 41230714268</p>\n                </td>\n            </tr>\n        </tbody>\n    </table>\n\n    <div>\n        <p style="margin-bottom: 4px;">Tembusan:</p>\n        <p style="margin-bottom: 0;">1. Ka. Prodi</p>\n        <p style="margin-bottom: 0;">2. Bagian Akademik</p>\n        <p style="margin-bottom: 0;">3. Bagian Keuangan</p>\n        <p style="margin-bottom: 0;">4. Fakultas</p>\n        <p style="margin-bottom: 0;">5. Mahasiswa</p>\n    </div>\n</section>', '2026-04-28 09:55:11', NULL, '2026-04-28 09:55:12', '2026-04-28 02:55:11', '2026-04-28 02:55:12', '41eebab4-575f-4a84-8c0c-4cec4871bf39'),
	(89, 9, 'surat_keluar', 5, 'B/0089/CUTI-MHS/FMIKOM/2026', 'tuntutan hidup', NULL, NULL, 'finished', '{"jenis_surat_id":9,"jenis_surat":"Permohonan Cuti Mahasiswa","keperluan":"tuntutan hidup","data":{"nim":"22EO10001","semester":"4","program_studi":"Informatika","tahun_akademik":"2026\\/2027","semester_pengajuan":"Ganjil","alasan_cuti":"kepooo luuu"},"created_by":{"id":5,"name":"Admin"}}', NULL, NULL, 5, '2026-04-28 10:02:17', NULL, NULL, 'fast/generated/surat-89-permohonan-cuti-mahasiswa.pdf', 'pdf', '2026-04-28 10:02:17', 1, 0, NULL, 0, NULL, '<section style="margin-bottom: 10px; text-align: center;">\n    <img src="data:image/png;base64,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" alt="Logo UNU Al Ghazali Cilacap" style="display: block; height: 70px; margin: 0 auto 2px;">\n    <div style="color: #00b050; font-family: \'Times New Roman\', serif; font-weight: 700; line-height: 1.04; text-align: center;">\n        <div style="font-size: 16pt;">UNIVERSITAS NAHDLATUL ULAMA AL GHAZALI</div>\n        <div style="font-size: 16pt;">CILACAP</div>\n        <div style="font-size: 14pt;">FAKULTAS MATEMATIKA DAN ILMU KOMPUTER</div>\n        <div style="font-size: 14pt;">(FMIKOM)</div>\n    </div>\n    <p style="color: #00b050; font-size: 10pt; line-height: 1.2; margin: 3px 0 5px; text-align: center;">\n        Keputusan Kemendikbud RI Nomor : 264/E/O/2014 Tanggal 23 Juli 2014\n    </p>\n    <div style="margin: 0 auto; width: 100%;">\n        <div style="border-top: 1.6px solid #00b050; margin-bottom: 1.5px;"></div>\n        <div style="border-top: 0.8px solid #00b050;"></div>\n    </div>\n</section>\n<section class="surat surat-permohonan-cuti">\n    <h1 style="margin-bottom: 0; text-align: center;">PERMOHONAN CUTI MAHASISWA</h1>\n    <p style="margin-bottom: 10px; text-align: center;">Nomor: B/0089/CUTI-MHS/FMIKOM/2026</p>\n\n    <p style="margin-bottom: 8px; text-align: justify;">Yang bertanda tangan di bawah ini:</p>\n\n    <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px;">\n        <tbody>\n            <tr>\n                <td style="width: 126px; padding: 1px 0; vertical-align: top;">Nama</td>\n                <td style="width: 14px; padding: 1px 0; vertical-align: top;">:</td>\n                <td style="padding: 1px 0; vertical-align: top;">Admin</td>\n            </tr>\n            <tr>\n                <td style="padding: 1px 0; vertical-align: top;">NIM</td>\n                <td style="padding: 1px 0; vertical-align: top;">:</td>\n                <td style="padding: 1px 0; vertical-align: top;">22EO10001</td>\n            </tr>\n            <tr>\n                <td style="padding: 1px 0; vertical-align: top;">Semester</td>\n                <td style="padding: 1px 0; vertical-align: top;">:</td>\n                <td style="padding: 1px 0; vertical-align: top;">4 (Empat)</td>\n            </tr>\n            <tr>\n                <td style="padding: 1px 0; vertical-align: top;">Fak/Prodi</td>\n                <td style="padding: 1px 0; vertical-align: top;">:</td>\n                <td style="padding: 1px 0; vertical-align: top;">FMIKOM/Informatika</td>\n            </tr>\n        </tbody>\n    </table>\n\n    <p style="margin-bottom: 8px; text-align: justify;">\n        Dengan ini mengajukan permohonan cuti, dengan alasan kepooo luuu.\n    </p>\n\n    <p style="text-align: justify;">\n        Demikian permohonan ini saya sampaikan, atas perhatian dan terkabulnya\n        permohonan ini disampaikan terima kasih.\n    </p>\n\n    <div style="margin-top: 14px; width: 100%;">\n        <div style="margin-left: auto; width: 245px;">\n            <p style="margin-bottom: 0;">Cilacap, 28 April 2026</p>\n            <p style="margin-bottom: 28px;">Pemohon,</p>\n            <p style="margin-bottom: 0;"><strong>Admin</strong></p>\n            <p>NIM. 22EO10001</p>\n        </div>\n    </div>\n\n    <p style="margin-top: 10px; margin-bottom: 6px; text-align: center;">Mengetahui,</p>\n\n    <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">\n        <tbody>\n            <tr>\n                <td style="width: 50%; padding: 0 12px 0 0; vertical-align: top; text-align: center;">\n                    <p style="margin-bottom: 28px;">DPA</p>\n                    <p style="margin-bottom: 0;"><strong>Verry, M.Kom.</strong></p>\n                    <p>NIK. 41230714</p>\n                </td>\n                <td style="width: 50%; padding: 0 0 0 12px; vertical-align: top; text-align: center;">\n                    <p style="margin-bottom: 0;">Kaprodi Informatika</p>\n                    <p style="margin-bottom: 28px;">a.n Sekretaris Fakultas,</p>\n                    <p style="margin-bottom: 0;"><strong>Tri Anggoro, M.Kom.</strong></p>\n                    <p>NIK. 41230714268</p>\n                </td>\n            </tr>\n        </tbody>\n    </table>\n\n    <div>\n        <p style="margin-bottom: 4px;">Tembusan:</p>\n        <p style="margin-bottom: 0;">1. Ka. Prodi</p>\n        <p style="margin-bottom: 0;">2. Bagian Akademik</p>\n        <p style="margin-bottom: 0;">3. Bagian Keuangan</p>\n        <p style="margin-bottom: 0;">4. Fakultas</p>\n        <p style="margin-bottom: 0;">5. Mahasiswa</p>\n    </div>\n</section>', '2026-04-28 10:02:17', NULL, '2026-04-28 10:02:17', '2026-04-28 03:02:17', '2026-04-28 03:02:17', '2e9e5101-9ab5-4fca-bca0-bbec7f22462e'),
	(90, 9, 'surat_keluar', 5, 'B/0090/CUTI-MHS/FMIKOM/2026', 'tuntutan hidup', NULL, NULL, 'finished', '{"jenis_surat_id":9,"jenis_surat":"Permohonan Cuti Mahasiswa","keperluan":"tuntutan hidup","data":{"nim":"22EO10001","semester":"2","program_studi":"Informatika","tahun_akademik":"2026\\/2027","semester_pengajuan":"Ganjil","alasan_cuti":"jhbjhbhjbjh"},"created_by":{"id":5,"name":"Admin"}}', NULL, NULL, 5, '2026-04-29 15:17:44', NULL, NULL, 'fast/generated/surat-90-permohonan-cuti-mahasiswa.pdf', 'pdf', '2026-04-29 15:17:55', 1, 0, NULL, 0, NULL, '<section style="margin-bottom: 10px; text-align: center;">\n    <img src="data:image/png;base64,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" alt="Logo UNU Al Ghazali Cilacap" style="display: block; height: 70px; margin: 0 auto 2px;">\n    <div style="color: #00b050; font-family: \'Times New Roman\', serif; font-weight: 700; line-height: 1.04; text-align: center;">\n        <div style="font-size: 16pt;">UNUGHA CILACAP</div>\n        <div style="font-size: 14pt;">FAKULTAS MATEMATIKA DAN ILMU KOMPUTER</div>\n        <div style="font-size: 14pt;">(FMIKOM)</div>\n    </div>\n    <p style="color: #00b050; font-size: 10pt; line-height: 1.2; margin: 3px 0 5px; text-align: center;">\n        Keputusan Kemendikbud RI Nomor : 264/E/O/2014 Tanggal 23 Juli 2014\n    </p>\n    <div style="margin: 0 auto; width: 100%;">\n        <div style="border-top: 1.6px solid #00b050; margin-bottom: 1.5px;"></div>\n        <div style="border-top: 0.8px solid #00b050;"></div>\n    </div>\n</section>\n<section class="surat surat-permohonan-cuti">\n    <h1 style="margin-bottom: 0; text-align: center;">PERMOHONAN CUTI MAHASISWA</h1>\n    <p style="margin-bottom: 10px; text-align: center;">Nomor: B/0090/CUTI-MHS/FMIKOM/2026</p>\n\n    <p style="margin-bottom: 8px; text-align: justify;">Yang bertanda tangan di bawah ini:</p>\n\n    <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px;">\n        <tbody>\n            <tr>\n                <td style="width: 126px; padding: 1px 0; vertical-align: top;">Nama</td>\n                <td style="width: 14px; padding: 1px 0; vertical-align: top;">:</td>\n                <td style="padding: 1px 0; vertical-align: top;">Admin</td>\n            </tr>\n            <tr>\n                <td style="padding: 1px 0; vertical-align: top;">NIM</td>\n                <td style="padding: 1px 0; vertical-align: top;">:</td>\n                <td style="padding: 1px 0; vertical-align: top;">22EO10001</td>\n            </tr>\n            <tr>\n                <td style="padding: 1px 0; vertical-align: top;">Semester</td>\n                <td style="padding: 1px 0; vertical-align: top;">:</td>\n                <td style="padding: 1px 0; vertical-align: top;">2 (Dua)</td>\n            </tr>\n            <tr>\n                <td style="padding: 1px 0; vertical-align: top;">Fak/Prodi</td>\n                <td style="padding: 1px 0; vertical-align: top;">:</td>\n                <td style="padding: 1px 0; vertical-align: top;">FMIKOM/Informatika</td>\n            </tr>\n        </tbody>\n    </table>\n\n    <p style="margin-bottom: 8px; text-align: justify;">\n        Dengan ini mengajukan permohonan cuti, dengan alasan jhbjhbhjbjh.\n    </p>\n\n    <p style="text-align: justify;">\n        Demikian permohonan ini saya sampaikan, atas perhatian dan terkabulnya\n        permohonan ini disampaikan terima kasih.\n    </p>\n\n    <div style="margin-top: 8px; width: 100%;">\n        <div style="margin-left: auto; width: 220px;">\n            <p style="margin-bottom: 0;">Cilacap, 29 April 2026</p>\n            <p style="margin-bottom: 40px;">Pemohon,</p>\n            <p style="margin-bottom: 0;"><strong>Admin</strong></p>\n            <p>NIM. 22EO10001</p>\n        </div>\n    </div>\n\n    <p style="margin-top: 2px; margin-bottom: 2px; text-align: center;">Mengetahui,</p>\n\n    <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px;">\n        <tbody>\n            <tr>\n                <td style="width: 50%; padding: 0 12px 0 0; vertical-align: top; text-align: center;">\n                    <div style="height: 30px; margin-bottom: 34px;">DPA</div>\n                    <p style="margin-bottom: 0;"><strong>Verry, M.Kom.</strong></p>\n                    <p>NIK. 41230714</p>\n                </td>\n                <td style="width: 50%; padding: 0 0 0 12px; vertical-align: top; text-align: center;">\n                    <div style="height: 30px; margin-bottom: 34px;">\n                        <div>Kaprodi Informatika</div>\n                        <div>a.n Sekretaris Fakultas,</div>\n                    </div>\n                    <p style="margin-bottom: 0;"><strong>Tri Anggoro, M.Kom.</strong></p>\n                    <p>NIK. 41230714268</p>\n                </td>\n            </tr>\n        </tbody>\n    </table>\n\n    <div style="font-size: 10.5pt; line-height: 1.2;">\n        <p style="margin-bottom: 2px;">Tembusan:</p>\n        <p style="margin-bottom: 0;">1. Ka. Prodi</p>\n        <p style="margin-bottom: 0;">2. Bagian Akademik</p>\n        <p style="margin-bottom: 0;">3. Bagian Keuangan</p>\n        <p style="margin-bottom: 0;">4. Fakultas</p>\n        <p style="margin-bottom: 0;">5. Mahasiswa</p>\n    </div>\n</section>', '2026-04-29 15:17:44', NULL, '2026-04-29 15:17:55', '2026-04-29 08:17:44', '2026-04-29 08:17:55', '627a9564-aaeb-4ec4-968a-564bb6cea507'),
	(91, 11, 'surat_keluar', 5, 'B/0091/DISPENSASI-MHS/FMIKOM/2026', 'gatauuuu', NULL, NULL, 'validated_admin', '{"jenis_surat_id":11,"jenis_surat":"Permohonan Dispensasi Mahasiswa","keperluan":"gatauuuu","data":{"nim":"22EO10001","program_studi":"Informatika","mata_kuliah":"bbbbbbbbbbbbb","kelas":"IF-8A","dosen_pengampu":"bbbbbbbbbbbb","tanggal_kegiatan":"2026-04-29","alasan_dispensasi":"yjhgjufdtrgsersre"},"created_by":{"id":5,"name":"Admin"}}', NULL, NULL, 5, '2026-04-29 15:26:49', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, '2026-04-29 15:26:49', NULL, NULL, '2026-04-29 08:26:49', '2026-04-29 08:26:49', '72d77cea-282d-4ab5-a6d6-4adcf57e09d0'),
	(92, 13, 'surat_keluar', 5, 'B/0092/PENGUJI-SIDANG/FMIKOM/2026', 'tuntutan hidup', NULL, NULL, 'validated_admin', '{"jenis_surat_id":13,"jenis_surat":"Permohonan Menjadi Penguji Sidang","keperluan":"tuntutan hidup","data":{"nip":"22329483","nama_dosen":"fulan","nama_mahasiswa":"kkkk","nim_mahasiswa":"22wrre545","judul_skripsi":"kuali merah putih","tanggal_sidang":"2026-04-30","ruang_sidang":"1"},"created_by":{"id":5,"name":"Admin"}}', NULL, NULL, 5, '2026-04-29 15:28:38', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, '2026-04-29 15:28:38', NULL, NULL, '2026-04-29 08:28:38', '2026-04-29 08:28:38', 'f7e107c0-412e-48a0-8a18-f7d3bb611fa1'),
	(93, 9, 'surat_keluar', 5, 'B/0093/CUTI-MHS/FMIKOM/2026', 'tuntutan hidup', NULL, NULL, 'validated_admin', '{"jenis_surat_id":9,"jenis_surat":"Permohonan Cuti Mahasiswa","keperluan":"tuntutan hidup","data":{"nim":"22EO10001","semester":"3","program_studi":"Informatika","tahun_akademik":"2026\\/2027","semester_pengajuan":"Genap","alasan_cuti":"refvrrrrerfre"},"created_by":{"id":5,"name":"Admin"}}', NULL, NULL, 5, '2026-04-29 16:27:18', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, '2026-04-29 16:27:18', NULL, NULL, '2026-04-29 09:27:18', '2026-04-29 09:27:18', '39a6af7f-3270-4b09-b64a-336b4091d598'),
	(94, 9, 'surat_keluar', 5, 'B/0094/CUTI-MHS/FMIKOM/2026', 'tuntutan hidup', NULL, NULL, 'validated_admin', '{"jenis_surat_id":9,"jenis_surat":"Permohonan Cuti Mahasiswa","keperluan":"tuntutan hidup","data":{"nim":"22EO10001","semester":"3","program_studi":"Informatika","tahun_akademik":"2026\\/2027","semester_pengajuan":"Genap","alasan_cuti":"refvrrrrerfre"},"created_by":{"id":5,"name":"Admin"}}', NULL, NULL, 5, '2026-04-29 16:28:46', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, '2026-04-29 16:28:46', NULL, NULL, '2026-04-29 09:28:46', '2026-04-29 09:28:46', 'cf9aa333-6f22-4e90-893c-f94417949df9'),
	(95, 9, 'surat_keluar', 5, 'B/0095/CUTI-MHS/FMIKOM/2026', 'tuntutan hidup', NULL, NULL, 'validated_admin', '{"jenis_surat_id":9,"jenis_surat":"Permohonan Cuti Mahasiswa","keperluan":"tuntutan hidup","data":{"nim":"22EO10001","semester":"3","program_studi":"Informatika","tahun_akademik":"2026\\/2027","semester_pengajuan":"Genap","alasan_cuti":"refvrrrrerfre"},"created_by":{"id":5,"name":"Admin"}}', NULL, NULL, 5, '2026-04-29 16:31:41', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, '2026-04-29 16:31:41', NULL, NULL, '2026-04-29 09:31:41', '2026-04-29 09:31:41', 'dfb29ead-19eb-4372-a461-55bb0e656f47'),
	(96, 9, 'surat_keluar', 5, 'B/0096/CUTI-MHS/FMIKOM/2026', 'tuntutan hidup', NULL, NULL, 'finished', '{"jenis_surat_id":9,"jenis_surat":"Permohonan Cuti Mahasiswa","keperluan":"tuntutan hidup","data":{"nim":"22EO10001","semester":"3","program_studi":"Informatika","tahun_akademik":"2026\\/2027","semester_pengajuan":"Genap","alasan_cuti":"refvrrrrerfre"},"created_by":{"id":5,"name":"Admin"}}', NULL, NULL, 5, '2026-04-29 16:35:17', NULL, NULL, 'fast/generated/surat-96-permohonan-cuti-mahasiswa.pdf', 'pdf', '2026-04-29 16:35:18', 1, 0, NULL, 0, NULL, '<section style="margin-bottom: 10px; text-align: center;">\n    <img src="data:image/png;base64,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" alt="Logo UNU Al Ghazali Cilacap" style="display: block; height: 70px; margin: 0 auto 2px;">\n    <div style="color: #00b050; font-family: \'Times New Roman\', serif; font-weight: 700; line-height: 1.04; text-align: center;">\n        <div style="font-size: 16pt;">UNUGHA CILACAP</div>\n        <div style="font-size: 14pt;">FAKULTAS MATEMATIKA DAN ILMU KOMPUTER</div>\n        <div style="font-size: 14pt;">(FMIKOM)</div>\n    </div>\n    <p style="color: #00b050; font-size: 10pt; line-height: 1.2; margin: 3px 0 5px; text-align: center;">\n        Keputusan Kemendikbud RI Nomor : 264/E/O/2014 Tanggal 23 Juli 2014\n    </p>\n    <div style="margin: 0 auto; width: 100%;">\n        <div style="border-top: 1.6px solid #00b050; margin-bottom: 1.5px;"></div>\n        <div style="border-top: 0.8px solid #00b050;"></div>\n    </div>\n</section>\n<section class="surat surat-permohonan-cuti">\n    <h1 style="margin-bottom: 0; text-align: center;">PERMOHONAN CUTI MAHASISWA</h1>\n    <p style="margin-bottom: 10px; text-align: center;">Nomor: B/0096/CUTI-MHS/FMIKOM/2026</p>\n\n    <p style="margin-bottom: 8px; text-align: justify;">Yang bertanda tangan di bawah ini:</p>\n\n    <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px;">\n        <tbody>\n            <tr>\n                <td style="width: 126px; padding: 1px 0; vertical-align: top;">Nama</td>\n                <td style="width: 14px; padding: 1px 0; vertical-align: top;">:</td>\n                <td style="padding: 1px 0; vertical-align: top;">Admin</td>\n            </tr>\n            <tr>\n                <td style="padding: 1px 0; vertical-align: top;">NIM</td>\n                <td style="padding: 1px 0; vertical-align: top;">:</td>\n                <td style="padding: 1px 0; vertical-align: top;">22EO10001</td>\n            </tr>\n            <tr>\n                <td style="padding: 1px 0; vertical-align: top;">Semester</td>\n                <td style="padding: 1px 0; vertical-align: top;">:</td>\n                <td style="padding: 1px 0; vertical-align: top;">3 (Tiga)</td>\n            </tr>\n            <tr>\n                <td style="padding: 1px 0; vertical-align: top;">Fak/Prodi</td>\n                <td style="padding: 1px 0; vertical-align: top;">:</td>\n                <td style="padding: 1px 0; vertical-align: top;">FMIKOM/Informatika</td>\n            </tr>\n        </tbody>\n    </table>\n\n    <p style="margin-bottom: 8px; text-align: justify;">\n        Dengan ini mengajukan permohonan cuti, dengan alasan refvrrrrerfre.\n    </p>\n\n    <p style="text-align: justify;">\n        Demikian permohonan ini saya sampaikan, atas perhatian dan terkabulnya\n        permohonan ini disampaikan terima kasih.\n    </p>\n\n    <div style="margin-top: 8px; width: 100%;">\n        <div style="margin-left: auto; width: 220px;">\n            <p style="margin-bottom: 0;">Cilacap, 29 April 2026</p>\n            <p style="margin-bottom: 40px;">Pemohon,</p>\n            <p style="margin-bottom: 0;"><strong>Admin</strong></p>\n            <p>NIM. 22EO10001</p>\n        </div>\n    </div>\n\n    <p style="margin-top: 2px; margin-bottom: 2px; text-align: center;">Mengetahui,</p>\n\n    <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px;">\n        <tbody>\n            <tr>\n                <td style="width: 50%; padding: 0 12px 0 0; vertical-align: top; text-align: center;">\n                    <div style="height: 30px; margin-bottom: 34px;">DPA</div>\n                    <p style="margin-bottom: 0;"><strong>Verry, M.Kom.</strong></p>\n                    <p>NIK. 41230714</p>\n                </td>\n                <td style="width: 50%; padding: 0 0 0 12px; vertical-align: top; text-align: center;">\n                    <div style="height: 30px; margin-bottom: 34px;">\n                        <div>Kaprodi Informatika</div>\n                        <div>a.n Sekretaris Fakultas,</div>\n                    </div>\n                    <p style="margin-bottom: 0;"><strong>Tri Anggoro, M.Kom.</strong></p>\n                    <p>NIK. 41230714268</p>\n                </td>\n            </tr>\n        </tbody>\n    </table>\n\n    <div style="font-size: 10.5pt; line-height: 1.2;">\n        <p style="margin-bottom: 2px;">Tembusan:</p>\n        <p style="margin-bottom: 0;">1. Ka. Prodi</p>\n        <p style="margin-bottom: 0;">2. Bagian Akademik</p>\n        <p style="margin-bottom: 0;">3. Bagian Keuangan</p>\n        <p style="margin-bottom: 0;">4. Fakultas</p>\n        <p style="margin-bottom: 0;">5. Mahasiswa</p>\n    </div>\n</section>', '2026-04-29 16:35:17', NULL, '2026-04-29 16:35:18', '2026-04-29 09:35:17', '2026-04-29 09:35:18', '9df5441d-af1a-45e4-aaa5-9ff9f69b5398');

-- Dumping structure for table portal-fmikom.surat_approval_flows
DROP TABLE IF EXISTS `surat_approval_flows`;
CREATE TABLE IF NOT EXISTS `surat_approval_flows` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `surat_id` bigint unsigned NOT NULL,
  `approver_id` bigint unsigned NOT NULL,
  `urutan` int NOT NULL,
  `role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `keterangan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `tanggal_aksi` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `surat_approval_flows_surat_id_foreign` (`surat_id`),
  KEY `surat_approval_flows_approver_id_foreign` (`approver_id`),
  CONSTRAINT `surat_approval_flows_approver_id_foreign` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `surat_approval_flows_surat_id_foreign` FOREIGN KEY (`surat_id`) REFERENCES `surats` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.surat_approval_flows: ~1 rows (approximately)
INSERT INTO `surat_approval_flows` (`id`, `surat_id`, `approver_id`, `urutan`, `role`, `status`, `keterangan`, `approved_at`, `catatan`, `tanggal_aksi`, `created_at`, `updated_at`) VALUES
	(34, 81, 5, 1, 'admin', 'approved', 'Validasi Admin', '2026-04-28 05:07:25', NULL, '2026-04-28 05:07:25', '2026-04-27 22:07:25', '2026-04-27 22:07:25');

-- Dumping structure for table portal-fmikom.surat_categories
DROP TABLE IF EXISTS `surat_categories`;
CREATE TABLE IF NOT EXISTS `surat_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Lucide icon name, e.g. mail, file-text',
  `warna` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tailwind color key, e.g. indigo, emerald, amber',
  `urutan` int unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `surat_categories_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.surat_categories: ~11 rows (approximately)
INSERT INTO `surat_categories` (`id`, `nama`, `slug`, `deskripsi`, `icon`, `warna`, `urutan`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 'Surat Permohonan', 'surat-permohonan', 'Surat yang berisi permintaan resmi dari mahasiswa atau dosen.', NULL, NULL, 1, 1, '2026-04-26 23:28:31', '2026-04-26 23:28:31'),
	(2, 'Surat Keterangan', 'surat-keterangan', 'Surat yang menerangkan status atau kondisi akademik.', NULL, NULL, 2, 1, '2026-04-26 23:28:31', '2026-04-26 23:28:31'),
	(3, 'Surat Tugas', 'surat-tugas', 'Surat penugasan resmi untuk dosen atau staf.', NULL, NULL, 3, 1, '2026-04-26 23:28:31', '2026-04-26 23:28:31'),
	(4, 'Surat Undangan', 'surat-undangan', 'Surat undangan rapat atau kegiatan resmi.', NULL, NULL, 4, 1, '2026-04-26 23:28:31', '2026-04-26 23:28:31'),
	(5, 'Surat Pengantar', 'surat-pengantar', 'Surat pengantar ke instansi atau pihak lain.', NULL, NULL, 5, 1, '2026-04-26 23:28:31', '2026-04-26 23:28:31'),
	(6, 'Surat Rekomendasi', 'surat-rekomendasi', 'Surat rekomendasi akademik atau non-akademik.', NULL, NULL, 6, 1, '2026-04-26 23:28:31', '2026-04-26 23:28:31'),
	(7, 'Surat Keputusan', 'surat-keputusan', 'Dokumen keputusan resmi lembaga.', NULL, NULL, 7, 1, '2026-04-26 23:28:31', '2026-04-26 23:28:31'),
	(8, 'Surat Edaran', 'surat-edaran', 'Penyampaian informasi resmi yang bersifat umum.', NULL, NULL, 8, 1, '2026-04-26 23:28:31', '2026-04-26 23:28:31'),
	(9, 'Surat Pemberitahuan', 'surat-pemberitahuan', 'Informasi resmi yang lebih spesifik kepada penerima.', NULL, NULL, 9, 1, '2026-04-26 23:28:31', '2026-04-26 23:28:31'),
	(10, 'Memo / Nota Dinas', 'memo-nota-dinas', 'Komunikasi internal formal untuk kebutuhan administratif.', NULL, NULL, 10, 1, '2026-04-26 23:28:31', '2026-04-26 23:28:31'),
	(11, 'Pengumuman', 'pengumuman', 'Informasi publik internal kampus.', NULL, NULL, 11, 1, '2026-04-26 23:28:31', '2026-04-26 23:28:31');

-- Dumping structure for table portal-fmikom.surat_data
DROP TABLE IF EXISTS `surat_data`;
CREATE TABLE IF NOT EXISTS `surat_data` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `surat_id` bigint unsigned NOT NULL,
  `field_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `field_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `surat_data_surat_id_field_name_unique` (`surat_id`,`field_name`),
  CONSTRAINT `surat_data_surat_id_foreign` FOREIGN KEY (`surat_id`) REFERENCES `surats` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=450 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.surat_data: ~68 rows (approximately)
INSERT INTO `surat_data` (`id`, `surat_id`, `field_name`, `field_value`, `created_at`, `updated_at`) VALUES
	(352, 81, 'nim', '22EO10001', '2026-04-27 21:46:13', '2026-04-27 21:46:13'),
	(353, 81, 'semester', '4', '2026-04-27 21:46:13', '2026-04-27 21:46:13'),
	(354, 81, 'program_studi', 'Informatika', '2026-04-27 21:46:13', '2026-04-27 21:46:13'),
	(355, 81, 'tahun_akademik', '2026/2027', '2026-04-27 21:46:13', '2026-04-27 21:46:13'),
	(356, 81, 'semester_pengajuan', 'Ganjil', '2026-04-27 21:46:13', '2026-04-27 21:46:13'),
	(357, 81, 'alasan_cuti', 'Kerja bu', '2026-04-27 21:46:13', '2026-04-27 21:46:13'),
	(388, 87, 'nim', '22EO10001', '2026-04-28 02:45:25', '2026-04-28 02:45:25'),
	(389, 87, 'semester', '4', '2026-04-28 02:45:25', '2026-04-28 02:45:25'),
	(390, 87, 'program_studi', 'Informatika', '2026-04-28 02:45:25', '2026-04-28 02:45:25'),
	(391, 87, 'tahun_akademik', '2026/2027', '2026-04-28 02:45:25', '2026-04-28 02:45:25'),
	(392, 87, 'semester_pengajuan', 'Genap', '2026-04-28 02:45:25', '2026-04-28 02:45:25'),
	(393, 87, 'alasan_cuti', 'jiaaaaaa', '2026-04-28 02:45:25', '2026-04-28 02:45:25'),
	(394, 88, 'nim', '22EO10001', '2026-04-28 02:55:11', '2026-04-28 02:55:11'),
	(395, 88, 'semester', '5', '2026-04-28 02:55:11', '2026-04-28 02:55:11'),
	(396, 88, 'program_studi', 'Informatika', '2026-04-28 02:55:11', '2026-04-28 02:55:11'),
	(397, 88, 'tahun_akademik', '2026/2027', '2026-04-28 02:55:11', '2026-04-28 02:55:11'),
	(398, 88, 'semester_pengajuan', 'Ganjil', '2026-04-28 02:55:11', '2026-04-28 02:55:11'),
	(399, 88, 'alasan_cuti', 'jyyyyyyyy', '2026-04-28 02:55:11', '2026-04-28 02:55:11'),
	(400, 89, 'nim', '22EO10001', '2026-04-28 03:02:17', '2026-04-28 03:02:17'),
	(401, 89, 'semester', '4', '2026-04-28 03:02:17', '2026-04-28 03:02:17'),
	(402, 89, 'program_studi', 'Informatika', '2026-04-28 03:02:17', '2026-04-28 03:02:17'),
	(403, 89, 'tahun_akademik', '2026/2027', '2026-04-28 03:02:17', '2026-04-28 03:02:17'),
	(404, 89, 'semester_pengajuan', 'Ganjil', '2026-04-28 03:02:17', '2026-04-28 03:02:17'),
	(405, 89, 'alasan_cuti', 'kepooo luuu', '2026-04-28 03:02:17', '2026-04-28 03:02:17'),
	(406, 90, 'nim', '22EO10001', '2026-04-29 08:17:44', '2026-04-29 08:17:44'),
	(407, 90, 'semester', '2', '2026-04-29 08:17:44', '2026-04-29 08:17:44'),
	(408, 90, 'program_studi', 'Informatika', '2026-04-29 08:17:44', '2026-04-29 08:17:44'),
	(409, 90, 'tahun_akademik', '2026/2027', '2026-04-29 08:17:44', '2026-04-29 08:17:44'),
	(410, 90, 'semester_pengajuan', 'Ganjil', '2026-04-29 08:17:44', '2026-04-29 08:17:44'),
	(411, 90, 'alasan_cuti', 'jhbjhbhjbjh', '2026-04-29 08:17:44', '2026-04-29 08:17:44'),
	(412, 91, 'nim', '22EO10001', '2026-04-29 08:26:49', '2026-04-29 08:26:49'),
	(413, 91, 'program_studi', 'Informatika', '2026-04-29 08:26:49', '2026-04-29 08:26:49'),
	(414, 91, 'mata_kuliah', 'bbbbbbbbbbbbb', '2026-04-29 08:26:49', '2026-04-29 08:26:49'),
	(415, 91, 'kelas', 'IF-8A', '2026-04-29 08:26:49', '2026-04-29 08:26:49'),
	(416, 91, 'dosen_pengampu', 'bbbbbbbbbbbb', '2026-04-29 08:26:49', '2026-04-29 08:26:49'),
	(417, 91, 'tanggal_kegiatan', '2026-04-29', '2026-04-29 08:26:49', '2026-04-29 08:26:49'),
	(418, 91, 'alasan_dispensasi', 'yjhgjufdtrgsersre', '2026-04-29 08:26:49', '2026-04-29 08:26:49'),
	(419, 92, 'nip', '22329483', '2026-04-29 08:28:38', '2026-04-29 08:28:38'),
	(420, 92, 'nama_dosen', 'fulan', '2026-04-29 08:28:38', '2026-04-29 08:28:38'),
	(421, 92, 'nama_mahasiswa', 'kkkk', '2026-04-29 08:28:38', '2026-04-29 08:28:38'),
	(422, 92, 'nim_mahasiswa', '22wrre545', '2026-04-29 08:28:38', '2026-04-29 08:28:38'),
	(423, 92, 'judul_skripsi', 'kuali merah putih', '2026-04-29 08:28:38', '2026-04-29 08:28:38'),
	(424, 92, 'tanggal_sidang', '2026-04-30', '2026-04-29 08:28:38', '2026-04-29 08:28:38'),
	(425, 92, 'ruang_sidang', '1', '2026-04-29 08:28:38', '2026-04-29 08:28:38'),
	(426, 93, 'nim', '22EO10001', '2026-04-29 09:27:18', '2026-04-29 09:27:18'),
	(427, 93, 'semester', '3', '2026-04-29 09:27:18', '2026-04-29 09:27:18'),
	(428, 93, 'program_studi', 'Informatika', '2026-04-29 09:27:18', '2026-04-29 09:27:18'),
	(429, 93, 'tahun_akademik', '2026/2027', '2026-04-29 09:27:18', '2026-04-29 09:27:18'),
	(430, 93, 'semester_pengajuan', 'Genap', '2026-04-29 09:27:18', '2026-04-29 09:27:18'),
	(431, 93, 'alasan_cuti', 'refvrrrrerfre', '2026-04-29 09:27:18', '2026-04-29 09:27:18'),
	(432, 94, 'nim', '22EO10001', '2026-04-29 09:28:46', '2026-04-29 09:28:46'),
	(433, 94, 'semester', '3', '2026-04-29 09:28:46', '2026-04-29 09:28:46'),
	(434, 94, 'program_studi', 'Informatika', '2026-04-29 09:28:46', '2026-04-29 09:28:46'),
	(435, 94, 'tahun_akademik', '2026/2027', '2026-04-29 09:28:46', '2026-04-29 09:28:46'),
	(436, 94, 'semester_pengajuan', 'Genap', '2026-04-29 09:28:46', '2026-04-29 09:28:46'),
	(437, 94, 'alasan_cuti', 'refvrrrrerfre', '2026-04-29 09:28:46', '2026-04-29 09:28:46'),
	(438, 95, 'nim', '22EO10001', '2026-04-29 09:31:41', '2026-04-29 09:31:41'),
	(439, 95, 'semester', '3', '2026-04-29 09:31:41', '2026-04-29 09:31:41'),
	(440, 95, 'program_studi', 'Informatika', '2026-04-29 09:31:41', '2026-04-29 09:31:41'),
	(441, 95, 'tahun_akademik', '2026/2027', '2026-04-29 09:31:41', '2026-04-29 09:31:41'),
	(442, 95, 'semester_pengajuan', 'Genap', '2026-04-29 09:31:41', '2026-04-29 09:31:41'),
	(443, 95, 'alasan_cuti', 'refvrrrrerfre', '2026-04-29 09:31:41', '2026-04-29 09:31:41'),
	(444, 96, 'nim', '22EO10001', '2026-04-29 09:35:17', '2026-04-29 09:35:17'),
	(445, 96, 'semester', '3', '2026-04-29 09:35:17', '2026-04-29 09:35:17'),
	(446, 96, 'program_studi', 'Informatika', '2026-04-29 09:35:17', '2026-04-29 09:35:17'),
	(447, 96, 'tahun_akademik', '2026/2027', '2026-04-29 09:35:17', '2026-04-29 09:35:17'),
	(448, 96, 'semester_pengajuan', 'Genap', '2026-04-29 09:35:17', '2026-04-29 09:35:17'),
	(449, 96, 'alasan_cuti', 'refvrrrrerfre', '2026-04-29 09:35:17', '2026-04-29 09:35:17');

-- Dumping structure for table portal-fmikom.surat_histories
DROP TABLE IF EXISTS `surat_histories`;
CREATE TABLE IF NOT EXISTS `surat_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `surat_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'created|submitted|validated|approved|rejected|generated|printed|qr_scanned|qr_revoked|revised',
  `action_label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Label ramah: Surat dibuat, Surat disetujui Kaprodi',
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `meta` json DEFAULT NULL COMMENT 'Data tambahan: {status_lama, status_baru, ...}',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `surat_histories_surat_id_created_at_index` (`surat_id`,`created_at`),
  KEY `surat_histories_user_id_index` (`user_id`),
  CONSTRAINT `surat_histories_surat_id_foreign` FOREIGN KEY (`surat_id`) REFERENCES `surats` (`id`) ON DELETE CASCADE,
  CONSTRAINT `surat_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.surat_histories: ~0 rows (approximately)

-- Dumping structure for table portal-fmikom.surat_lampirans
DROP TABLE IF EXISTS `surat_lampirans`;
CREATE TABLE IF NOT EXISTS `surat_lampirans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `surat_id` bigint unsigned NOT NULL,
  `nama_file` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipe` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `surat_lampirans_surat_id_foreign` (`surat_id`),
  CONSTRAINT `surat_lampirans_surat_id_foreign` FOREIGN KEY (`surat_id`) REFERENCES `surats` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.surat_lampirans: ~1 rows (approximately)
INSERT INTO `surat_lampirans` (`id`, `surat_id`, `nama_file`, `file_path`, `tipe`, `created_at`, `updated_at`) VALUES
	(21, 81, '(2019) Analisis Implementasi Tata Naskah Dinas dalam Pembuatan Surat Dinas di Lingkungan Universitas Gadjah Mada (UGM).pdf', 'surat-lampirans/6KnoL1B7xtqw5DZKG2UTsjp7SMyKf09Vf1EGPGXZ.pdf', 'application/pdf', '2026-04-27 21:46:13', '2026-04-27 21:46:13');

-- Dumping structure for table portal-fmikom.surat_qr_codes
DROP TABLE IF EXISTS `surat_qr_codes`;
CREATE TABLE IF NOT EXISTS `surat_qr_codes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `surat_id` bigint unsigned NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID token QR',
  `status` enum('active','revoked','expired') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `revoked_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `revoked_by` bigint unsigned DEFAULT NULL,
  `revoked_at` timestamp NULL DEFAULT NULL,
  `activated_at` timestamp NULL DEFAULT NULL COMMENT 'Kapan QR mulai aktif — null jika belum disetujui',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `surat_qr_codes_token_unique` (`token`),
  KEY `surat_qr_codes_revoked_by_foreign` (`revoked_by`),
  KEY `surat_qr_codes_token_status_index` (`token`,`status`),
  KEY `surat_qr_codes_surat_id_index` (`surat_id`),
  CONSTRAINT `surat_qr_codes_revoked_by_foreign` FOREIGN KEY (`revoked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `surat_qr_codes_surat_id_foreign` FOREIGN KEY (`surat_id`) REFERENCES `surats` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.surat_qr_codes: ~0 rows (approximately)

-- Dumping structure for table portal-fmikom.surat_templates
DROP TABLE IF EXISTS `surat_templates`;
CREATE TABLE IF NOT EXISTS `surat_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `jenis_surat_id` bigint unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `format` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'html',
  `source_reference` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `docx_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Path file .docx yang diupload admin',
  `kop_surat` text COLLATE utf8mb4_unicode_ci COMMENT 'HTML kop override (null = pakai global settings)',
  `footer_surat` text COLLATE utf8mb4_unicode_ci COMMENT 'HTML footer override',
  `css_style` text COLLATE utf8mb4_unicode_ci COMMENT 'CSS kustom untuk template ini',
  `subject` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `template_header` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `template_body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `template_footer` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `version` int unsigned NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `surat_templates_slug_unique` (`slug`),
  KEY `surat_templates_jenis_surat_id_foreign` (`jenis_surat_id`),
  KEY `surat_templates_created_by_foreign` (`created_by`),
  KEY `surat_templates_updated_by_foreign` (`updated_by`),
  CONSTRAINT `surat_templates_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `surat_templates_jenis_surat_id_foreign` FOREIGN KEY (`jenis_surat_id`) REFERENCES `jenis_surats` (`id`) ON DELETE CASCADE,
  CONSTRAINT `surat_templates_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.surat_templates: ~6 rows (approximately)
INSERT INTO `surat_templates` (`id`, `jenis_surat_id`, `name`, `deskripsi`, `slug`, `format`, `source_reference`, `docx_path`, `kop_surat`, `footer_surat`, `css_style`, `subject`, `template_header`, `template_body`, `template_footer`, `version`, `is_active`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
	(1, 9, 'Template Permohonan Cuti Mahasiswa', NULL, 'template-permohonan-cuti-mahasiswa', 'html', '[Template] Permohonan Cuti Mahasiswa.docx', NULL, NULL, NULL, NULL, 'Permohonan Cuti Mahasiswa', '<section style="margin-bottom: 10px; text-align: center;">\n    <img src="{{kop_logo_data_uri}}" alt="Logo UNU Al Ghazali Cilacap" style="display: block; height: 70px; margin: 0 auto 2px;">\n    <div style="color: #00b050; font-family: \'Times New Roman\', serif; font-weight: 700; line-height: 1.04; text-align: center;">\n        <div style="font-size: 16pt;">UNUGHA CILACAP</div>\n        <div style="font-size: 14pt;">FAKULTAS MATEMATIKA DAN ILMU KOMPUTER</div>\n        <div style="font-size: 14pt;">(FMIKOM)</div>\n    </div>\n    <p style="color: #00b050; font-size: 10pt; line-height: 1.2; margin: 3px 0 5px; text-align: center;">\n        Keputusan Kemendikbud RI Nomor : 264/E/O/2014 Tanggal 23 Juli 2014\n    </p>\n    <div style="margin: 0 auto; width: 100%;">\n        <div style="border-top: 1.6px solid #00b050; margin-bottom: 1.5px;"></div>\n        <div style="border-top: 0.8px solid #00b050;"></div>\n    </div>\n</section>', '<section class="surat surat-permohonan-cuti">\n    <h1 style="margin-bottom: 0; text-align: center;">PERMOHONAN CUTI MAHASISWA</h1>\n    <p style="margin-bottom: 10px; text-align: center;">Nomor: {{nomor_surat}}</p>\n\n    <p style="margin-bottom: 8px; text-align: justify;">Yang bertanda tangan di bawah ini:</p>\n\n    <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px;">\n        <tbody>\n            <tr>\n                <td style="width: 126px; padding: 1px 0; vertical-align: top;">Nama</td>\n                <td style="width: 14px; padding: 1px 0; vertical-align: top;">:</td>\n                <td style="padding: 1px 0; vertical-align: top;">{{nama_pemohon}}</td>\n            </tr>\n            <tr>\n                <td style="padding: 1px 0; vertical-align: top;">NIM</td>\n                <td style="padding: 1px 0; vertical-align: top;">:</td>\n                <td style="padding: 1px 0; vertical-align: top;">{{nim}}</td>\n            </tr>\n            <tr>\n                <td style="padding: 1px 0; vertical-align: top;">Semester</td>\n                <td style="padding: 1px 0; vertical-align: top;">:</td>\n                <td style="padding: 1px 0; vertical-align: top;">{{semester}} ({{semester_terbilang}})</td>\n            </tr>\n            <tr>\n                <td style="padding: 1px 0; vertical-align: top;">Fak/Prodi</td>\n                <td style="padding: 1px 0; vertical-align: top;">:</td>\n                <td style="padding: 1px 0; vertical-align: top;">{{fakultas}}/{{program_studi}}</td>\n            </tr>\n        </tbody>\n    </table>\n\n    <p style="margin-bottom: 8px; text-align: justify;">\n        Dengan ini mengajukan permohonan cuti, dengan alasan {{alasan_cuti}}.\n    </p>\n\n    <p style="text-align: justify;">\n        Demikian permohonan ini saya sampaikan, atas perhatian dan terkabulnya\n        permohonan ini disampaikan terima kasih.\n    </p>\n\n    <div style="margin-top: 8px; width: 100%;">\n        <div style="margin-left: auto; width: 220px;">\n            <p style="margin-bottom: 0;">{{kota_surat}}, {{tanggal_surat_panjang}}</p>\n            <p style="margin-bottom: 40px;">Pemohon,</p>\n            <p style="margin-bottom: 0;"><strong>{{nama_pemohon}}</strong></p>\n            <p>NIM. {{nim}}</p>\n        </div>\n    </div>\n\n    <p style="margin-top: 2px; margin-bottom: 2px; text-align: center;">Mengetahui,</p>\n\n    <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px;">\n        <tbody>\n            <tr>\n                <td style="width: 50%; padding: 0 12px 0 0; vertical-align: top; text-align: center;">\n                    <div style="height: 30px; margin-bottom: 34px;">DPA</div>\n                    <p style="margin-bottom: 0;"><strong>Verry, M.Kom.</strong></p>\n                    <p>NIK. 41230714</p>\n                </td>\n                <td style="width: 50%; padding: 0 0 0 12px; vertical-align: top; text-align: center;">\n                    <div style="height: 30px; margin-bottom: 34px;">\n                        <div>Kaprodi Informatika</div>\n                        <div>a.n Sekretaris Fakultas,</div>\n                    </div>\n                    <p style="margin-bottom: 0;"><strong>Tri Anggoro, M.Kom.</strong></p>\n                    <p>NIK. 41230714268</p>\n                </td>\n            </tr>\n        </tbody>\n    </table>\n\n    <div style="font-size: 10.5pt; line-height: 1.2;">\n        <p style="margin-bottom: 2px;">Tembusan:</p>\n        <p style="margin-bottom: 0;">1. Ka. Prodi</p>\n        <p style="margin-bottom: 0;">2. Bagian Akademik</p>\n        <p style="margin-bottom: 0;">3. Bagian Keuangan</p>\n        <p style="margin-bottom: 0;">4. Fakultas</p>\n        <p style="margin-bottom: 0;">5. Mahasiswa</p>\n    </div>\n</section>', NULL, 1, 1, NULL, NULL, '2026-04-27 09:20:36', '2026-04-28 03:23:53'),
	(2, 5, 'Template Surat Keterangan Lulus', NULL, 'template-surat-keterangan-lulus', 'html', '[Template] Surat Keterangan Lulus.docx', NULL, NULL, NULL, NULL, 'Surat Keterangan Lulus', NULL, '<section class="surat surat-keterangan-lulus">\n    <h1 style="text-align:center;">SURAT KETERANGAN LULUS</h1>\n    <p style="text-align:center;">Nomor: {{nomor_surat}}</p>\n\n    <p>Yang bertanda tangan di bawah ini menerangkan bahwa:</p>\n    <p>Nama: {{nama_pemohon}}</p>\n    <p>NIM: {{nim}}</p>\n    <p>Program Studi: {{program_studi}}</p>\n\n    <p>\n        Telah dinyatakan lulus pada tanggal {{tanggal_yudisium_panjang}}\n        dengan IPK akhir {{ipk_akhir}}.\n    </p>\n\n    <p v-if="{{judul_tugas_akhir}}">\n        Judul tugas akhir: {{judul_tugas_akhir}}\n    </p>\n\n    <p>\n        Surat keterangan ini dibuat untuk dipergunakan sebagaimana mestinya.\n    </p>\n\n    <p>{{kota_surat}}, {{tanggal_surat_panjang}}</p>\n    <p><strong>{{penanda_tangan_jabatan}}</strong></p>\n    <br><br>\n    <p><strong>{{penanda_tangan_nama}}</strong></p>\n    <p>NIDN. {{penanda_tangan_nidn}}</p>\n</section>', NULL, 1, 1, NULL, NULL, '2026-04-27 09:20:36', '2026-04-27 09:20:36'),
	(3, 10, 'Template Surat Permohonan Observasi', NULL, 'template-surat-permohonan-observasi', 'html', '[Template] Surat Permohonan Observasi.docx', NULL, NULL, NULL, NULL, 'Surat Permohonan Observasi', NULL, '<section class="surat surat-permohonan-observasi">\n    <h1 style="text-align:center;">SURAT PERMOHONAN OBSERVASI</h1>\n    <p style="text-align:center;">Nomor: {{nomor_surat}}</p>\n\n    <p>Kepada Yth. Pimpinan {{instansi_tujuan}}</p>\n    <p>di {{alamat_instansi}}</p>\n\n    <p>\n        Dengan hormat, kami mengajukan permohonan observasi untuk mahasiswa berikut:\n    </p>\n    <p>Nama: {{nama_pemohon}}</p>\n    <p>NIM: {{nim}}</p>\n    <p>Program Studi: {{program_studi}}</p>\n\n    <p>\n        Adapun topik observasi yang diajukan adalah {{topik_observasi}},\n        yang direncanakan berlangsung pada {{tanggal_mulai_panjang}} sampai\n        dengan {{tanggal_selesai_panjang}}.\n    </p>\n\n    <p>\n        Demikian permohonan ini kami sampaikan. Atas perhatian dan kerja samanya,\n        kami ucapkan terima kasih.\n    </p>\n\n    <p>{{kota_surat}}, {{tanggal_surat_panjang}}</p>\n    <p><strong>{{penanda_tangan_jabatan}}</strong></p>\n    <br><br>\n    <p><strong>{{penanda_tangan_nama}}</strong></p>\n    <p>NIDN. {{penanda_tangan_nidn}}</p>\n</section>', NULL, 1, 1, NULL, NULL, '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(4, 11, 'Template Permohonan Dispensasi Mahasiswa', NULL, 'template-permohonan-dispensasi-mahasiswa', 'html', '142. permohonan dispensasi Mhs.docx', NULL, NULL, NULL, NULL, 'Permohonan Dispensasi Mahasiswa', NULL, '<section class="surat surat-permohonan-dispensasi">\n    <h1 style="text-align:center;">SURAT PERMOHONAN DISPENSASI</h1>\n    <p style="text-align:center;">Nomor: {{nomor_surat}}</p>\n\n    <p>Yang bertanda tangan di bawah ini:</p>\n    <p>Hari/Tanggal: {{27 April 2026}}</p>\n    <p>Waktu: {{09.00-selesai}}</p>\n    <p>Tempat: {{UNUGHA}}</p>\n\n    <p>\n        Mengajukan permohonan dispensasi untuk mata kuliah {{mata_kuliah}}\n        {{kelas_info}} pada tanggal {{tanggal_kegiatan_panjang}}\n        {{dosen_pengampu_info}}, dengan alasan {{alasan_dispensasi}}.\n    </p>\n\n    <p>\n        Demikian surat permohonan ini saya sampaikan. Atas perhatian dan kebijaksanaannya\n        saya ucapkan terima kasih.\n    </p>\n\n    <p>{{kota_surat}}, {{tanggal_surat_panjang}}</p>\n    <p>Pemohon,</p>\n    <br><br>\n    <p><strong>{{nama_pemohon}}</strong></p>\n    <p>NIM. {{nim}}</p>\n</section>', NULL, 2, 1, NULL, NULL, '2026-04-27 09:20:37', '2026-04-27 10:11:14'),
	(5, 12, 'Template Permohonan Pindah Kelas', NULL, 'template-permohonan-pindah-kelas', 'html', 'permohonan  pindah kelas.docx', NULL, NULL, NULL, NULL, 'Permohonan Pindah Kelas', NULL, '<section class="surat surat-permohonan-pindah-kelas">\n    <h1 style="text-align:center;">SURAT PERMOHONAN PINDAH KELAS</h1>\n    <p style="text-align:center;">Nomor: {{nomor_surat}}</p>\n\n    <p>Yang bertanda tangan di bawah ini:</p>\n    <p>Nama: {{nama_pemohon}}</p>\n    <p>NIM: {{nim}}</p>\n    <p>Program Studi: {{program_studi}}</p>\n\n    <p>\n        Mengajukan permohonan pindah kelas untuk mata kuliah {{mata_kuliah}}\n        dari kelas {{kelas_asal}} ke kelas {{kelas_tujuan}} dengan alasan\n        {{alasan_pindah}}.\n    </p>\n\n    <p>\n        Demikian permohonan ini saya ajukan. Atas perhatian dan persetujuannya,\n        saya ucapkan terima kasih.\n    </p>\n\n    <p>{{kota_surat}}, {{tanggal_surat_panjang}}</p>\n    <p>Pemohon,</p>\n    <br><br>\n    <p><strong>{{nama_pemohon}}</strong></p>\n    <p>NIM. {{nim}}</p>\n</section>', NULL, 1, 1, NULL, NULL, '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(6, 13, 'Template Permohonan Menjadi Penguji Sidang', NULL, 'template-permohonan-menjadi-penguji-sidang', 'html', 'PERMOHONAN MENJADI PENGUJI SIDANG (fix) (1).docx', NULL, NULL, NULL, NULL, 'Permohonan Menjadi Penguji Sidang', NULL, '<section class="surat surat-penguji-sidang">\n    <h1 style="text-align:center;">SURAT TUGAS / PERMOHONAN MENJADI PENGUJI SIDANG</h1>\n    <p style="text-align:center;">Nomor: {{nomor_surat}}</p>\n\n    <p>Yang bertanda tangan di bawah ini menugaskan / memohonkan kepada:</p>\n    <p>Nama Dosen: {{nama_dosen}}</p>\n    <p>NIP / NIDN: {{nip}}</p>\n\n    <p>\n        Untuk menjadi penguji sidang mahasiswa {{nama_mahasiswa}}\n        (NIM {{nim_mahasiswa}}) dengan judul tugas akhir:\n    </p>\n    <p><em>{{judul_skripsi}}</em></p>\n\n    <p>\n        Sidang dijadwalkan pada {{tanggal_sidang_panjang}}{{ruang_sidang_info}}.\n    </p>\n\n    <p>\n        Demikian surat ini dibuat untuk dilaksanakan sebagaimana mestinya.\n    </p>\n\n    <p>{{kota_surat}}, {{tanggal_surat_panjang}}</p>\n    <p><strong>{{penanda_tangan_jabatan}}</strong></p>\n    <br><br>\n    <p><strong>{{penanda_tangan_nama}}</strong></p>\n    <p>NIDN. {{penanda_tangan_nidn}}</p>\n</section>', NULL, 1, 1, NULL, NULL, '2026-04-27 09:20:37', '2026-04-27 09:20:37');

-- Dumping structure for table portal-fmikom.surat_template_placeholders
DROP TABLE IF EXISTS `surat_template_placeholders`;
CREATE TABLE IF NOT EXISTS `surat_template_placeholders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `surat_template_id` bigint unsigned NOT NULL,
  `placeholder_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT '1',
  `default_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `surat_template_placeholder_unique` (`surat_template_id`,`placeholder_key`),
  CONSTRAINT `surat_template_placeholders_surat_template_id_foreign` FOREIGN KEY (`surat_template_id`) REFERENCES `surat_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.surat_template_placeholders: ~73 rows (approximately)
INSERT INTO `surat_template_placeholders` (`id`, `surat_template_id`, `placeholder_key`, `label`, `source_type`, `source_key`, `is_required`, `default_value`, `description`, `created_at`, `updated_at`) VALUES
	(1, 1, 'nomor_surat', 'Nomor Surat', 'surat', 'nomor_surat', 1, NULL, 'Nomor surat final yang digenerate sistem.', '2026-04-27 09:20:36', '2026-04-27 09:20:36'),
	(2, 1, 'nama_pemohon', 'Nama Pemohon', 'user', 'name', 1, NULL, 'Nama mahasiswa dari akun pemohon.', '2026-04-27 09:20:36', '2026-04-27 09:20:36'),
	(3, 1, 'nim', 'NIM', 'surat_data', 'nim', 1, NULL, 'Nomor induk mahasiswa.', '2026-04-27 09:20:36', '2026-04-27 09:20:36'),
	(4, 1, 'semester', 'Semester', 'surat_data', 'semester', 1, NULL, 'Semester saat ini dalam angka.', '2026-04-27 09:20:36', '2026-04-27 09:20:36'),
	(5, 1, 'semester_terbilang', 'Semester Terbilang', 'computed', 'semester_terbilang', 1, NULL, 'Konversi angka semester ke teks.', '2026-04-27 09:20:36', '2026-04-27 09:20:36'),
	(6, 1, 'fakultas', 'Fakultas', 'system', 'fakultas_nama', 1, 'FMIKOM', 'Nama fakultas default.', '2026-04-27 09:20:36', '2026-04-27 09:20:36'),
	(7, 1, 'program_studi', 'Program Studi', 'surat_data', 'program_studi', 1, NULL, 'Program studi mahasiswa.', '2026-04-27 09:20:36', '2026-04-27 09:20:36'),
	(8, 1, 'tahun_akademik', 'Tahun Akademik', 'surat_data', 'tahun_akademik', 1, NULL, 'Tahun akademik pengajuan cuti.', '2026-04-27 09:20:36', '2026-04-27 09:20:36'),
	(9, 1, 'semester_pengajuan', 'Semester Pengajuan', 'surat_data', 'semester_pengajuan', 1, NULL, 'Semester ganjil atau genap.', '2026-04-27 09:20:36', '2026-04-27 09:20:36'),
	(10, 1, 'alasan_cuti', 'Alasan Cuti', 'surat_data', 'alasan_cuti', 1, NULL, 'Alasan pengajuan cuti.', '2026-04-27 09:20:36', '2026-04-27 09:20:36'),
	(11, 1, 'kota_surat', 'Kota Surat', 'system', 'kota_surat', 1, 'Cilacap', 'Kota penerbitan surat.', '2026-04-27 09:20:36', '2026-04-27 09:20:36'),
	(12, 1, 'tanggal_surat_panjang', 'Tanggal Surat Panjang', 'computed', 'tanggal_surat_panjang', 1, NULL, 'Tanggal final dalam format panjang bahasa Indonesia.', '2026-04-27 09:20:36', '2026-04-27 09:20:36'),
	(13, 2, 'nomor_surat', 'Nomor Surat', 'surat', 'nomor_surat', 1, NULL, 'Nomor surat final.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(14, 2, 'nama_pemohon', 'Nama Pemohon', 'user', 'name', 1, NULL, 'Nama mahasiswa yang dinyatakan lulus.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(15, 2, 'nim', 'NIM', 'surat_data', 'nim', 1, NULL, 'Nomor induk mahasiswa.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(16, 2, 'program_studi', 'Program Studi', 'surat_data', 'program_studi', 1, NULL, 'Program studi mahasiswa.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(17, 2, 'tanggal_yudisium_panjang', 'Tanggal Yudisium Panjang', 'computed', 'tanggal_yudisium_panjang', 1, NULL, 'Tanggal yudisium dalam format panjang.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(18, 2, 'ipk_akhir', 'IPK Akhir', 'surat_data', 'ipk_akhir', 1, NULL, 'Nilai IPK akhir mahasiswa.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(19, 2, 'judul_tugas_akhir', 'Judul Tugas Akhir', 'surat_data', 'judul_tugas_akhir', 0, NULL, 'Judul tugas akhir jika dibutuhkan dalam surat.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(20, 2, 'kota_surat', 'Kota Surat', 'system', 'kota_surat', 1, 'Cilacap', 'Kota penerbitan surat.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(21, 2, 'tanggal_surat_panjang', 'Tanggal Surat Panjang', 'computed', 'tanggal_surat_panjang', 1, NULL, 'Tanggal final surat.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(22, 2, 'penanda_tangan_jabatan', 'Jabatan Penanda Tangan', 'system', 'penanda_tangan_jabatan', 1, 'Dekan', 'Jabatan pejabat penanda tangan.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(23, 2, 'penanda_tangan_nama', 'Nama Penanda Tangan', 'system', 'penanda_tangan_nama', 1, NULL, 'Nama pejabat penanda tangan.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(24, 2, 'penanda_tangan_nidn', 'NIDN Penanda Tangan', 'system', 'penanda_tangan_nidn', 1, NULL, 'NIDN pejabat penanda tangan.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(25, 3, 'nomor_surat', 'Nomor Surat', 'surat', 'nomor_surat', 1, NULL, 'Nomor surat final.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(26, 3, 'instansi_tujuan', 'Instansi Tujuan', 'surat_data', 'instansi_tujuan', 1, NULL, 'Nama instansi tujuan observasi.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(27, 3, 'alamat_instansi', 'Alamat Instansi', 'surat_data', 'alamat_instansi', 1, NULL, 'Alamat lengkap instansi tujuan.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(28, 3, 'nama_pemohon', 'Nama Pemohon', 'user', 'name', 1, NULL, 'Nama mahasiswa pemohon.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(29, 3, 'nim', 'NIM', 'surat_data', 'nim', 1, NULL, 'NIM mahasiswa.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(30, 3, 'program_studi', 'Program Studi', 'surat_data', 'program_studi', 1, NULL, 'Program studi mahasiswa.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(31, 3, 'topik_observasi', 'Topik Observasi', 'surat_data', 'topik_observasi', 1, NULL, 'Topik atau fokus observasi.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(32, 3, 'tanggal_mulai_panjang', 'Tanggal Mulai Panjang', 'computed', 'tanggal_mulai_panjang', 1, NULL, 'Tanggal mulai observasi format panjang.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(33, 3, 'tanggal_selesai_panjang', 'Tanggal Selesai Panjang', 'computed', 'tanggal_selesai_panjang', 1, NULL, 'Tanggal selesai observasi format panjang.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(34, 3, 'kota_surat', 'Kota Surat', 'system', 'kota_surat', 1, 'Cilacap', 'Kota penerbitan surat.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(35, 3, 'tanggal_surat_panjang', 'Tanggal Surat Panjang', 'computed', 'tanggal_surat_panjang', 1, NULL, 'Tanggal final surat.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(36, 3, 'penanda_tangan_jabatan', 'Jabatan Penanda Tangan', 'system', 'penanda_tangan_jabatan', 1, 'Dekan', 'Jabatan penanda tangan.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(37, 3, 'penanda_tangan_nama', 'Nama Penanda Tangan', 'system', 'penanda_tangan_nama', 1, NULL, 'Nama pejabat penanda tangan.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(38, 3, 'penanda_tangan_nidn', 'NIDN Penanda Tangan', 'system', 'penanda_tangan_nidn', 1, NULL, 'NIDN pejabat penanda tangan.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(39, 4, 'nomor_surat', 'Nomor Surat', 'surat', 'nomor_surat', 1, NULL, 'Nomor surat final.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(40, 4, 'nama_pemohon', 'Nama Pemohon', 'user', 'name', 1, NULL, 'Nama mahasiswa pemohon.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(41, 4, 'nim', 'NIM', 'surat_data', 'nim', 1, NULL, 'NIM mahasiswa.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(42, 4, 'program_studi', 'Program Studi', 'surat_data', 'program_studi', 1, NULL, 'Program studi mahasiswa.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(43, 4, 'mata_kuliah', 'Mata Kuliah', 'surat_data', 'mata_kuliah', 1, NULL, 'Mata kuliah yang didispensasi.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(44, 4, 'kelas_info', 'Informasi Kelas', 'computed', 'kelas_info', 0, NULL, 'Potongan teks kelas jika data kelas tersedia.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(45, 4, 'tanggal_kegiatan_panjang', 'Tanggal Kegiatan Panjang', 'computed', 'tanggal_kegiatan_panjang', 1, NULL, 'Tanggal kegiatan/dispen format panjang.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(46, 4, 'dosen_pengampu_info', 'Informasi Dosen Pengampu', 'computed', 'dosen_pengampu_info', 0, NULL, 'Potongan teks dosen pengampu jika tersedia.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(47, 4, 'alasan_dispensasi', 'Alasan Dispensasi', 'surat_data', 'alasan_dispensasi', 1, NULL, 'Alasan pengajuan dispensasi.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(48, 4, 'kota_surat', 'Kota Surat', 'system', 'kota_surat', 1, 'Cilacap', 'Kota penerbitan surat.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(49, 4, 'tanggal_surat_panjang', 'Tanggal Surat Panjang', 'computed', 'tanggal_surat_panjang', 1, NULL, 'Tanggal final surat.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(50, 5, 'nomor_surat', 'Nomor Surat', 'surat', 'nomor_surat', 1, NULL, 'Nomor surat final.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(51, 5, 'nama_pemohon', 'Nama Pemohon', 'user', 'name', 1, NULL, 'Nama mahasiswa pemohon.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(52, 5, 'nim', 'NIM', 'surat_data', 'nim', 1, NULL, 'NIM mahasiswa.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(53, 5, 'program_studi', 'Program Studi', 'surat_data', 'program_studi', 1, NULL, 'Program studi mahasiswa.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(54, 5, 'mata_kuliah', 'Mata Kuliah', 'surat_data', 'mata_kuliah', 1, NULL, 'Mata kuliah yang dimohonkan pindah kelas.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(55, 5, 'kelas_asal', 'Kelas Asal', 'surat_data', 'kelas_asal', 1, NULL, 'Kelas asal mahasiswa.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(56, 5, 'kelas_tujuan', 'Kelas Tujuan', 'surat_data', 'kelas_tujuan', 1, NULL, 'Kelas tujuan mahasiswa.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(57, 5, 'alasan_pindah', 'Alasan Pindah', 'surat_data', 'alasan_pindah', 1, NULL, 'Alasan perpindahan kelas.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(58, 5, 'kota_surat', 'Kota Surat', 'system', 'kota_surat', 1, 'Cilacap', 'Kota penerbitan surat.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(59, 5, 'tanggal_surat_panjang', 'Tanggal Surat Panjang', 'computed', 'tanggal_surat_panjang', 1, NULL, 'Tanggal final surat.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(60, 6, 'nomor_surat', 'Nomor Surat', 'surat', 'nomor_surat', 1, NULL, 'Nomor surat final.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(61, 6, 'nama_dosen', 'Nama Dosen', 'surat_data', 'nama_dosen', 1, NULL, 'Nama dosen penguji yang diajukan.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(62, 6, 'nip', 'NIP / NIDN', 'surat_data', 'nip', 1, NULL, 'NIP atau NIDN dosen.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(63, 6, 'nama_mahasiswa', 'Nama Mahasiswa', 'surat_data', 'nama_mahasiswa', 1, NULL, 'Nama mahasiswa yang akan sidang.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(64, 6, 'nim_mahasiswa', 'NIM Mahasiswa', 'surat_data', 'nim_mahasiswa', 1, NULL, 'NIM mahasiswa yang akan sidang.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(65, 6, 'judul_skripsi', 'Judul Skripsi', 'surat_data', 'judul_skripsi', 1, NULL, 'Judul skripsi atau tugas akhir.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(66, 6, 'tanggal_sidang_panjang', 'Tanggal Sidang Panjang', 'computed', 'tanggal_sidang_panjang', 1, NULL, 'Tanggal sidang dalam format panjang.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(67, 6, 'ruang_sidang_info', 'Informasi Ruang Sidang', 'computed', 'ruang_sidang_info', 0, NULL, 'Potongan teks ruang sidang jika tersedia.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(68, 6, 'kota_surat', 'Kota Surat', 'system', 'kota_surat', 1, 'Cilacap', 'Kota penerbitan surat.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(69, 6, 'tanggal_surat_panjang', 'Tanggal Surat Panjang', 'computed', 'tanggal_surat_panjang', 1, NULL, 'Tanggal final surat.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(70, 6, 'penanda_tangan_jabatan', 'Jabatan Penanda Tangan', 'system', 'penanda_tangan_jabatan', 1, 'Dekan', 'Jabatan penanda tangan.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(71, 6, 'penanda_tangan_nama', 'Nama Penanda Tangan', 'system', 'penanda_tangan_nama', 1, NULL, 'Nama pejabat penanda tangan.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(72, 6, 'penanda_tangan_nidn', 'NIDN Penanda Tangan', 'system', 'penanda_tangan_nidn', 1, NULL, 'NIDN pejabat penanda tangan.', '2026-04-27 09:20:37', '2026-04-27 09:20:37'),
	(73, 1, 'kop_logo_data_uri', 'Logo Kop Surat', 'system', 'kop_logo_data_uri', 1, NULL, 'Logo sementara untuk kop surat fakultas.', '2026-04-28 02:53:28', '2026-04-28 02:53:28');

-- Dumping structure for table portal-fmikom.template_global_settings
DROP TABLE IF EXISTS `template_global_settings`;
CREATE TABLE IF NOT EXISTS `template_global_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `tipe` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text' COMMENT 'text|html|json|image_path',
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `template_global_settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.template_global_settings: ~13 rows (approximately)
INSERT INTO `template_global_settings` (`id`, `key`, `value`, `tipe`, `label`, `deskripsi`, `created_at`, `updated_at`) VALUES
	(1, 'nama_instansi', 'Universitas Nahdlatul Ulama Al Ghazali Cilacap', 'text', 'Nama Instansi', NULL, '2026-05-02 22:06:19', '2026-05-02 22:06:19'),
	(2, 'nama_fakultas', 'Fakultas Matematika dan Ilmu Komputer', 'text', 'Nama Fakultas', NULL, '2026-05-02 22:06:19', '2026-05-02 22:06:19'),
	(3, 'singkatan', 'FMIKOM', 'text', 'Singkatan Fakultas', NULL, '2026-05-02 22:06:19', '2026-05-02 22:06:19'),
	(4, 'alamat', 'Jl. Kemerdekaan Barat No. 17, Cilacap Tengah', 'text', 'Alamat', NULL, '2026-05-02 22:06:19', '2026-05-02 22:06:19'),
	(5, 'telepon', NULL, 'text', 'Telepon', NULL, '2026-05-02 22:06:19', '2026-05-02 22:06:19'),
	(6, 'website', NULL, 'text', 'Website', NULL, '2026-05-02 22:06:19', '2026-05-02 22:06:19'),
	(7, 'email', NULL, 'text', 'Email', NULL, '2026-05-02 22:06:19', '2026-05-02 22:06:19'),
	(8, 'logo_path', NULL, 'image_path', 'Logo Universitas', NULL, '2026-05-02 22:06:19', '2026-05-02 22:06:19'),
	(9, 'kop_html', NULL, 'html', 'Kop Surat (HTML)', NULL, '2026-05-02 22:06:19', '2026-05-02 22:06:19'),
	(10, 'footer_html', NULL, 'html', 'Footer Surat (HTML)', NULL, '2026-05-02 22:06:19', '2026-05-02 22:06:19'),
	(11, 'warna_primer', '#1B5E20', 'text', 'Warna Primer Kop', NULL, '2026-05-02 22:06:19', '2026-05-02 22:06:19'),
	(12, 'kota_surat', 'Cilacap', 'text', 'Kota Default', NULL, '2026-05-02 22:06:19', '2026-05-02 22:06:19'),
	(13, 'format_nomor', '{kode}/{urutan}/{bulan_romawi}/{tahun}', 'text', 'Format Nomor Surat', NULL, '2026-05-02 22:06:19', '2026-05-02 22:06:19');

-- Dumping structure for table portal-fmikom.users
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomor_induk` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_approval` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'approved',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `otp_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `otp_expires_at` timestamp NULL DEFAULT NULL,
  `password_changed_at` timestamp NULL DEFAULT NULL,
  `two_factor_secret` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `two_factor_recovery_codes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role_id` bigint unsigned DEFAULT NULL,
  `program_studi_id` bigint unsigned DEFAULT NULL,
  `nim_nip` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_telepon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_id_foreign` (`role_id`),
  KEY `users_program_studi_id_foreign` (`program_studi_id`),
  CONSTRAINT `users_program_studi_id_foreign` FOREIGN KEY (`program_studi_id`) REFERENCES `program_studis` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table portal-fmikom.users: ~9 rows (approximately)
INSERT INTO `users` (`id`, `name`, `email`, `nomor_induk`, `status_approval`, `email_verified_at`, `password`, `otp_code`, `otp_expires_at`, `password_changed_at`, `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`, `remember_token`, `created_at`, `updated_at`, `role_id`, `program_studi_id`, `nim_nip`, `no_telepon`, `foto_path`, `is_active`) VALUES
	(1, 'Muchlisin Maruf', 'muchlisinmaruf@gmail.com', NULL, 'approved', '2026-03-29 13:45:30', '$2y$12$7dVeo0mbJITCFTuEF51VoOzrZADBo16Pr0NKszt/rHCfeTQWIN.1e', NULL, NULL, '2026-03-29 13:57:07', NULL, NULL, NULL, NULL, '2026-03-29 13:23:50', '2026-03-29 13:57:07', 5, NULL, NULL, NULL, NULL, 1),
	(2, 'Dummy Pelajar', 'mahasiswa@example.com', NULL, 'approved', NULL, '$2y$12$/0u391PgODpjbB2hikSzMuRr8FuOkjegffWWE508W6uTIeQgcN3n2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-29 13:23:51', '2026-03-29 13:23:55', 3, NULL, NULL, NULL, NULL, 1),
	(3, 'Maruf Muchlisin', 'cahwangon27112003@gmail.com', '22eo10013', 'pending', NULL, '$2y$12$pTB6zUDqA7NvUZk0qp/o0.Bi4PYI9qqusho8spU.D4hHIXcrvDF2.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-29 14:50:10', '2026-03-29 14:50:10', 3, NULL, NULL, NULL, NULL, 1),
	(5, 'Admin', 'tiffanyhellen27@gmail.com', NULL, 'approved', '2026-04-22 17:18:33', '$2y$12$M4949ana0DQJY84aX4QTEeNDSQkx7404pu9hNwFgw.loq5YVao8hO', NULL, NULL, NULL, NULL, NULL, NULL, 'BFUVj3lqnHf4sMZzJ1Sat7dPs3ooAVnqnG1GFiiu8H0qfG7VJuK5rEL848nO', '2026-04-08 08:47:09', '2026-04-22 17:18:33', 5, NULL, NULL, NULL, NULL, 1),
	(6, 'Test User', 'test@example.com', '20210045', 'approved', '2026-04-27 09:33:20', '$2y$12$UgR52kLlAsSKB5yyxUy6ie6WMDQ/E1Cd3yM5ez5ARSoOq1MDxO1ju', NULL, NULL, NULL, NULL, NULL, NULL, 'LtWlUGyiTM', '2026-04-18 04:27:39', '2026-04-27 09:33:20', 3, NULL, '20210045', NULL, NULL, 1),
	(8, 'Fanny', 'hellentiffanyyy@gmail.com', NULL, 'approved', '2026-04-22 17:18:33', '$2y$12$I9m3CK6kuTeFWwoix0bxA.h1j2FNOzElgEbimZhhmP4oxIaJVK/iC', NULL, NULL, NULL, NULL, NULL, NULL, 'P88guHRB0lzN8IpOBn9QBkSOGX4EpqMnoDrtA5IlZVAl3cTUTyvlWhVEoIVO', '2026-04-22 17:18:11', '2026-04-22 17:18:33', 3, NULL, NULL, NULL, NULL, 1),
	(9, 'Mrs. Laora', 'hellenfast1@gmail.com', NULL, 'approved', '2026-04-22 17:18:33', '$2y$12$fKiKLmFnXBJ6SrPO6IaUb./x7cJkHfeyoj1OHwDhzQBpvhvJFvLhe', NULL, NULL, NULL, NULL, NULL, NULL, 'd0twQJ6OVq023c0d35PDPC3FPxzPQmIgsS3boH0PjJYprJ8Ir51j7nHEODrq', '2026-04-22 17:18:11', '2026-04-22 17:18:33', 4, NULL, NULL, NULL, NULL, 1),
	(10, 'Mrs. Anna', 'hellenfast2@gmail.com', NULL, 'approved', '2026-04-22 17:18:33', '$2y$12$2LIbDW.A9SKCW6BoQzPlSuMCFNS11ED8zEr5YbHnbzVoxMteKLDS6', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-22 17:18:11', '2026-04-22 17:18:33', 6, NULL, NULL, NULL, NULL, 1),
	(11, 'Mrs. Moana', 'hellenfast3@gmail.com', NULL, 'approved', '2026-04-22 17:18:33', '$2y$12$Y3hD7wmDIBn4hd23aAYaNOII9XwvF66XUu44wPrR8FcGq95L2RFJK', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-22 17:18:11', '2026-04-22 17:18:33', 7, NULL, NULL, NULL, NULL, 1);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
