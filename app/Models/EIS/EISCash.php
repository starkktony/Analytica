<?php

namespace App\Models\Eis;

use Illuminate\Database\Eloquent\Model;

class EISCash extends Model
{
    protected $connection = 'analytica';
    protected $table = 'eiscash';
}
