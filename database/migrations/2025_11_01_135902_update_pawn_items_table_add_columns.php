<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pawn_items', function (Blueprint $table) {
            // Add missing columns
            $table->string('title')->after('id');
            $table->text('description')->nullable()->after('title');
            $table->foreignId('customer_id')
                  ->after('description')
                  ->constrained('users')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();
            $table->decimal('price', 12, 2)->default(0)->after('customer_id');
            $table->decimal('interest_cost', 12, 2)->default(0)->after('price');
            $table->date('due_date')->nullable()->after('interest_cost');
            $table->enum('status', ['active', 'redeemed', 'forfeited'])
                  ->default('active')
                  ->after('due_date');

            // Add indexes
            $table->index(['customer_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pawn_items', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropIndex(['customer_id', 'status']);

            $table->dropColumn([
                'title',
                'description',
                'customer_id',
                'price',
                'interest_cost',
                'due_date',
                'status',
            ]);
        });
    }
};
