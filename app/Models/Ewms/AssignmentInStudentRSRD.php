<?php
// app/Models/Ewms/AssignmentInStudentRSRD.php

namespace App\Models\Ewms;

use Illuminate\Database\Eloquent\Model;

class AssignmentInStudentRSRD extends Model
{
    protected $connection = 'ewms';
    protected $table = 'table_assignment_in_student_rs_rd';
    protected $primaryKey = 'id';
    public $timestamps = false;
    
    protected $casts = [
        'etl_1' => 'float',
        'etl_2' => 'float',
        'etl_3' => 'float',
        'etl_4' => 'float',
        'etl_5' => 'float',
        'f_id' => 'integer',
        'sem_id' => 'integer',
        'w_version' => 'integer',
    ];
    
    public function faculty()
    {
        return $this->belongsTo(FacultyProfile::class, 'f_id', 'id');
    }
    
    public function semester()
    {
        return $this->belongsTo(Semester::class, 'sem_id', 'sem_id');
    }
    
    // Get total ETL
    public function getTotalEtlAttribute()
    {
        return $this->etl_1 + $this->etl_2 + $this->etl_3 + $this->etl_4 + $this->etl_5;
    }
}