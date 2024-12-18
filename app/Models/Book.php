<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'is_reserved',
        'release_date',
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
