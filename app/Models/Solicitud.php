<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    use HasFactory;

    // 👇 Forzar el nombre correcto
    protected $table = 'solicitudes';

    protected $fillable = [
        'code',
        'product_id',
        'inventory_id',
    'quantity',
        'created_by',
        'from_gerencia_id',
        'to_gerencia_id',
        'status',
        'description',
    ];

    // Relaciones
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

  // en el modelo Solicitud.php
public function fromGerencia()
{
    return $this->belongsTo(Gerencia::class, 'from_gerencia_id');
}


    public function toGerencia()
    {
        return $this->belongsTo(Gerencia::class, 'to_gerencia_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
