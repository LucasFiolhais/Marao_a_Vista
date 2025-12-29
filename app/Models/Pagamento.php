<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pagamento extends Model
{
    use HasFactory;
    protected $fillable = [
        'reserva_id',
        'valor',
        'estado',
        'payment_key',
    ];

    public function reserva()
    {
        return $this->belongsTo(Reserva::class);
    }
}
