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
        $request->validate([
            'search' => 'nullable|string',
            'category' => 'nullable|string',
            'folder_id' => 'nullable|integer',
        ]);
        $query = MediaItem::query()->orderBy('updated_at', 'desc');
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
        $folder_id = $request->input('folder_id');
        if ($folder_id) {
            $query->where('folder_id', $folder_id);
        }
        $items = $query->paginate(50);
        return MediaItemResource::collection($items);
    }

    public function folders(Request $request)
    {
        $this->authorize('index', MediaItem::class);
        $folder_data = [];
        $recurse = function ($folder) use (&$folder_data, &$recurse) {
            $path = [$folder->name];
            $curr = $folder;
            while ($curr = $curr->parent) {
                $path[] = $curr->name;
            }
            $folder_data[] = [
                'id' => $folder->id,
                'name' => $folder->name,
                'path' => array_reverse($path),
            ];
            foreach ($folder->children as $child) {
                $recurse($child);
            }
        };
        foreach (MediaFolder::tree() as $folder) {
            $recurse($folder);
        }
        return [
            'folders' => $folder_data,
        ];
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
            'title' => substr($basename, 0, 128),
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
            'title' => 'required|string|max:128',
            'alt' => 'nullable|string|max:256',
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
