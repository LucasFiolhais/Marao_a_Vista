<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

use App\Models\Foto;
use App\Models\Reserva;
use App\Models\Comentario;
use App\Models\Video;
use App\Models\Bloqueio;

class Alojamento extends Model
{
    use HasFactory;

    /**
     * Campos preenchíveis
     */
    protected $fillable = [
        'titulo',
        'descricao',
        'preco_noite',
        'capacidade',
    ];

    /**
     * Atributos calculados que vão para o JSON
     */
    protected $appends = [
        'foto_capa_url',
        'preco',
    ];

    /* =======================
     | Relações
     ======================= */
    public function reservas()
    {
        return $this->hasMany(Reserva::class);
    }

    public function comentarios()
    {
        return $this->hasMany(Comentario::class);
    }

    public function fotos()
    {
        return $this->hasMany(Foto::class);
    }

    public function videos()
    {
        return $this->hasMany(Video::class);
    }

    public function bloqueios()
    {
        return $this->hasMany(Bloqueio::class);
    }

    /* =======================
     | Accessors
     ======================= */

    // Foto de capa (primeira foto)
    public function getFotoCapaUrlAttribute(): ?string
    {
        $foto = $this->relationLoaded('fotos')
            ? $this->fotos->sortBy('ordem')->first()
            : $this->fotos()->orderBy('ordem')->first();

        return $foto?->path
            ? Storage::disk('public')->url($foto->path)
            : null;
    }

    // Preço exposto para o frontend
    public function getPrecoAttribute(): float
    {
        return (float) $this->preco_noite;
    }
}
