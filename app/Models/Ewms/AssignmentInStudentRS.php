<?php
// app/Models/Ewms/AssignmentInStudentRS.php

namespace App\Models\Ewms;

use Illuminate\Database\Eloquent\Model;

class AssignmentInStudentRS extends Model
{
    protected $connection = 'ewms';
    protected $table = 'table_assignment_in_student_rs';
    protected $primaryKey = 'id';
    public $timestamps = false;
    
    protected $casts = [
        'etl' => 'float',
        'f_id' => 'integer',
        'sem_id' => 'integer',
        'w_version' => 'integer',
    ];
    
    protected $fillable = [
        'id',
        'f_id',
        'sem_id',
        'w_version',
        'mode',
        'degree',
        'position',
        'name',
        'etl',
    ];
    
    public function faculty()
    {
        return $this->belongsTo(FacultyProfile::class, 'f_id', 'id');
    }
    
    public function semester()
    {
        return $this->belongsTo(Semester::class, 'sem_id', 'sem_id');
    }
}