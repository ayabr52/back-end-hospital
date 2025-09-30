<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nurse extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
         'favorite_club',
        'name',
        'phone',
        'specialty',
        'bio',
        'image',
        'department_id',
    ];

    /**
     * Get the user that owns the nurse profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the department that the nurse belongs to.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the appointments processed by this nurse.
     */
    public function processedAppointments()
    {
        return $this->hasMany(Appointment::class, 'processed_by_nurse_id');
    }
}

