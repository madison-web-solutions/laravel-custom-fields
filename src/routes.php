<?php
namespace MadisonSolutions\LCF;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Route;

Route::get('lcf/suggestions', function (Request $request, LCF $lcf) {
    // @todo authorization
    $request->validate([
        'search' => 'required|string|min:2',
    ]);
    $field = $lcf->getField($request->path);
    if (!$field || !method_exists($field, 'getSuggestions')) {
        throw ValidationException::withMessages(['path' => ["Field not found"]]);
    }
    return response()->json($field->getSuggestions($request->search));
});

Route::get('lcf/display-name', function (Request $request, LCF $lcf) {
    $request->validate([
        'id' => 'required',
    ]);
    $field = $lcf->getField($request->path);
    if (!$field || !method_exists($field, 'getDisplayName')) {
        throw ValidationException::withMessages(['path' => ["Field not found"]]);
    }
    return response()->json($field->getDisplayName($request->id));
});
