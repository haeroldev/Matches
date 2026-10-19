<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FightMatch extends Model
{
    use HasFactory;

    protected $table = 'matches';

    protected $fillable = [
        'fighter_1',
        'fighter_2',
        'winner',
        'image_path',
        'description',
        'fighter_1_palong',
        'fighter_1_lambe',
        'fighter_1_leg_color',
        'fighter_2_palong',
        'fighter_2_lambe',
        'fighter_2_leg_color',
    ];

    protected function casts(): array
    {
        return [
            'fighter_1_palong' => 'boolean',
            'fighter_1_lambe' => 'boolean',
            'fighter_2_palong' => 'boolean',
            'fighter_2_lambe' => 'boolean',
        ];
    }
}
