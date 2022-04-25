<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'slug', 'description'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at', 'pivot'];

    /**
     * Relationship between tags and links
     *
     * @return BelongsToMany
     */
    public function link(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Link');
    }
}
