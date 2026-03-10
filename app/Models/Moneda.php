<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Moneda extends SoftlandModel
{
    use HasFactory;
    protected $table = 'AMS.moneda'; // Nombre de tu tabla

    protected $fillable = [
        'moneda',
    ];
}
