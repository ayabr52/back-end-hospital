<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'patient_id',
        'issued_by_user_id',
        'invoice_number',
        'total_amount',
        'paid_amount',
        'status',
        'description',
    ];

    /**
     * Get the patient that owns the invoice.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the user who issued the invoice.
     */
    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by_user_id');
    }
}