<?php
namespace App\Models\Ewms;

use Illuminate\Database\Eloquent\Model;

class ClassSchedule extends Model
{
    protected $connection = 'ewms';
    protected $table = 'table_class_schedule';
    protected $primaryKey = 'sched_id';
    public $timestamps = false;
    
    protected $casts = [
        'no_of_student' => 'integer',
        'hours_per_week' => 'float',
        'atl' => 'float',
        'department_id' => 'integer',
        'sem_id' => 'integer',
    ];
    
    public function faculty()
    {
        return $this->belongsTo(FacultyProfile::class, 'f_id', 'id');
    }
    
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }
    
    public function semester()
    {
        return $this->belongsTo(Semester::class, 'sem_id', 'sem_id');
    }
}