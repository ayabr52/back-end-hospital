<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tip extends Model
{
    protected $fillable = ['title', 'description'];

    public function items()
    {
        return $this->hasMany(TipItem::class);
    }
}
