<?php

// app/Models/Laboratory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laboratory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'capacity',
        'materials_supplied',
        'is_available',
        'observation',
    ];
    
    // O campo is_available é um booleano
    protected $casts = [
        'is_available' => 'boolean',
    ];
}
