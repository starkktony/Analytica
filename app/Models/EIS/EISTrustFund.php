<?php

namespace App\Models\Eis;

use Illuminate\Database\Eloquent\Model;

class EISTrustFund extends Model
{
    protected $connection = 'analytica';
    protected $table = 'eistrustfunds';
}
