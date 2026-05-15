<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkExperience extends Model
{
    protected $connection = 'mysql_recruitment';

    protected $fillable = [
        'user_id',
        'company_name',
        'position',
        'salary',
        'supervisor_name',
        'supervisor_phone',
        'is_contactable',
        'start_date',
        'end_date',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
