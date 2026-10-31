<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = 'employees';

    protected $fillable = [
        'employee_code',
        'first_name',
        'last_name',
        'email',
        'phone',
        'photo',
        'epf_enabled',
        'esi_enabled',
        'department',
        'designation',
        'police_verification_status',
        'id_card_status',
        'dress_status',
        'status',
    ];
}
