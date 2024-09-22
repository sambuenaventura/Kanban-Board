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
        Schema::table('tasks', function (Blueprint $table) {
            // Drop the user_id column and its foreign key constraint
            if (Schema::hasColumn('tasks', 'user_id')) {
                $table->dropForeign(['user_id']);  // Drop foreign key
                $table->dropColumn('user_id');     // Drop user_id column
            }
    
            // Add the board_user_id column and reference it to the board_user table
            if (!Schema::hasColumn('tasks', 'board_user_id')) {
                $table->foreignId('board_user_id')
                    ->constrained('board_user') // Reference board_user table
                    ->onDelete('cascade');      // Cascade on delete
            }
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Drop the foreign key for board_user_id
            if (Schema::hasColumn('tasks', 'board_user_id')) {
                $table->dropForeign(['board_user_id']);
                $table->dropColumn('board_user_id');
            }
    
            // Re-add the user_id column and foreign key constraint
            if (!Schema::hasColumn('tasks', 'user_id')) {
                $table->foreignId('user_id')
                    ->constrained()
                    ->onDelete('cascade');
            }
        });
    }
    
    
};
