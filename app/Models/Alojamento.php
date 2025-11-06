<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alojamento extends Model
{
    use HasFactory;

    // Definindo os campos que podem ser preenchidos
    protected $fillable = [
        'titulo',
        'descricao',
        'preco_noite',
    ];

    // Definindo os campos que não podem ser preenchidos
    protected $guarded = ['id'];
}