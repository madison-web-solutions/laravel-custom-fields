<?php

namespace MadisonSolutions\LCF;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function getSuggestions(Request $request)
    {
        $this->authorize('getSuggestions', LCF::class);
        $request->validate([
            'type' => 'required|in:model,link',
            'options' => 'required|json',
            'search' => 'required|string|min:2',
        ]);
        if ($request->type == 'model') {
            $field = new Fields\ModelField(json_decode($request->options, true));
            return response()->json($field->getSuggestions($request->search));
        }
        if ($request->type === 'link') {
            $lf = app(LCF::class)->getLinkFinder();
            return response()->json($lf->getSuggestions($request->search));
        }
        throw new \Exception("Unexpected type {$request->type}");
    }

    public function getDisplayName(Request $request)
    {
        $this->authorize('getDisplayName', LCF::class);
        $request->validate([
            'type' => 'required|in:model,link',
            'options' => 'required|json',
            'id' => 'required',
        ]);
        if ($request->type == 'model') {
            $field = new Fields\ModelField(json_decode($request->options, true));
            return response()->json($field->getDisplayName($request->id));
        }
        if ($request->type === 'link') {
            $lf = app(LCF::class)->getLinkFinder();
            return response()->json($lf->getDisplayName($request->id));
        }
        throw new \Exception("Unexpected type {$request->type}");
    }

    public function linkLookup(Request $request)
    {
        $this->authorize('linkLookup', LCF::class);
        $request->validate([
            'spec' => 'required|string',
        ]);
        $lf = app(LCF::class)->getLinkFinder();
        return response()->json($lf->lookup($request->spec));
    }

    public function markdown(Request $request)
    {
        $this->authorize('markdown', LCF::class);
        $request->validate([
            'input' => 'nullable|string',
        ]);
        return app(LCF::class)->getMarkdown()->text($request->input ?? '');
    }
}
