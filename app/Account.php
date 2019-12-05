<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $guarded = [];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $hidden = [
        'user_id', 'id'
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function transactions()
    {
        return $this->hasMany('App\Transaction');
    }
}
