<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class TipItem extends Model
{
    protected $fillable = ['tip_id', 'content'];

    public function tip()
    {
        return $this->belongsTo(Tip::class);
    }
}
