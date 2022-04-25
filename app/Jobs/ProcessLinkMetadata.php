<?php

namespace App\Jobs;

use App\Models\Link;
use App\Models\Image;
use App\Http\Controllers\API\ImageController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use shweshi\OpenGraph\OpenGraph;

class ProcessLinkMetadata implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $link;

    /**
     * Create a new job instance.
     *
     * @param Link $link
     */
    public function __construct(Link $link)
    {
        $this->link = $link;
    }

    /**
     * Execute the job.
     *
     * @param OpenGraph $openGraph
     * @return void
     * @throws \shweshi\OpenGraph\Exceptions\FetchException
     */
    public function handle(OpenGraph $openGraph)
    {
        $og_metadata = $openGraph->fetch($this->link->url, true);
        $metadata = get_meta_tags($this->link->url);
        $metadata_title = '';
        $metadata_description = '';
        $metadata_image = '';

        if (isset($og_metadata['title']) && strlen($og_metadata['title'])) {
            $metadata_title = trim($og_metadata['title']);
        } else if (isset($metadata['title']) && strlen($metadata['title'])) {
            $metadata_title = trim($metadata['title']);
        }

        if (isset($og_metadata['description']) && strlen($og_metadata['description'])) {
            $metadata_description = trim($og_metadata['description']);
        } else if (isset($metadata['description']) && strlen($metadata['description'])) {
            $metadata_description = trim($metadata['description']);
        }

        if (isset($og_metadata['image']) && strlen($og_metadata['image'])) {
            $metadata_image = $og_metadata['image'];
        } else if (isset($metadata['image']) && strlen($metadata['image'])) {
            $metadata_image = $metadata['image'];
        }

        // Read of OG metadata (title, description, image URL)
        if (strlen($metadata_title) && strlen($metadata_description)) {
            $this->link->title = strlen($metadata_title) <= 252 ?
                $metadata_title : substr($metadata_title, 0, 252) . '...';

            $this->link->short_description = strlen($metadata_description) <= 252 ?
                $metadata_description : substr($metadata_description, 0, 252) . '...';

            // If image exists, save a copy at S3
            if (isset($metadata_image) && strlen($metadata_image)) {
                $image = new Image();
                $image->title = '';
                $image->path = $metadata_image;
                $image->user_id = $this->link->user_id;

                $image_upload = new ImageController($image);
                $image->path = $image_upload->imageMetadataUpload();

                if (strlen($image->path)) {
                    $image->save();
                    $this->link->image_id = $image->id;
                }
            }

            $this->link->save();
        }
    }
}


