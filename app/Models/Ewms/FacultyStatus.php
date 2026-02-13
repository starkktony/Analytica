<?php
// app/Models/Ewms/FacultyStatus.php
namespace App\Models\Ewms;

use Illuminate\Database\Eloquent\Model;

class FacultyStatus extends Model
{
    protected $connection = 'ewms';
    protected $table = 'table_faculty_status';
    protected $primaryKey = 'id';
    public $timestamps = false;
    
    protected $casts = [
        'category_of_faculty' => 'integer',
    ];
    
    public function faculty()
    {
        return $this->belongsTo(FacultyProfile::class, 'f_id', 'id');
    }
}