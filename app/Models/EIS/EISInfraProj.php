<?php

namespace App\Models\Eis;

use Illuminate\Database\Eloquent\Model;

class EISInfraProj extends Model
{
    protected $connection = 'analytica';
    protected $table = 'eisinfraproj';
}
