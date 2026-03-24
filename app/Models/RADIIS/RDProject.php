<?php

namespace App\Models\Radiis;

use Illuminate\Database\Eloquent\Model;

class RDProject extends Model
{
    protected $connection = 'analytica';
    protected $table = 'rdprojects';
}
