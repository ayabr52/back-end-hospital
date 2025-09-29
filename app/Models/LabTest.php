<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabTest extends Model
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
        'performed_by_user_id',
        'test_type',
        'test_date',
        'result_status',
        'results',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'test_date' => 'date', // تحويل تاريخ التحليل إلى كائن تاريخ
        'results' => 'array', // يمكن تحويل حقل النتائج إلى مصفوفة (JSON) تلقائياً
    ];

    /**
     * Get the patient that the lab test belongs to.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the doctor who requested the lab test.
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Get the user who performed/entered the lab test results.
     */
    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by_user_id');
    }
}