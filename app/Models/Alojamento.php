<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Alojamento extends Model
{
    use HasFactory;

    // Definindo os campos que podem ser preenchidos
    protected $fillable = [
        'titulo',
        'descricao',
        'preco_noite',
    ];
    protected $appends = ['foto_capa_url'];

    // Definindo os campos que não podem ser preenchidos
    protected $guarded = ['id'];

    public function reservas() { return $this->hasMany(Reserva::class); }
    public function comentarios() { return $this->hasMany(Comentario::class); }
    public function fotos() { return $this->hasMany(Foto::class); }
    public function videos() { return $this->hasMany(Video::class); }
    public function bloqueios() { return $this->hasMany(Bloqueio::class); }


    public function getFotoCapaUrlAttribute(): ?string
    {
        // garante que não faz N+1 se já vier com fotos carregadas
        $foto = $this->relationLoaded('fotos')
            ? $this->fotos->sortBy('id')->first()
            : $this->fotos()->orderBy('id')->first();

        if (!$foto?->path) return null;

        return Storage::disk('public')->url($foto->path);
    }
}
    
