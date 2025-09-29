<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'prescription_date',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'prescription_date' => 'date', // تحويل تاريخ الوصفة إلى كائن تاريخ
    ];

    /**
     * Get the patient that owns the prescription.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the doctor who wrote the prescription.
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * The medicines that belong to the prescription.
     */
    public function medicines()
    {
        return $this->belongsToMany(Medicine::class, 'prescription_medicine')
                    ->withPivot('dosage', 'frequency', 'duration', 'instructions')
                    ->withTimestamps();
    }
}
