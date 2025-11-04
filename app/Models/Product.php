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
        'ubicaciones',
        'tipo_material',
        'marcas_sugeridas',
        'modelo',
        'dimensiones',
        'especificaciones_tecnicas',
        'image',
        'description',
    ];

    protected $casts = [
        'ubicaciones' => 'array',
    ];

    public function inventories()
{
    return $this->hasMany(Inventory::class);
}

}
