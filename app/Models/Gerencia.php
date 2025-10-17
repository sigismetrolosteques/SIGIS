<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gerencia extends Model
{
    protected $table = 'gerencias'; // <- solo si la tabla no sigue convención

    protected $fillable = [
        'name',
    ];
}
