<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [

    ];

    protected $attributes = [
        'link_id' => 0,
        'referer' => '',
        'ip' => '',
        'server_info' => '',
        'request_info' => '',
        'stylized_url' => '',
    ];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function processAndSave(Link $link): bool
    {
        $server = request()->server->all();
        unset($server['HTTP_AUTHORIZATION']);
        unset($server['REDIRECT_HTTP_AUTHORIZATION']);

        $headers = request()->headers->all();
        unset($headers['authorization']);

        $this->link_id = $link->id;
        $this->referer = request()->headers->get('referer');
        $this->ip = request()->ip();
        $this->server_info = json_encode($server);
        $this->request_info = json_encode($headers);
        $this->stylized_url = request()->fullUrl();

        return $this->save();
    }

    /**
     * Relationship between hits and links
     *
     * @return BelongsTo
     */
    public function link(): BelongsTo
    {
        return $this->belongsTo('App\Models\Link');
    }

}
