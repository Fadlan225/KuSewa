<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menambahkan index performa untuk query-query berat di homepage:
     *
     * 1. assets (status, asset_type_id, city_code)
     *    → dipakai di buildDynamicSections: WHERE status='approved' AND asset_type_id=? AND city_code=?
     *    → supersedes idx_assets_status_type (status, asset_type_id) yang lebih sempit
     *
     * 2. assets (province_code)
     *    → dipakai di GROUP BY combinations query (buildDynamicSections JOIN provinces)
     *
     * 3. asset_images (asset_id, is_thumbnail)
     *    → dipakai di: WHERE asset_id=? AND is_thumbnail=1 ORDER BY id LIMIT 1
     *    → lebih efisien dari idx_asset_images_asset_id (asset_id, id) untuk query thumbnail
     *
     * 4. asset_views (user_id, last_viewed)
     *    → dipakai di "Terakhir Dilihat": WHERE user_id=? ORDER BY last_viewed DESC LIMIT 10
     *    → unique index (user_id, asset_id) tidak efisien untuk ordering by last_viewed
     */
    public function up(): void
    {
        // 1. Compound index untuk query assets per type + city + status
        if (!$this->indexExists('assets', 'idx_assets_status_type_city')) {
            Schema::table('assets', function (Blueprint $table) {
                $table->index(['status', 'asset_type_id', 'city_code'], 'idx_assets_status_type_city');
            });
        }

        // 2. Index province_code untuk GROUP BY di combinations query
        if (!$this->indexExists('assets', 'idx_assets_province')) {
            Schema::table('assets', function (Blueprint $table) {
                $table->index('province_code', 'idx_assets_province');
            });
        }

        // 3. Compound index untuk lookup thumbnail per asset
        if (!$this->indexExists('asset_images', 'idx_asset_images_asset_thumbnail')) {
            Schema::table('asset_images', function (Blueprint $table) {
                $table->index(['asset_id', 'is_thumbnail'], 'idx_asset_images_asset_thumbnail');
            });
        }

        // 4. Compound index untuk "Terakhir Dilihat" per user, ordered by waktu
        if (!$this->indexExists('asset_views', 'idx_asset_views_user_last_viewed')) {
            Schema::table('asset_views', function (Blueprint $table) {
                $table->index(['user_id', 'last_viewed'], 'idx_asset_views_user_last_viewed');
            });
        }
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_assets_status_type_city');
            $table->dropIndexIfExists('idx_assets_province');
        });

        Schema::table('asset_images', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_asset_images_asset_thumbnail');
        });

        Schema::table('asset_views', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_asset_views_user_last_viewed');
        });
    }

    /**
     * Cek apakah index sudah ada untuk menghindari error duplikasi.
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = DB::select(
            "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
            [$indexName]
        );
        return !empty($indexes);
    }
};
