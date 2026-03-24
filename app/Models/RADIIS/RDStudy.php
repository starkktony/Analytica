<?php

namespace App\Models\Radiis;

use Illuminate\Database\Eloquent\Model;

class RDStudy extends Model
{
    protected $connection = 'analytica';
    protected $table = 'rdstudies';
}
