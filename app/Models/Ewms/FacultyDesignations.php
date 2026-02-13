<?php
// app/Models/Ewms/FacultyDesignations.php

namespace App\Models\Ewms;

use Illuminate\Database\Eloquent\Model;

class FacultyDesignations extends Model
{
    protected $connection = 'ewms';
    protected $table = 'table_faculty_designations';
    protected $primaryKey = 'id';
    public $timestamps = false;
    
    protected $casts = [
        'f_id' => 'string', // Note: This is string in your schema
        'sem_id' => 'integer',
        'etl' => 'string', // String in your schema, we'll convert
    ];
    
    protected $fillable = [
        'id',
        'f_id',
        'sem_id',
        'type',
        'designation',
        'title',
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
    
    // Convert ETL string to float
    public function getEtlFloatAttribute()
    {
        if (is_numeric($this->etl)) {
            return (float) $this->etl;
        }
        return 0.0;
    }
}