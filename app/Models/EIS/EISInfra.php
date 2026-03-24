<?php

namespace App\Models\Eis;

use Illuminate\Database\Eloquent\Model;

class EISInfra extends Model
{
    protected $connection = 'analytica';
    protected $table = 'eisinfras';
}
