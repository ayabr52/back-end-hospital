<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalRecord extends Model
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
        'record_date',
        'diagnosis',
        'treatment',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'record_date' => 'date', // تحويل تاريخ السجل إلى كائن تاريخ
    ];

    /**
     * Get the patient that owns the medical record.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the doctor who created the medical record.
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
  /**
   * نربط السجل الطبي مع الوصفة الطبية 
   */

    public function prescription()
{
    return $this->hasOne(Prescription::class);
}

}
