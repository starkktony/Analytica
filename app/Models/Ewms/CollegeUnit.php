<?php
// app/Models/Ewms/CollegeUnit.php

namespace App\Models\Ewms;

use Illuminate\Database\Eloquent\Model;

class CollegeUnit extends Model
{
    // Connect to eWMS database
    protected $connection = 'ewms';
    
    protected $table = 'table_college_unit';
    protected $primaryKey = 'c_u_id';
    public $timestamps = false;
    
    protected $fillable = [
        'c_u_id',
        'college_acro',
        'college_unit',
        'dean_id'
    ];
    
    public function departments()
    {
        return $this->hasMany(Department::class, 'college_id', 'c_u_id');
    }
    
    public function faculty()
    {
        return $this->hasMany(FacultyProfile::class, 'college', 'c_u_id');
    }
}