<?php

// app/Models/FundReportLine.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FundReportLine extends Model
{
    protected $fillable = [
        'fund_report_id','campus','function','source','category','amount'
    ];
}
