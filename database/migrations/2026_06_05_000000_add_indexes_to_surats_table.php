<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surats', function (Blueprint $table) {
            if (! $this->hasIndex('surats', 'surats_status_index')) {
                $table->index('status', 'surats_status_index');
            }
            if (! $this->hasIndex('surats', 'surats_tanggal_pengajuan_index')) {
                $table->index('tanggal_pengajuan', 'surats_tanggal_pengajuan_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('surats', function (Blueprint $table) {
            $table->dropIndex('surats_status_index');
            $table->dropIndex('surats_tanggal_pengajuan_index');
        });
    }

    private function hasIndex(string $table, string $index): bool
    {
        return \Illuminate\Support\Facades\DB::select(
            "SELECT 1 FROM information_schema.STATISTICS WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?",
            [$table, $index]
        ) !== [];
    }
};
