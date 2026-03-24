<?php

namespace App\Models\Eis;

use Illuminate\Database\Eloquent\Model;

class EISGovt extends Model
{
    protected $connection = 'analytica';
    protected $table = 'eisgovt';
}
