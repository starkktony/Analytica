<?php
// app/Models/Ewms/ScheduleOfStudentResearchConsultation.php

namespace App\Models\Ewms;

use Illuminate\Database\Eloquent\Model;

class ScheduleOfStudentResearchConsultation extends Model
{
    protected $connection = 'ewms';
    protected $table = 'table_schedule_of_student_research_consultation';
    protected $primaryKey = 'id';
    public $timestamps = false;
    
    protected $casts = [
        'f_id' => 'integer',
        'sem_id' => 'integer',
        'w_version' => 'integer',
    ];
    
    protected $fillable = [
        'id',
        'f_id',
        'sem_id',
        'w_version',
        'days',
        'time',
        'room',
    ];
    
    public function faculty()
    {
        return $this->belongsTo(FacultyProfile::class, 'f_id', 'id');
    }
    
    public function semester()
    {
        return $this->belongsTo(Semester::class, 'sem_id', 'sem_id');
    }
    
    // Parse days string into array
    public function getDaysArrayAttribute()
    {
        if (empty($this->days)) {
            return [];
        }
        
        // Handle different formats: "MWF", "M,W,F", "Mon, Wed, Fri", etc.
        $days = strtoupper($this->days);
        $days = str_replace(['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'], 
                           ['M', 'T', 'W', 'TH', 'F', 'S', 'SU'], $days);
        
        // Split by common separators
        return preg_split('/[,\s]+/', $days);
    }
}