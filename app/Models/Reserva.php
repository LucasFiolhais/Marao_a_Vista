<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    protected $fillable = [
        'user_id',
        'alojamento_id',
        'checkin',
        'checkout',
        'hospedes',
        'total',
        'estado',
        'referencia',
        'observacoes'
    ];

    private function calcularPreco($checkin, $checkout, $alojamento)
    {
        $inicio = \Carbon\Carbon::parse($checkin);
        $fim = \Carbon\Carbon::parse($checkout);

        $noites = $inicio->diffInDays($fim);

        if ($noites <= 0) {
         $noites = 1;
        }

        return $noites * (float) $alojamento->preco_noite;
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function alojamento()
    {
        return $this->belongsTo(Alojamento::class);
    }
}


