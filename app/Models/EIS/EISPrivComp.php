<?php

namespace App\Models\Eis;

use Illuminate\Database\Eloquent\Model;

class EISPrivComp extends Model
{
    protected $connection = 'analytica';
    protected $table = 'eisprivcomp';
}
