<?php

namespace MadisonSolutions\LCF\Media;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use MadisonSolutions\LCF\LCF;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function index(Request $request)
    {
        $this->authorize('index', MediaItem::class);
        $query = MediaItem::query();
        $ilike = LCF::iLikeOperator($query->getConnection());

        $search = $request->input('search');
        if ($search) {
            $words = preg_split('/\s+/', $search);
            foreach ($words as $word) {
                $query->where(function ($q) use ($ilike, $word) {
                    $wordLike = '%' . str_replace('%', '\\%', $word) . '%';
                    $q->where('title', $ilike, $wordLike)->orWhere('alt', $ilike, $wordLike)->orWhere('extension', $word);
                });
            }
        }
        $category = $request->input('category');
        if ($category) {
            $query->whereIn('extension', MediaType::allExtensionsForCategory($category));
        }
        $items = $query->paginate(50);
        return MediaItemResource::collection($items);
    }

    public function get(Request $request, $id)
    {
        $item = MediaItem::findOrFail($id);
        $this->authorize('get', $item);
        return [
            'ok' => true,
            'item' => new MediaItemResource($item),
        ];
    }

    public function upload(Request $request)
    {
        $this->authorize('upload', MediaItem::class);
        if (!$request->hasFile('file')) {
            return ['ok' => false, 'error' => 'No file attached'];
        }
        $file = $request->file('file');
        if (!$file->isValid()) {
            return ['ok' => false, 'error' => 'Upload not valid'];
        }
        $extension = $file->getClientOriginalExtension();
        $mediaType = new MediaType($extension);
        if ($mediaType->category == 'Unknown') {
            return ['ok' => false, 'error' => 'Unrecognised format'];
        }
        $category = $request->input('category', null);
        if ($category && strtolower($mediaType->category) !== $category) {
            return ['ok' => false, 'error' => "Must upload a file of type: {$category}, received type {$mediaType->category}"];
        }
        $suffixLen = strlen($extension) + 1;
        $basename = substr($file->getClientOriginalName(), 0, -$suffixLen);
        $item = new MediaItem([
            'title' => $basename,
            'extension' => $mediaType->extension,
            'alt' => '',
        ]);
        $item->setUniqueSlug($basename);
        $item->getStorageItem()->setFileFromUpload($file);
        $item->save();
        return [
            'ok' => true,
            'item' => new MediaItemResource($item),
        ];
    }

    public function update(Request $request, $id)
    {
        $item = MediaItem::findOrFail($id);
        $this->authorize('update', $item);
        $request->validate([
            'title' => 'required|string|max:255',
            'alt' => 'nullable|string|max:255',
        ]);

        $item->title = $request->title;
        $item->alt = $request->alt ?? '';
        $item->save();
        return [
            'ok' => true,
            'item' => new MediaItemResource($item),
        ];
    }

    // @todo replace file

    public function delete(Request $request, $id)
    {
        $item = MediaItem::findOrFail($id);
        $this->authorize('delete', $item);
        $item->delete();
        return [
            'ok' => true,
        ];
    }
}
