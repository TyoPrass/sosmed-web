<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

#[Fillable(['follower_id', 'following_id'])]
class Follow extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    /**
     * Relasi ke User yang mengikuti (follower)
     */
    public function follower()
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    /**
     * Relasi ke User yang diikuti (following)
     */
    public function following()
    {
        return $this->belongsTo(User::class, 'following_id');
    }
}
