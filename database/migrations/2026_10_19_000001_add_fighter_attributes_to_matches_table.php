<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->boolean('fighter_1_palong')->default(false);
            $table->boolean('fighter_1_lambe')->default(false);
            $table->string('fighter_1_leg_color')->nullable();
            $table->boolean('fighter_2_palong')->default(false);
            $table->boolean('fighter_2_lambe')->default(false);
            $table->string('fighter_2_leg_color')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropColumn([
                'fighter_1_palong',
                'fighter_1_lambe',
                'fighter_1_leg_color',
                'fighter_2_palong',
                'fighter_2_lambe',
                'fighter_2_leg_color',
            ]);
        });
    }
};
