<?php

namespace App\Models\Radiis;

use Illuminate\Database\Eloquent\Model;

class RDAward extends Model
{
    protected $connection = 'analytica';
    protected $table = 'rdawards';
}
