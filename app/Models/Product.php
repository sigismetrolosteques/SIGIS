<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'sub_area',
        'tipo_material',
        'marcas_sugeridas',
        'modelo',
        'dimensiones',
        'especificaciones_tecnicas',
        'image',
        'description',
    ];

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }
}
