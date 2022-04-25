<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    private $image;

    public function __construct(Image $image)
    {
        $this->image = $image;
    }

    public function getImages()
    {
        return view('images')->with('images', auth()->user()->images);
    }

    public function imageMetadataUpload()
    {
        if(isset($this->image->path) && strlen($this->image->path)) {
            $path_date = (new \DateTime())->format('Y/m/d');
            $file = file_get_contents($this->image->path);
            $path = 'images/' . $path_date . '/' . substr(md5(microtime(true)), 0, 12) . '.jpg';
            $upload = Storage::disk('s3')->put($path, $file);
            return $upload ? $path : '';
        }

        return '';
    }

    /*public function postUpload(StoreImage $request)
    {
        $path = Storage::disk('s3')->put('images/originals', $request->file);
        $request->merge([
            'size' => $request->file->getClientSize(),
            'path' => $path,
        ]);

        $this->image->create($request->only('path', 'title', 'size'));

        return back()->with('success', 'Image Successfully Saved');
    }*/
}
