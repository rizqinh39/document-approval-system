<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permohonan extends Model
{
    use HasFactory;

    protected $table = 'permohonan';

    protected $fillable = [
        'nomor_permohonan',
        'pemohon_id',
        'judul_project',
        'deskripsi',
        'status',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function pemohon()
    {
        return $this->belongsTo(User::class, 'pemohon_id');
    }

    public function documents()
    {
        return $this->hasMany(PermohonanDocument::class, 'permohonan_id');
    }

    public function logs()
    {
        return $this->hasMany(PermohonanLog::class, 'permohonan_id');
    }
}
