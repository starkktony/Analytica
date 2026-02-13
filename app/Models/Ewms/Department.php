<?php
// app/Models/Ewms/Department.php
namespace App\Models\Ewms;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $connection = 'ewms';
    protected $table = 'table_department';
    protected $primaryKey = 'department_id';
    public $timestamps = false;
    
    public function faculty()
    {
        return $this->hasMany(FacultyProfile::class, 'department', 'department_id');
    }
    
    public function college()
    {
        return $this->belongsTo(CollegeUnit::class, 'college_id', 'c_u_id');
    }
}