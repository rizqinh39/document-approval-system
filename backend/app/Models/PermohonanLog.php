<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermohonanLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'permohonan_id',
        'actor_id',
        'action',
        'status_from',
        'status_to',
        'notes',
    ];

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class, 'permohonan_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
