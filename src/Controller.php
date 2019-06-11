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
            'search_type' => 'required|string',
            'search_settings' => 'required|json',
            'search' => 'required|string|min:2',
        ]);
        $type = explode(':', $request->search_type)[0];
        $settings = json_decode($request->search_settings);

        if ($type == 'model') {
            $mf = app(LCF::class)->makeModelFinder($settings->model_class, $settings->criteria, $settings->search_fields, $settings->label_attribute);
            return response()->json($mf->getSuggestions($request->search));
        }
        if ($type === 'link') {
            $lf = app(LCF::class)->getLinkFinder();
            return response()->json($lf->getSuggestions($request->search));
        }
        throw new \Exception("Unexpected type {$type}");
    }

    public function lookup(Request $request)
    {
        $this->authorize('lookup', LCF::class);
        $request->validate([
            'search_type' => 'required|string',
            'search_settings' => 'required|json',
            'id' => 'required',
        ]);
        $type = explode(':', $request->search_type)[0];
        $settings = json_decode($request->search_settings);

        if ($type == 'model') {
            $mf = app(LCF::class)->makeModelFinder($settings->model_class, $settings->criteria, $settings->search_fields, $settings->label_attribute);
            return response()->json($mf->lookup($request->id));
        }
        if ($type === 'link') {
            $lf = app(LCF::class)->getLinkFinder();
            return response()->json($lf->lookup($request->id));
        }
        throw new \Exception("Unexpected type {$type}");
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
