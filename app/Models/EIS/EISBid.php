<?php

namespace App\Models\Eis;

use Illuminate\Database\Eloquent\Model;

class EISBid extends Model
{
    protected $connection = 'analytica';
    protected $table = 'eisbids';
}
