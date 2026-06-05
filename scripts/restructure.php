<?php
$dryRun = in_array('--dry-run', $argv ?? [], true);
$root   = rtrim(getcwd(), '/\\');

echo "\n=======================================================\n";
echo "  FAST Academic - Restructure Script\n";
echo ($dryRun ? "  MODE: DRY RUN\n" : "  MODE: LIVE\n");
echo "=======================================================\n\n";

function rPrint(string $msg, string $type = 'info'): void {
    $p = match($type) {
        'ok'   => "[OK]  ", 'move' => "[->]  ", 'edit' => "[~]   ",
        'skip' => "[..]  ", 'head' => "\n--- ", default => "      ",
    };
    echo "{$p} {$msg}\n";
}

function rMkdir(string $path, bool $dry): void {
    if (!is_dir($path)) {
        if (!$dry) mkdir($path, 0755, true);
        rPrint("mkdir {$path}", 'ok');
    }
}

function rMove(string $from, string $to, bool $dry): bool {
    global $root;
    $s = DIRECTORY_SEPARATOR;
    $af = $root.$s.str_replace(['/','\\'  ], $s, $from);
    $at = $root.$s.str_replace(['/','\\'  ], $s, $to);
    if (!file_exists($af)) { rPrint("SKIP: {$from}", 'skip'); return false; }
    rMkdir(dirname($at), $dry);
    if (!$dry) rename($af, $at);
    rPrint("{$from}", 'move');
    echo "        -> {$to}\n";
    return true;
}

function rEdit(string $file, array $rep, bool $dry): void {
    global $root;
    $s = DIRECTORY_SEPARATOR;
    $ap = $root.$s.str_replace(['/','\\'  ], $s, $file);
    if (!file_exists($ap)) { rPrint("SKIP: {$file}", 'skip'); return; }
    $old = file_get_contents($ap);
    $new = str_replace(array_keys($rep), array_values($rep), $old);
    if ($old === $new) { rPrint("No change: {$file}", 'skip'); return; }
    if (!$dry) file_put_contents($ap, $new);
    rPrint("Updated: {$file}", 'edit');
}

function rEditDir(string $dir, array $rep, bool $dry): void {
    global $root;
    $s = DIRECTORY_SEPARATOR;
    $ad = $root.$s.str_replace(['/','\\'  ], $s, $dir);
    if (!is_dir($ad)) { rPrint("SKIP dir: {$dir}", 'skip'); return; }
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($ad, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        if (in_array($f->getExtension(), ['vue','ts'], true)) {
            $rel = ltrim(str_replace($root, '', $f->getPathname()), '/\\');
            rEdit($rel, $rep, $dry);
        }
    }
}

// STEP 1 - Layout
rPrint('STEP 1 - Vue Layout', 'head');
rMove('resources/js/layouts/fast/AdminWorkspaceLayout.vue','resources/js/layouts/AdminLayout.vue',$dryRun);

// STEP 2 - Components
rPrint('STEP 2 - Vue Components', 'head');
rMove('resources/js/components/Fast/AdminSuratStepIndicator.vue','resources/js/components/admin/LetterStepIndicator.vue',$dryRun);
rMove('resources/js/components/Fast/AdminSuratDynamicField.vue','resources/js/components/admin/DynamicField.vue',$dryRun);
rMove('resources/js/components/Fast/DynamicFormModal.vue','resources/js/components/admin/DynamicFormModal.vue',$dryRun);

// STEP 3 - Pages
rPrint('STEP 3 - Vue Pages', 'head');
rMove('resources/js/pages/fast/administrasi/DashboardAdmin.vue','resources/js/pages/admin/dashboard/Index.vue',$dryRun);
rMove('resources/js/pages/fast/administrasi/surat/Create.vue','resources/js/pages/admin/letters/Create.vue',$dryRun);
rMove('resources/js/pages/fast/administrasi/surat/Form.vue','resources/js/pages/admin/letters/Form.vue',$dryRun);
rMove('resources/js/pages/fast/administrasi/surat/Preview.vue','resources/js/pages/admin/letters/Preview.vue',$dryRun);
rMove('resources/js/pages/fast/administrasi/Templates.vue','resources/js/pages/admin/templates/Index.vue',$dryRun);
rMove('resources/js/pages/fast/approval/Dashboard.vue','resources/js/pages/admin/approval/Index.vue',$dryRun);

// STEP 4 - Controllers
rPrint('STEP 4 - Controllers', 'head');
rMove('app/Http/Controllers/Fast/Administrasi/AdminDashboardController.php','app/Http/Controllers/Admin/DashboardController.php',$dryRun);
rMove('app/Http/Controllers/Fast/Administrasi/AdminSuratController.php','app/Http/Controllers/Admin/LetterController.php',$dryRun);
rMove('app/Http/Controllers/Fast/Administrasi/TemplateManagementController.php','app/Http/Controllers/Admin/TemplateController.php',$dryRun);
rMove('app/Http/Controllers/Fast/Approval/DashboardController.php','app/Http/Controllers/Admin/ApprovalController.php',$dryRun);
rMove('app/Http/Controllers/Fast/User/DashboardController.php','app/Http/Controllers/User/DashboardController.php',$dryRun);
rMove('app/Http/Controllers/Fast/User/SubmissionController.php','app/Http/Controllers/User/SubmissionController.php',$dryRun);
rMove('app/Http/Controllers/Fast/User/JenisSuratFieldConfigController.php','app/Http/Controllers/User/LetterTypeController.php',$dryRun);

// STEP 5 - Namespace
rPrint('STEP 5 - Namespace Controllers', 'head');
rEdit('app/Http/Controllers/Admin/DashboardController.php',['namespace App\\Http\\Controllers\\Fast\\Administrasi;'=>'namespace App\\Http\\Controllers\\Admin;','class AdminDashboardController'=>'class DashboardController'],$dryRun);
rEdit('app/Http/Controllers/Admin/LetterController.php',['namespace App\\Http\\Controllers\\Fast\\Administrasi;'=>'namespace App\\Http\\Controllers\\Admin;','class AdminSuratController'=>'class LetterController'],$dryRun);
rEdit('app/Http/Controllers/Admin/TemplateController.php',['namespace App\\Http\\Controllers\\Fast\\Administrasi;'=>'namespace App\\Http\\Controllers\\Admin;','class TemplateManagementController'=>'class TemplateController'],$dryRun);
rEdit('app/Http/Controllers/Admin/ApprovalController.php',['namespace App\\Http\\Controllers\\Fast\\Approval;'=>'namespace App\\Http\\Controllers\\Admin;','class DashboardController'=>'class ApprovalController'],$dryRun);
rEdit('app/Http/Controllers/User/DashboardController.php',['namespace App\\Http\\Controllers\\Fast\\User;'=>'namespace App\\Http\\Controllers\\User;'],$dryRun);
rEdit('app/Http/Controllers/User/SubmissionController.php',['namespace App\\Http\\Controllers\\Fast\\User;'=>'namespace App\\Http\\Controllers\\User;'],$dryRun);
rEdit('app/Http/Controllers/User/LetterTypeController.php',['namespace App\\Http\\Controllers\\Fast\\User;'=>'namespace App\\Http\\Controllers\\User;','class JenisSuratFieldConfigController'=>'class LetterTypeController'],$dryRun);

// STEP 6 - Inertia render
rPrint('STEP 6 - Inertia render path', 'head');
rEdit('app/Http/Controllers/Admin/DashboardController.php',["'fast/administrasi/DashboardAdmin'"=>"'admin/dashboard/Index'"],$dryRun);
rEdit('app/Http/Controllers/Admin/LetterController.php',["'fast/administrasi/surat/Create'"=>"'admin/letters/Create'","'fast/administrasi/surat/Form'"=>"'admin/letters/Form'","'fast/administrasi/surat/Preview'"=>"'admin/letters/Preview'"],$dryRun);
rEdit('app/Http/Controllers/Admin/TemplateController.php',["'fast/administrasi/Templates'"=>"'admin/templates/Index'"],$dryRun);
rEdit('app/Http/Controllers/Admin/ApprovalController.php',["'fast/approval/Dashboard'"=>"'admin/approval/Index'"],$dryRun);

// STEP 7 - Vue imports
rPrint('STEP 7 - Vue imports', 'head');
$vi = [
    "from '@/layouts/fast/AdminWorkspaceLayout.vue'"=>"from '@/layouts/AdminLayout.vue'",
    'AdminWorkspaceLayout'=>'AdminLayout',
    "from '@/components/Fast/AdminSuratStepIndicator.vue'"=>"from '@/components/admin/LetterStepIndicator.vue'",
    "from '@/components/Fast/AdminSuratDynamicField.vue'"=>"from '@/components/admin/DynamicField.vue'",
    'AdminSuratStepIndicator'=>'LetterStepIndicator',
    'AdminSuratDynamicField'=>'DynamicField',
];
rEditDir('resources/js/pages/admin',$vi,$dryRun);
rEditDir('resources/js/pages/user',$vi,$dryRun);

// STEP 8 - Routes
rPrint('STEP 8 - Routes', 'head');
$rr = [
    'App\\Http\\Controllers\\Fast\\Administrasi\\AdminDashboardController'=>'App\\Http\\Controllers\\Admin\\DashboardController',
    'App\\Http\\Controllers\\Fast\\Administrasi\\AdminSuratController'=>'App\\Http\\Controllers\\Admin\\LetterController',
    'App\\Http\\Controllers\\Fast\\Administrasi\\TemplateManagementController'=>'App\\Http\\Controllers\\Admin\\TemplateController',
    'App\\Http\\Controllers\\Fast\\Approval\\DashboardController'=>'App\\Http\\Controllers\\Admin\\ApprovalController',
    'App\\Http\\Controllers\\Fast\\User\\JenisSuratFieldConfigController'=>'App\\Http\\Controllers\\User\\LetterTypeController',
    'AdminDashboardController::class'=>'DashboardController::class',
    'AdminSuratController::class'=>'LetterController::class',
    'TemplateManagementController::class'=>'TemplateController::class',
    'JenisSuratFieldConfigController::class'=>'LetterTypeController::class',
];
foreach (glob($root.DIRECTORY_SEPARATOR.'routes'.DIRECTORY_SEPARATOR.'*.php') ?: [] as $rf) {
    rEdit(ltrim(str_replace($root,'',$rf),'/\\'), $rr, $dryRun);
}

// STEP 9 - Wayfinder & cache
rPrint('STEP 9 - Wayfinder & cache', 'head');
if (!$dryRun) {
    passthru('php artisan wayfinder:generate');
    passthru('php artisan route:clear');
    passthru('php artisan view:clear');
    passthru('php artisan optimize');
} else {
    rPrint('[DRY RUN] Akan jalankan: wayfinder:generate, route:clear, optimize', 'skip');
}

echo "\n=======================================================\n";
echo ($dryRun ? "  DRY RUN selesai.\n" : "  Selesai!\n");
echo "  Selanjutnya: update manual (Langkah 6) lalu npm run dev\n";
echo "=======================================================\n\n";