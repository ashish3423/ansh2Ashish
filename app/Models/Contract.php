<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $fillable = [
        'contract_number',
        'employee_id',
        'title',
        'start_date',
        'end_date',
        'amount',
        'status',
        'notes',
    ];
}
