<?php

namespace App\Models\Radiis;

use Illuminate\Database\Eloquent\Model;

class RDPublication extends Model
{
    protected $connection = 'analytica';
    protected $table = 'rdpublications';
}
