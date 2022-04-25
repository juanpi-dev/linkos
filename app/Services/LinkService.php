<?php

namespace App\Services;

use App\Models\LinkType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class LinkService
{
    public function prepare($links)
    {
        foreach ($links as $link) {
            $link->shortened_url = request()->getSchemeAndHttpHost() . '/' . $link->name;
            $link->type = $link->type()->get()[0]->name;
            $link->total_hits = $link->hits()->count();
            $link->tags;
            $link->image_url = '';
            if ($link->image) $link->image_url = $link->image->getUrlAttribute();
        }

        return $links;
    }

    public function generateShortURLLetters()
    {
        $len_gen_id = 6;
        $letters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $special_chars = '0123456789-';
        $gen_id = substr(str_shuffle(str_repeat($letters . $special_chars, $len_gen_id)), 0, $len_gen_id);
        $gen_id[0] = substr(str_shuffle(str_repeat($letters, 1)), 0, 1);
        $gen_id[strlen($gen_id) - 1] = substr(str_shuffle(str_repeat($letters, 1)), 0, 1);

        return $gen_id;
    }

    public function generateShortURL(Request $request)
    {
        $request_data = $request->all();
        $data_link['user_id'] = $request->user()->id;

        $type = isset($request_data['type']) && strlen($request_data['type']) ? $request_data['type'] : 'redirect';

        try {
            $data_link['type_id'] = LinkType::where('name', $type)->get()[0]->id;
        } catch (\Throwable $e) {
            return response(['error' => $e->getMessage(), 'Validation Error']);
        }

        $data_link['name'] = $request_data['name'] ?? $this->generateShortURLLetters();
        $data_link['tags'] = isset($request_data['tags']) ? explode(',', $request_data['tags']) : [];

        // Initial implementation for Telegram, in a column
        $data_link['telegram_user_id'] = $request_data['telegram_user_id'] ?? 0;

        return $data_link;
    }

    public function validateLinkCreate($request_data)
    {
        return Validator::make($request_data, [
            'url' => 'required|url',
            'name' => 'max:255',
            'title' => 'max:255',
            'type' => 'max:255',
            'short_description' => 'max:255',
            'tags' => 'max:255',
        ]);
    }

    public function getUserLastLinks($limit, $offset)
    {
        return Auth::user()->links()
            ->orderBy('links.created_at', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

    public function getUserFeed($limit, $offset, $tags)
    {
        $tags = strlen($tags) ? array_map('trim', explode(',', $tags)) : [];
        return Auth::user()->links()
            ->whereHas(
                'tags',
                function (Builder $query) use ($tags) {
                    if (sizeof($tags)) $query->whereIn('name', $tags);
                    else $query->whereIn('name', []);
                }
            )
            ->whereNotNull('image_id')->where('image_id', '<>', '')
            ->whereNotNull('title')->where('title', '<>', '')
            ->whereNotNull('short_description')->where('short_description', '<>', '')
            ->orderBy('links.created_at', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

}
