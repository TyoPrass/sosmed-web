<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

#[Fillable(['post_id', 'user_id', 'text'])]
class Comment extends Model
{
    use HasFactory;

    /**
     * Relasi ke User pembuat komentar
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Post yang dikomentari
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
