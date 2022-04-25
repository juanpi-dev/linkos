<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;

class Image extends Model
{
    use SoftDeletes;

    /* Fillable */
    protected $fillable = [
        'title', 'original_path', 'path', 'user_id', 'size', 'file'
    ];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function getUrlAttribute()
    {
        return Storage::disk('s3')->url($this->path);
    }

    public function getUploadedTimeAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getSizeInKbAttribute()
    {
        return round($this->size / 1024, 2);
    }

    public static function boot()
    {
        parent::boot();
        static::creating(function ($image) {
//            $image->user_id = auth()->user()->id ?? $image->link->user_id;
        });
    }

    /**
     * Relationship between images and users
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship between images and links
     *
     * @return BelongsTo
     */
    public function link(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
