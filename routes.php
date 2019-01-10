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
        'type' => 'required|in:model',
        'options' => 'required|json',
        'search' => 'required|string|min:2',
    ]);
    if ($request->type == 'model') {
        $field = new ModelField(json_decode($request->options, true));
        return response()->json($field->getSuggestions($request->search));
    }
    throw new \Exception("Unexpected type {$request->type}");
});

Route::get('lcf/display-name', function (Request $request, LCF $lcf) {
    // @todo authorization
    $request->validate([
        'type' => 'required|in:model',
        'options' => 'required|json',
        'id' => 'required',
    ]);
    if ($request->type == 'model') {
        $field = new ModelField(json_decode($request->options, true));
        return response()->json($field->getDisplayName($request->id));
    }
    throw new \Exception("Unexpected type {$request->type}");
});

Route::get('lcf/media-library', function (Request $request) {
    // @todo authorization, pagination, searching
    return MediaItemResource::collection(MediaItem::all());
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
