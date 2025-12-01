<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Foto extends Model
{
    use HasFactory;

    protected $fillable = [
        'alojamento_id',
        'path',
        'descricao',
        'ordem',
    ];

    public function alojamento()
    {
        return $this->belongsTo(Alojamento::class);
    }
}
