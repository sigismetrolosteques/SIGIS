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
        'destination_decision',
        'destination_decision_by',
        'destination_decision_at',
        'destination_justification',
    ];

    // Relaciones
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

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

    public function destinationDecisionUser()
    {
        return $this->belongsTo(User::class, 'destination_decision_by');
    }

    public function statusLogs()
    {
        return $this->hasMany(SolicitudStatusLog::class)->orderBy('created_at');
    }

    public function latestStatusLog()
    {
        return $this->hasOne(SolicitudStatusLog::class)->latestOfMany();
    }
}
