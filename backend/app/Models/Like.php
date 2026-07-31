<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

#[Fillable(['post_id', 'user_id'])]
class Like extends Model
{
    use HasFactory;

    /**
     * Menonaktifkan updated_at karena di struktur hanya meminta created_at
     */
    public const UPDATED_AT = null;

    /**
     * Relasi ke User yang melakukan like
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Post yang di-like
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
