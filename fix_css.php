<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

\App\Models\SuratTemplate::whereNotNull('css_style')->get()->each(function($t) {
    $t->css_style = str_replace('.page {', '.surat-content {', $t->css_style);
    $t->save();
});

echo 'Done!';