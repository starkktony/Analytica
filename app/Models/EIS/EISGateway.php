<?php

namespace App\Models\Eis;

use Illuminate\Database\Eloquent\Model;

class EISGateway extends Model
{
    protected $connection = 'analytica';
    protected $table = 'eisgateway';
}
