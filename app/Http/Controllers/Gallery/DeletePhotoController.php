<?php

namespace App\Http\Controllers\Gallery;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gallery\DeletePhotoRequest;
use App\Models\Photo;
use App\Services\PhotoStorageService;
use Illuminate\Http\Response;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response as ScribeResponse;

#[Group('Gallery', 'APIs for Gallery')]
class DeletePhotoController extends Controller
{
    #[Endpoint('Delete Photo', 'Delete a photo from the gallery.')]
    #[ScribeResponse(content: '', status: 204)]
    public function __invoke(DeletePhotoRequest $request, Photo $photo, PhotoStorageService $storage): Response
    {
        $storage->delete($photo);
        $photo->delete();

        return response()->noContent();
    }
}
