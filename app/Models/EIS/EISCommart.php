<?php

namespace App\Models\Eis;

use Illuminate\Database\Eloquent\Model;

class EISCommart extends Model
{
    protected $connection = 'analytica';
    protected $table = 'eiscommart';
}
