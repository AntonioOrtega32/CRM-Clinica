<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaEvent extends Model
{
    protected $table = 'sa_events';
    protected $primaryKey = 'id';
    public $timestamps = false; // Ajusta según tu tabla

    protected $fillable = [
        'event_type', 'attendance_type', 'title', 'start', 'end', 'description',
        'clinic', 'status', 'qualy', 'uploaded_by', 'review_time', 'protocolo_alejandro'
    ];

    // Relación con usuarios
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Relación con assessment events
    public function assessment()
    {
        return $this->hasOne(SaAssessmentEvent::class, 'event_id');
    }
    
}
