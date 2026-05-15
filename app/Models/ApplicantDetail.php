<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicantDetail extends Model
{
    protected $connection = 'mysql_recruitment';

    protected $fillable = [
        'user_id',
        'is_experienced',
        'status',
        'nickname',
        'phone',
        'address',
        'birth_place',
        'birth_date',
        'gender',
        'ktp_image',
        'selfie_image',
        'nik',
        'religion',
        'marital_status',
        'children_count',
        'education_level',
        'education_major',
        'father_name',
        'mother_name',
        'home_location',
        'emergency_phone',
        'emergency_name',
        'driver_license',
    ];

    protected $appends = ['ktp_image_url', 'selfie_image_url'];

    public function getKtpImageUrlAttribute()
    {
        return $this->ktp_image ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->ktp_image) : null;
    }

    public function getSelfieImageUrlAttribute()
    {
        return $this->selfie_image ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->selfie_image) : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
