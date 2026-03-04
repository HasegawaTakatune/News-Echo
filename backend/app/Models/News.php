<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class News extends Model
{
    protected $fillable = ['user_id', 'title', 'post_interval', 'research_prompt'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
