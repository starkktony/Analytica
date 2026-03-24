<?php

namespace App\Models\Radiis;

use Illuminate\Database\Eloquent\Model;

class RDPresentation extends Model
{
    protected $connection = 'analytica';
    protected $table = 'rdpresentations';
}
