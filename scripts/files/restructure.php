#!/usr/bin/env php
<?php
/**
 * FAST Academic — Rename & Restructure Script (FIXED)
 * Jalankan: php scripts\restructure.php --dry-run
 * Eksekusi: php scripts\restructure.php
 */

$dryRun = in_array('--dry-run', $argv ?? [], true);
$root   = rtrim(getcwd(), '/\\');

echo "\n";
echo "=======================================================\n";
echo "  FAST Academic - Restructure Script\n";
echo ($dryRun
    ? "  MODE: DRY RUN (tidak ada file yang berubah)\n"
    : "  MODE: LIVE - file akan dipindah & diubah\n");
echo "=======================================================\n\n";

// ── Helpers ──────────────────────────────────────────────────────────────

function rPrint(string $msg, string $type = 'info'): void
{
    $prefix = match($type) {
        'ok'    => "[OK]  ",
        'move'  => "[->]  ",
        'edit'  => "[~]   ",
        'skip'  => "[..]  ",
        'error' => "[ERR] ",
        'head'  => "\n---",
        default => "      ",
    };
    echo "{$prefix} {$msg}\n";
}

function rMkdir(string $path, bool $dry): void
{
    if (!is_dir($path)) {
        if (!$dry) mkdir($path, 0755, true);
        rPrint("mkdir {$path}", 'ok');
    }
}

function rMove(string $from, string $to, bool $dry): bool
{
    global $root;
    $sep     = DIRECTORY_SEPARATOR;
    $absFrom = $root . $sep . str_replace(['/', '\\'], $sep, $from);
    $absTo   = $root . $sep . str_replace(['/', '\\'], $sep, $to);

    if (!file_exists($absFrom)) {
        rPrint("SKIP (tidak ada): {$from}", 'skip');
        return false;
    }

    rMkdir(dirname($absTo), $dry);
    if (!$dry) rename($absFrom, $absTo);
    rPrint("{$from}", 'move');
    echo "        -> {$to}\n";
    return true;
}

function rEdit(string $file, array $replacements, bool $dry): void
{
    global $root;
    $sep     = DIRECTORY_SEPARATOR;
    $absPath = $root . $sep . str_replace(['/', '\\'], $sep, $file);

    if (!file_exists($absPath)) {
        rPrint("SKIP (tidak ada): {$file}", 'skip');
        return;
    }

    $original = file_get_contents($absPath);
    $updated  = str_replace(array_keys($replacements), array_values($replacements), $original);

    if ($original === $updated) {
        rPrint("No change: {$file}", 'skip');
        return;
    }

    if (!$dry) file_put_contents($absPath, $updated);
    rPrint("Updated: {$file}", 'edit');

    foreach ($replacements as $k => $v) {
        if (str_contains($original, $k)) {
            echo "        ~ \"{$k}\"\n";
            echo "          -> \"{$v}\"\n";
        }
    }
}

function rEditDir(string $dir, array $replacements, bool $dry): void
{
    global $root;
    $sep    = DIRECTORY_SEPARATOR;
    $absDir = $root . $sep . str_replace(['/', '\\'], $sep, $dir);

    if (!is_dir($absDir)) {
        rPrint("SKIP dir (tidak ada): {$dir}", 'skip');
        return;
    }

    $iter = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($absDir, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iter as $fileInfo) {
        if (in_array($fileInfo->getExtension(), ['vue', 'ts'], true)) {
            $rel = ltrim(str_replace($root, '', $fileInfo->getPathname()), '/\\');
            rEdit($rel, $replacements, $dry);
        }
    }
}

// ════════════════════════════════════════════════════════════════════════════
// STEP 1 — Vue Layout
// ════════════════════════════════════════════════════════════════════════════
rPrint('STEP 1 - Vue Layouts', 'head');

rMove(
    'resources/js/layouts/fast/AdminWorkspaceLayout.vue',
    'resources/js/layouts/AdminLayout.vue',
    $dryRun
);

// ════════════════════════════════════════════════════════════════════════════
// STEP 2 — Vue Components
// ════════════════════════════════════════════════════════════════════════════
rPrint('STEP 2 - Vue Components', 'head');

foreach ([
    'resources/js/components/Fast/AdminSuratStepIndicator.vue' => 'resources/js/components/admin/LetterStepIndicator.vue',
    'resources/js/components/Fast/AdminSuratDynamicField.vue'  => 'resources/js/components/admin/DynamicField.vue',
    'resources/js/components/Fast/DynamicFormModal.vue'        => 'resources/js/components/admin/DynamicFormModal.vue',
] as $from => $to) {
    rMove($from, $to, $dryRun);
}

// ════════════════════════════════════════════════════════════════════════════
// STEP 3 — Vue Pages
// ════════════════════════════════════════════════════════════════════════════
rPrint('STEP 3 - Vue Pages', 'head');

foreach ([
    'resources/js/pages/fast/administrasi/DashboardAdmin.vue'      => 'resources/js/pages/admin/dashboard/Index.vue',
    'resources/js/pages/fast/administrasi/surat/Create.vue'         => 'resources/js/pages/admin/letters/Create.vue',
    'resources/js/pages/fast/administrasi/surat/Form.vue'           => 'resources/js/pages/admin/letters/Form.vue',
    'resources/js/pages/fast/administrasi/surat/Preview.vue'        => 'resources/js/pages/admin/letters/Preview.vue',
    'resources/js/pages/fast/administrasi/Templates.vue'            => 'resources/js/pages/admin/templates/Index.vue',
    'resources/js/pages/fast/approval/Dashboard.vue'                => 'resources/js/pages/admin/approval/Index.vue',
] as $from => $to) {
    rMove($from, $to, $dryRun);
}

// ════════════════════════════════════════════════════════════════════════════
// STEP 4 — Controllers
// ════════════════════════════════════════════════════════════════════════════
rPrint('STEP 4 - Controllers', 'head');

foreach ([
    'app/Http/Controllers/Fast/Administrasi/AdminDashboardController.php'     => 'app/Http/Controllers/Admin/DashboardController.php',
    'app/Http/Controllers/Fast/Administrasi/AdminSuratController.php'         => 'app/Http/Controllers/Admin/LetterController.php',
    'app/Http/Controllers/Fast/Administrasi/TemplateManagementController.php' => 'app/Http/Controllers/Admin/TemplateController.php',
    'app/Http/Controllers/Fast/Approval/DashboardController.php'              => 'app/Http/Controllers/Admin/ApprovalController.php',
    'app/Http/Controllers/Fast/User/DashboardController.php'                  => 'app/Http/Controllers/User/DashboardController.php',
    'app/Http/Controllers/Fast/User/SubmissionController.php'                 => 'app/Http/Controllers/User/SubmissionController.php',
    'app/Http/Controllers/Fast/User/JenisSuratFieldConfigController.php'      => 'app/Http/Controllers/User/LetterTypeController.php',
] as $from => $to) {
    rMove($from, $to, $dryRun);
}

// ════════════════════════════════════════════════════════════════════════════
// STEP 5 — Update namespace Controllers
// ════════════════════════════════════════════════════════════════════════════
rPrint('STEP 5 - Update namespace Controllers', 'head');

foreach ([
    'app/Http/Controllers/Admin/DashboardController.php' => [
        'namespace App\\Http\\Controllers\\Fast\\Administrasi;' => 'namespace App\\Http\\Controllers\\Admin;',
        'class AdminDashboardController'                        => 'class DashboardController',
    ],
    'app/Http/Controllers/Admin/LetterController.php' => [
        'namespace App\\Http\\Controllers\\Fast\\Administrasi;' => 'namespace App\\Http\\Controllers\\Admin;',
        'class AdminSuratController'                            => 'class LetterController',
    ],
    'app/Http/Controllers/Admin/TemplateController.php' => [
        'namespace App\\Http\\Controllers\\Fast\\Administrasi;' => 'namespace App\\Http\\Controllers\\Admin;',
        'class TemplateManagementController'                    => 'class TemplateController',
    ],
    'app/Http/Controllers/Admin/ApprovalController.php' => [
        'namespace App\\Http\\Controllers\\Fast\\Approval;' => 'namespace App\\Http\\Controllers\\Admin;',
        'class DashboardController'                         => 'class ApprovalController',
    ],
    'app/Http/Controllers/User/DashboardController.php' => [
        'namespace App\\Http\\Controllers\\Fast\\User;' => 'namespace App\\Http\\Controllers\\User;',
    ],
    'app/Http/Controllers/User/SubmissionController.php' => [
        'namespace App\\Http\\Controllers\\Fast\\User;' => 'namespace App\\Http\\Controllers\\User;',
    ],
    'app/Http/Controllers/User/LetterTypeController.php' => [
        'namespace App\\Http\\Controllers\\Fast\\User;' => 'namespace App\\Http\\Controllers\\User;',
        'class JenisSuratFieldConfigController'         => 'class LetterTypeController',
    ],
] as $file => $replacements) {
    rEdit($file, $replacements, $dryRun);
}

// ════════════════════════════════════════════════════════════════════════════
// STEP 6 — Update Inertia::render() path
// ════════════════════════════════════════════════════════════════════════════
rPrint('STEP 6 - Update Inertia render path', 'head');

foreach ([
    'app/Http/Controllers/Admin/DashboardController.php' => [
        "'fast/administrasi/DashboardAdmin'" => "'admin/dashboard/Index'",
    ],
    'app/Http/Controllers/Admin/LetterController.php' => [
        "'fast/administrasi/surat/Create'"  => "'admin/letters/Create'",
        "'fast/administrasi/surat/Form'"    => "'admin/letters/Form'",
        "'fast/administrasi/surat/Preview'" => "'admin/letters/Preview'",
    ],
    'app/Http/Controllers/Admin/TemplateController.php' => [
        "'fast/administrasi/Templates'" => "'admin/templates/Index'",
    ],
    'app/Http/Controllers/Admin/ApprovalController.php' => [
        "'fast/approval/Dashboard'" => "'admin/approval/Index'",
    ],
    'app/Http/Controllers/User/DashboardController.php' => [
        "'fast/user/Dashboard'" => "'user/Dashboard'",
    ],
] as $file => $replacements) {
    rEdit($file, $replacements, $dryRun);
}

// ════════════════════════════════════════════════════════════════════════════
// STEP 7 — Update Vue imports
// ════════════════════════════════════════════════════════════════════════════
rPrint('STEP 7 - Update Vue imports', 'head');

$vueImports = [
    "from '@/layouts/fast/AdminWorkspaceLayout.vue'"       => "from '@/layouts/AdminLayout.vue'",
    'from "@/layouts/fast/AdminWorkspaceLayout.vue"'       => 'from "@/layouts/AdminLayout.vue"',
    'AdminWorkspaceLayout'                                 => 'AdminLayout',
    "from '@/components/Fast/AdminSuratStepIndicator.vue'" => "from '@/components/admin/LetterStepIndicator.vue'",
    'from "@/components/Fast/AdminSuratStepIndicator.vue"' => 'from "@/components/admin/LetterStepIndicator.vue"',
    "from '@/components/Fast/AdminSuratDynamicField.vue'"  => "from '@/components/admin/DynamicField.vue'",
    'from "@/components/Fast/AdminSuratDynamicField.vue"'  => 'from "@/components/admin/DynamicField.vue"',
    'AdminSuratStepIndicator'                              => 'LetterStepIndicator',
    'AdminSuratDynamicField'                               => 'DynamicField',
];

rEditDir('resources/js/pages/admin', $vueImports, $dryRun);
rEditDir('resources/js/pages/user',  $vueImports, $dryRun);

// ════════════════════════════════════════════════════════════════════════════
// STEP 8 — Update routes
// ════════════════════════════════════════════════════════════════════════════
rPrint('STEP 8 - Update routes', 'head');

$routeReplacements = [
    'App\\Http\\Controllers\\Fast\\Administrasi\\AdminDashboardController'     => 'App\\Http\\Controllers\\Admin\\DashboardController',
    'App\\Http\\Controllers\\Fast\\Administrasi\\AdminSuratController'         => 'App\\Http\\Controllers\\Admin\\LetterController',
    'App\\Http\\Controllers\\Fast\\Administrasi\\TemplateManagementController' => 'App\\Http\\Controllers\\Admin\\TemplateController',
    'App\\Http\\Controllers\\Fast\\Approval\\DashboardController'              => 'App\\Http\\Controllers\\Admin\\ApprovalController',
    'App\\Http\\Controllers\\Fast\\User\\DashboardController'                  => 'App\\Http\\Controllers\\User\\DashboardController',
    'App\\Http\\Controllers\\Fast\\User\\SubmissionController'                 => 'App\\Http\\Controllers\\User\\SubmissionController',
    'App\\Http\\Controllers\\Fast\\User\\JenisSuratFieldConfigController'      => 'App\\Http\\Controllers\\User\\LetterTypeController',
    'AdminDashboardController::class'                                          => 'DashboardController::class',
    'AdminSuratController::class'                                              => 'LetterController::class',
    'TemplateManagementController::class'                                      => 'TemplateController::class',
    'JenisSuratFieldConfigController::class'                                   => 'LetterTypeController::class',
];

$routeFiles = glob($root . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . '*.php') ?: [];
foreach ($routeFiles as $absPath) {
    $rel = ltrim(str_replace($root, '', $absPath), '/\\');
    rEdit($rel, $routeReplacements, $dryRun);
}

// ════════════════════════════════════════════════════════════════════════════
// STEP 9 — Wayfinder & cache
// ════════════════════════════════════════════════════════════════════════════
rPrint('STEP 9 - Wayfinder & cache', 'head');

if (!$dryRun) {
    rPrint('Menjalankan wayfinder:generate ...', 'info');
    passthru('php artisan wayfinder:generate');
    passthru('php artisan route:clear');
    passthru('php artisan view:clear');
    passthru('php artisan config:clear');
    passthru('php artisan optimize');
    rPrint('Cache berhasil dibersihkan', 'ok');
} else {
    rPrint('[DRY RUN] Akan jalankan: wayfinder:generate, route:clear, optimize', 'skip');
}

echo "\n";
echo "=======================================================\n";
echo ($dryRun
    ? "  DRY RUN selesai. Tidak ada file yang diubah.\n"
    : "  Restructure selesai!\n");
echo "  Selanjutnya:\n";
echo "  1. Update manual (Langkah 6 di PANDUAN_DETAIL.md)\n";
echo "  2. npm run dev\n";
echo "  3. Test semua halaman\n";
echo "=======================================================\n\n";
