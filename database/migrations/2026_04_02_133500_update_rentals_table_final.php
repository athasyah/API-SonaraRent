<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First drop guarantees to avoid constraint issues if we need to
        Schema::dropIfExists('guarantees');

        // Modify the status ENUM directly avoiding Doctrine DBAL requirement
        DB::statement("ALTER TABLE rentals MODIFY COLUMN status ENUM('pending', 'reserved', 'ongoing', 'returned', 'cancelled') NOT NULL");

        Schema::table('rentals', function (Blueprint $table) {
            $table->enum('payment_method', ['cash', 'gateway'])->default('cash')->after('total_price');
            $table->enum('payment_status', ['unpaid', 'pending', 'paid', 'failed'])->default('unpaid')->after('payment_method');
            $table->string('guarantee_type')->nullable()->after('payment_status');
            $table->string('guarantee_image')->nullable()->after('guarantee_type');
            $table->timestamp('guarantee_taken_at')->nullable()->after('guarantee_image');
            $table->foreignUuid('guarantee_taken_by')->nullable()->constrained('users')->after('guarantee_taken_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->dropForeign(['guarantee_taken_by']);
            $table->dropColumn([
                'payment_method', 
                'payment_status', 
                'guarantee_type', 
                'guarantee_image', 
                'guarantee_taken_at', 
                'guarantee_taken_by'
            ]);
        });

        DB::statement("ALTER TABLE rentals MODIFY COLUMN status ENUM('pending','reserved', 'approved', 'ongoing', 'returned', 'cancelled') NOT NULL");

        Schema::create('guarantees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('rental_id')->nullable()->constrained();
            $table->foreignUuid('user_id')->nullable()->constrained();
            $table->string('type');
            $table->string('note')->nullable();
            $table->enum('status', ['pending','held', 'returned']);
            $table->timestamps();
        });
    }
};
