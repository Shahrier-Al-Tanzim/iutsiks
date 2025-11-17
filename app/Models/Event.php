<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    //
    protected $fillable = [
        'title',
        'description',
        'event_date',
        'event_time',
        'author_id'
    ];

    public function author(){
        return $this-> belongsTo(User::class, 'author_id');
    }
}

