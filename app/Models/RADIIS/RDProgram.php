<?php

namespace App\Models\Radiis;

use Illuminate\Database\Eloquent\Model;

class RDProgram extends Model
{
    protected $connection = 'analytica';
    protected $table = 'rdprograms';
}
