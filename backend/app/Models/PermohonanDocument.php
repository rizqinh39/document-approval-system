<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermohonanDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'permohonan_id',
        'original_name',
        'file_path',
        'file_size',
        'mime_type',
    ];

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class, 'permohonan_id');
    }
}
