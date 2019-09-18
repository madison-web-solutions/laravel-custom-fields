<?php
namespace MadisonSolutions\LCF;

use MadisonSolutions\LCF\Media\MediaItem;
use MadisonSolutions\LCF\Media\MediaItemResource;
use MadisonSolutions\LCF\Media\MediaType;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Route;

Route::get('lcf/suggestions', function (Request $request, LCF $lcf) {
    // @todo authorization
    $request->validate([
        'type' => 'required|in:model,link',
        'options' => 'required|json',
        'search' => 'required|string|min:2',
    ]);
    if ($request->type == 'model') {
        $field = new ModelField(json_decode($request->options, true));
        return response()->json($field->getSuggestions($request->search));
    }
    if ($request->type === 'link') {
        $lf = $lcf->getLinkFinder();
        return response()->json($lf->getSuggestions($request->search));
    }
    throw new \Exception("Unexpected type {$request->type}");
});

Route::get('lcf/display-name', function (Request $request, LCF $lcf) {
    // @todo authorization
    $request->validate([
        'type' => 'required|in:model,link',
        'options' => 'required|json',
        'id' => 'required',
    ]);
    if ($request->type == 'model') {
        $field = new ModelField(json_decode($request->options, true));
        return response()->json($field->getDisplayName($request->id));
    }
    if ($request->type === 'link') {
        $lf = $lcf->getLinkFinder();
        return response()->json($lf->getDisplayName($request->id));
    }
    throw new \Exception("Unexpected type {$request->type}");
});

Route::get('lcf/link-lookup', function (Request $request, LCF $lcf) {
    // @todo authorization
    $request->validate([
        'spec' => 'required|string',
    ]);
    $lf = $lcf->getLinkFinder();
    return response()->json($lf->lookup($request->spec));
});

Route::get('lcf/media-library', function (Request $request) {
    // @todo authorization
    $query = MediaItem::query();
    $search = $request->input('search');
    if ($search) {
        $words = preg_split('/\s+/', $search);
        foreach ($words as $word) {
            $query->where(function ($q) use ($word) {
                $wordLike = '%' . str_replace('%', '\\%', $word) . '%';
                $q->where('title', 'ILIKE', $wordLike)->orWhere('alt', 'ILIKE', $wordLike)->orWhere('extension', $word);
            });
        }
    }
    $category = $request->input('category');
    if ($category) {
        $query->whereIn('extension', MediaType::allExtensionsForCategory($category));
    }
    $items = $query->paginate(50);
    return MediaItemResource::collection($items);
});

Route::get('lcf/media-library/{id}', function (Request $request, $id) {
    // @todo authorization
    $item = MediaItem::findOrFail($id);
    return [
        'ok' => true,
        'item' => new MediaItemResource($item),
    ];
});

Route::post('lcf/media-library', function (Request $request) {
    // @todo authorization
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
    $item->save();
    $item->setFileFromUpload($file);
    return [
        'ok' => true,
        'item' => new MediaItemResource($item),
    ];
});

Route::post('lcf/media-library/update', function (Request $request) {
    // @todo authorization
    $request->validate([
        'item_id' => 'required|integer',
        'title' => 'required|string|max:255',
        'alt' => 'nullable|string|max:255',
    ]);
    $item = MediaItem::findOrFail($request->item_id);
    $item->title = $request->title;
    $item->alt = $request->alt ?? '';
    $item->save();
    return [
        'ok' => true,
        'item' => new MediaItemResource($item),
    ];
});

Route::post('lcf/media-library/delete', function (Request $request) {
    // @todo authorization
    $request->validate([
        'item_id' => 'required|integer',
    ]);
    $item = MediaItem::findOrFail($request->item_id);
    $item->delete();
    return [
        'ok' => true,
    ];
});

Route::post('lcf/markdown', function (Request $request, LCF $lcf) {
    // @todo authorization
    $request->validate([
        'input' => 'nullable|string',
    ]);
    return $lcf->getMarkdown()->text($request->input ?? '');
});
