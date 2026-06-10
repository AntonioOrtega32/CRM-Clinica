<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaAssessmentEvent extends Model
{
    protected $table = 'sa_assessment_events';
    public $timestamps = false;

    protected $fillable = [
        'event_id', 'seller'
    ];

    public function event()
    {
        return $this->belongsTo(SaEvent::class, 'event_id');
    }

    
}
