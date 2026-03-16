<?php

// app/Models/FundReport.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FundReport extends Model
{
    protected $fillable = ['year','type','title'];

    public function lines(): HasMany
    {
        return $this->hasMany(FundReportLine::class);
    }
}
