<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class SavedPost extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'saved_posts';

    protected $fillable = [
        'user_id',
        'post_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}
