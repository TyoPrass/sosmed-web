<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

#[Fillable(['user_id', 'caption', 'image_path', 'likes_count', 'comments_count'])]
class Post extends Model
{
    use HasFactory;

    /**
     * Relasi ke pembuat post (User)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke komentar
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
