<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\hasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Link extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'url',
        'title',
        'short_description',
    ];

    protected $attributes = [
        'name' => '',
        'url' => '',
        'title' => '',
        'short_description' => '',
        'user_id' => 0,
        'type_id' => 0,
        'telegram_user_id' => 0,
    ];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function processLink()
    {
        switch ($this->type->name) {
            case 'redirect':
                return redirect($this->url);
                break;
            default:
                return response([
                    'message' => 'Link type not valid'
                ], 400);
        }
    }

    /**
     * @param $data
     * @return void
     */
    public function saveLink($data)
    {
        $this->url = $data['url'];
        $this->name = $data['name'];
        $this->user_id = $data['user_id'];
        $this->type_id = $data['type_id'];
        $this->telegram_user_id = $data['telegram_user_id'];
        $this->save();

        foreach ($data['tags'] as $tag_name) {
            $tag = Tag::updateOrCreate([
                'name' => trim($tag_name),
                'slug' => Str::slug($tag_name)
            ]);

            $this->tags()->attach($tag);
        }

        $this->shortened_url = request()->getSchemeAndHttpHost() . '/' . $this->name;
        $this->with('tags');
    }

    /**
     * @return bool
     */
    public function checkUserOwnership(): bool
    {
        return $this->user_id == Auth::id();
    }

    public function getFullAttributes(): Link
    {
        $this->shortened_url = request()->getSchemeAndHttpHost() . '/' . $this->name;

        $this->image_url = '';
        if (isset($this->image)) {
            $this->image_url = $this->image->getUrlAttribute();
        }
        $this->tags;

        return $this;
    }

    /**
     * Relationship between links and users
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Relationship between links and hits
     *
     * @return hasMany
     */
    public function hits(): hasMany
    {
        return $this->hasMany('App\Models\Hit');
    }

    /**
     * Relationship between links and type
     *
     * @return BelongsTo
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo('App\Models\LinkType');
    }

    /**
     * Relationship between links and type
     *
     * @return BelongsTo
     */
    public function image(): BelongsTo
    {
        return $this->belongsTo('App\Models\Image');
    }
    /**
     * Relationship between hits and links
     *
     * @return Link
     */
    public static function findByName(string $name): Link
    {
        // Case-sensitive WHERE
        $link = Link::where('name', $name)->get();
        if(sizeof($link)) return $link[0];
        return false;
    }

    /**
     * Relationship between links and tags
     *
     * @return BelongsToMany
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Tag');
    }

}
