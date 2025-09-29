<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'generic_name',
        'manufacturer',
        'dosage_form',
        'strength',
        'stock_quantity',
        'price',
        'expiry_date',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expiry_date' => 'date', // تحويل تاريخ انتهاء الصلاحية إلى كائن تاريخ
    ];

    /**
     * The prescriptions that belong to the medicine.
     */
    public function prescriptions()
    {
        return $this->belongsToMany(Prescription::class, 'prescription_medicine')
                    ->withPivot('dosage', 'frequency', 'duration', 'instructions')
                    ->withTimestamps();
    }
}
