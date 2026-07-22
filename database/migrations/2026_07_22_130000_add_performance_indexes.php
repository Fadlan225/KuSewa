<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan index performa pada semua tabel yang sering di-query.
     * Tanpa index ini, setiap WHERE/JOIN/ORDER BY melakukan full table scan
     * yang sangat lambat saat data 1000+ records.
     */
    public function up(): void
    {
        // ─── assets ────────────────────────────────────────────────────────────
        Schema::table('assets', function (Blueprint $table) {
            // Filter utama: WHERE status = 'active' (dipakai di semua query)
            $table->index('status', 'idx_assets_status');
            // Filter gabungan: status + type (cover query home & search)
            $table->index(['status', 'asset_type_id'], 'idx_assets_status_type');
            // Index asset_type_id (join & filter by type/category)
            $table->index('asset_type_id', 'idx_assets_type');
            // Search by city (LIKE prefix + filter lokasi)
            $table->index('city', 'idx_assets_city');
            // Foreign key owner_profile_id (join ke owner_profiles)
            $table->index('owner_profile_id', 'idx_assets_owner_profile');
        });

        // ─── asset_images ───────────────────────────────────────────────────────
        Schema::table('asset_images', function (Blueprint $table) {
            // thumbnailImages eager load: JOIN asset_id + ORDER BY id (limit 3)
            // Composite index agar sort by id juga ter-cover
            $table->index(['asset_id', 'id'], 'idx_asset_images_asset_id');
        });

        // ─── asset_pricings ─────────────────────────────────────────────────────
        Schema::table('asset_pricings', function (Blueprint $table) {
            // defaultPricing, sort by price, whereHas price range
            $table->index(['asset_id', 'price'], 'idx_asset_pricings_asset_price');
        });

        // ─── bookings ───────────────────────────────────────────────────────────
        Schema::table('bookings', function (Blueprint $table) {
            // whereDoesntHave bookings: filter asset_id + booking_status
            $table->index(['asset_id', 'booking_status'], 'idx_bookings_asset_status');
            // Date range overlap check (start_date <= endDate AND end_date >= startDate)
            $table->index(['start_date', 'end_date'], 'idx_bookings_dates');
            // User bookings lookup
            $table->index('user_id', 'idx_bookings_user');
        });

        // ─── reviews ────────────────────────────────────────────────────────────
        Schema::table('reviews', function (Blueprint $table) {
            // withAvg('reviews', 'rating') via hasManyThrough → booking_id
            $table->index('booking_id', 'idx_reviews_booking');
            $table->index('user_id', 'idx_reviews_user');
        });

        // ─── favorites ──────────────────────────────────────────────────────────
        Schema::table('favorites', function (Blueprint $table) {
            // WHERE user_id + asset_id (prevent duplicate + eager load per user)
            $table->index(['user_id', 'asset_id'], 'idx_favorites_user_asset');
        });

        // ─── search_logs ────────────────────────────────────────────────────────
        Schema::table('search_logs', function (Blueprint $table) {
            // getSearchMeta: WHERE user_id + ORDER BY searched_at DESC
            $table->index(['user_id', 'searched_at'], 'idx_search_logs_user_time');
            // trending: WHERE searched_at >= subWeek() GROUP BY keyword COUNT(*)
            $table->index(['searched_at', 'keyword'], 'idx_search_logs_time_keyword');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropIndex('idx_assets_status');
            $table->dropIndex('idx_assets_status_type');
            $table->dropIndex('idx_assets_type');
            $table->dropIndex('idx_assets_city');
            $table->dropIndex('idx_assets_owner_profile');
        });

        Schema::table('asset_images', function (Blueprint $table) {
            $table->dropIndex('idx_asset_images_asset_id');
        });

        Schema::table('asset_pricings', function (Blueprint $table) {
            $table->dropIndex('idx_asset_pricings_asset_price');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('idx_bookings_asset_status');
            $table->dropIndex('idx_bookings_dates');
            $table->dropIndex('idx_bookings_user');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex('idx_reviews_booking');
            $table->dropIndex('idx_reviews_user');
        });

        Schema::table('favorites', function (Blueprint $table) {
            $table->dropIndex('idx_favorites_user_asset');
        });

        Schema::table('search_logs', function (Blueprint $table) {
            $table->dropIndex('idx_search_logs_user_time');
            $table->dropIndex('idx_search_logs_time_keyword');
        });
    }
};
