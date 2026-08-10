<?php

use App\Enums\AdminRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('admin_role')->nullable()->after('email');
            $table->softDeletes();
        });

        // Backfill any existing local/dev accounts as Viewer rather than
        // leaving them with a null role, since a null admin_role should mean
        // "not an admin account at all" going forward (docs/architecture/authorization-model.md §2).
        DB::table('users')->whereNull('admin_role')->update([
            'admin_role' => AdminRole::Viewer->value,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('admin_role');
            $table->dropSoftDeletes();
        });
    }
};
