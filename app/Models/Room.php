<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'room_number',
        'type',
        'capacity',
        'status',
        'notes',
        'department_id',
    ];

    /**
     * Get the department that the room belongs to.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the patients currently in the room (if you implement patient-room assignment).
     * This is a placeholder for future functionality.
     */
    // public function patients()
    // {
    //     return $this->hasMany(Patient::class);
    // }
}