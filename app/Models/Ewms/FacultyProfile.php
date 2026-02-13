<?php
// app/Models/Ewms/FacultyProfile.php

namespace App\Models\Ewms;

use Illuminate\Database\Eloquent\Model;

class FacultyProfile extends Model
{
    // Connect to eWMS database
    protected $connection = 'ewms';
    
    // Specify the exact table name
    protected $table = 'table_faculty_profile';
    
    // Primary key
    protected $primaryKey = 'id';
    
    // Disable timestamps (eWMS doesn't have created_at/updated_at)
    public $timestamps = false;
    
    // Cast columns
    protected $casts = [
        'status' => 'integer',
        'gender' => 'integer',
    ];
    
    // Relationships
    public function department()
    {
        return $this->belongsTo(Department::class, 'department', 'department_id');
    }
    
    public function collegeUnit()
    {
        return $this->belongsTo(CollegeUnit::class, 'college', 'c_u_id');
    }
    
    public function facultyStatus()
    {
        return $this->hasOne(FacultyStatus::class, 'f_id', 'id');
    }
}