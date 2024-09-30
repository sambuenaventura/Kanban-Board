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
            // Add the board_user_id column and reference it to the board_user table
            if (!Schema::hasColumn('tasks', 'board_user_id')) {
                $table->foreignId('board_user_id')
                    ->constrained('board_users') // Reference board_user table
                    ->onDelete('cascade');
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
        });
    }
    
    
};
