<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    protected $fillable = [
        'user_id', 'src', 'file_path', 'file_type', 'file_size'
    ];

    public function User()
    {
        return $this->belongsTo(User::class);
    }
}
