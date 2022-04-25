<?php

namespace App\Http\Controllers\API;

use App\Models\Link;
use App\Models\Hit;
use App\Services\LinkService;
use App\Http\Controllers\Controller;
use App\Http\Resources\LinkResource;
use App\Jobs\ProcessLinkMetadata;
use Illuminate\Http\Response;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class LinkController extends Controller
{

    private $linkService;

    public function __construct(LinkService $linkService)
    {
        $this->linkService = $linkService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Response
     * @throws BindingResolutionException
     */
    public function index(Request $request): Response
    {
        $limit = $request->get('limit') ?? 12;
        $offset = $request->get('offset') ?? 0;

        $links = $this->linkService->getUserLastLinks($limit, $offset);

        return response([
            'links' => LinkResource::collection($this->linkService->prepare($links)),
            'message' => 'Retrieved successfully'
        ], 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Response
     * @throws BindingResolutionException
     */
    public function feed(Request $request): Response
    {
        $tags = $request->get('tags') ?? '';
        $limit = $request->get('limit') ?? 12;
        $offset = $request->get('offset') ?? 0;

        $links = $this->linkService->getUserFeed($limit, $offset, $tags);

        return response([
            'links' => LinkResource::collection($this->linkService->prepare($links)),
            'message' => 'Retrieved successfully'
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     * @throws BindingResolutionException
     */
    public function store(Request $request): Response
    {
        $validation = $this->linkService->validateLinkCreate($request->all());
        if ($validation->fails()) {
            return response([
                    'error' => $validation->errors(),
                    'message' => 'Validation Error']
            );
        }
        $linkToCreate = $this->linkService->generateShortURL($request);

        try {
            $link = new Link();
            $link->saveLink($linkToCreate);

            ProcessLinkMetadata::dispatch($link);

            return response([
                'link' => new LinkResource($link),
                'message' => 'Created successfully'
            ], 201);
        } catch (QueryException $e) {
            return response([
                'message' => 'error',
                'error_code' => $e->errorInfo[1] ?? 0,
                'error' => $e->errorInfo,
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Link $link
     * @return Response
     * @throws BindingResolutionException
     */
    public function show(Link $link): Response
    {
        if ($link->checkUserOwnership()) {
            return response([
                'link' => new LinkResource($link->getFullAttributes()),
                'message' => 'Retrieved successfully'
            ], 200);
        } else {
            return response([
                'message' => 'Link does not exist or does not belongs to this user'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Link $link
     * @return Response
     * @throws BindingResolutionException
     */
    public function update(Request $request, Link $link): Response
    {
        $link->update($request->all());
        $link->shortened_url = request()->getSchemeAndHttpHost() . '/' . $link->name;

        return response([
            'link' => new LinkResource($link),
            'message' => 'Update successfully'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Link $link
     * @return Response
     * @throws BindingResolutionException
     * @throws \Exception
     */
    public function destroy(Link $link): Response
    {
        $link->delete();
        return response([
            'message' => 'Deleted'
        ]);
    }

    /**
     * Determines the type of URL and handles the type of response.
     *
     * @param String $name
     * @return Response
     * @throws BindingResolutionException
     */
    public function go(string $name): Response
    {
        $link = Link::findByName($name);

        if ($link) {
            $hit = new Hit();
            $hit->processAndSave($link);

            return $link->processLink();
        } else {
            return response([
                'message' => 'Link does not exist'
            ], 404);
        }
    }

}
