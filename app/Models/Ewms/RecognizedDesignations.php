<?php
// app/Models/Ewms/RecognizedDesignations.php

namespace App\Models\Ewms;

use Illuminate\Database\Eloquent\Model;

class RecognizedDesignations extends Model
{
    protected $connection = 'ewms';
    protected $table = 'table_recognized_designations';
    protected $primaryKey = 'rd_id';
    public $timestamps = false;
    
    protected $fillable = [
        'rd_id',
        'type',
        'designation',
    ];
    
    public function facultyDesignations()
    {
        return $this->hasMany(FacultyDesignations::class, 'designation', 'designation');
    }
}