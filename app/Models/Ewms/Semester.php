<?php
// app/Models/Ewms/Semester.php

namespace App\Models\Ewms;

use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    // Connect to eWMS database
    protected $connection = 'ewms';
    
    protected $table = 'table_semester';
    protected $primaryKey = 'sem_id';
    public $timestamps = false;
    
    protected $casts = [
        'status' => 'integer',
    ];
}