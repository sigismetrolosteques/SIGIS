<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudStatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'solicitud_id',
        'status',
        'action',
        'justification',
        'changed_by',
        'attachment_path',
        'approved_quantity',
    ];

    protected $casts = [
        'approved_quantity' => 'integer',
    ];

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
