<?php

namespace MadisonSolutions\LCF;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests;

    public function getModelSuggestions(Request $request)
    {
        $request->validate([
            'model_class' => ['required', new FindableModelRule()],
            'finder_context' => 'nullable|string',
            'search' => 'required|string|min:2',
            'page' => 'nullable|integer|min:1',
        ]);
        $model_class = $request->model_class;
        $this->authorize('lookupModels', [LCF::class, $model_class, $request->finder_context]);
        $instance = new $model_class();
        $suggestions = $instance->lcfGetSuggestions($request->search, $request->page ?? 1, $request->finder_context);
        return response()->json($suggestions);
    }

    public function getLinkSuggestions(Request $request)
    {
        $this->authorize('lookupLinks', [LCF::class]);
        $request->validate([
            'search' => 'required|string|min:2',
            'page' => 'nullable|integer|min:1',
        ]);
        $finder = app(LCF::class)->getLinkFinder();
        return response()->json($finder->getSuggestions($request->search, $request->page ?? 1));
    }

    public function lookupModel(Request $request)
    {
        $request->validate([
            'model_class' => ['required', new FindableModelRule()],
            'finder_context' => 'nullable|string',
            'id' => 'required',
        ]);
        $model_class = $request->model_class;
        $this->authorize('lookupModels', [LCF::class, $model_class, $request->finder_context]);
        $instance = new $model_class();
        $result = $instance->lcfLookup($request->id, $request->finder_context);
        return response()->json($result);
    }

    public function lookupLink(Request $request)
    {
        $this->authorize('lookupLinks', [LCF::class]);
        $request->validate([
            'id' => 'required',
        ]);
        $finder = app(LCF::class)->getLinkFinder();
        return response()->json($finder->lookup($request->id));
    }

    public function markdown(Request $request)
    {
        $this->authorize('markdown', [LCF::class]);
        $request->validate([
            'input' => 'nullable|string',
        ]);
        return app(LCF::class)->getMarkdown()->text($request->input ?? '');
    }
}
