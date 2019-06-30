<?php

namespace MadisonSolutions\LCF;

use Illuminate\Database\Eloquent\Builder;

trait UsesSimpleQueryToFindModels
{
    abstract public function lcfGetSearchFields(?string $context = null) : array;

    abstract public function lcfGetLabel(?string $context = null) : string;

    public function lcfApplyCriteria(Builder $query, ?string $context = null)
    {
        // Override to set criteria on the search results
    }

    public function lcfGetSuggestions(string $search, int $page = 1, ?string $context = null) : SearchResultSet
    {
        $num_per_page = 20;
        $query = $this->query();
        $this->lcfApplyCriteria($query, $context);
        $ilike = LCF::iLikeOperator($query->getConnection());
        $query->where(function ($q) use ($ilike, $search, $context) {
            $search_esc = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $search) . '%';
            foreach (array_values($this->lcfGetSearchFields($context)) as $i => $search_field) {
                if ($i == 0) {
                    $q->where($search_field, $ilike, $search_esc);
                } else {
                    $q->orWhere($search_field, $ilike, $search_esc);
                }
            }
        });
        $has_more = ($query->count() > ($num_per_page * $page));
        $results = new SearchResultSet($has_more);
        $models = $query->limit($num_per_page)->offset($num_per_page * ($page - 1))->get();
        foreach ($models as $model) {
            $results->addResult(new SearchResult($model->getKey(), $model->lcfGetLabel($context)));
        }
        return $results;
    }

    public function lcfLookup($id, ?string $context = null) : ?SearchResult
    {
        $query = $this->query();
        $this->lcfapplyCriteria($query, $context);
        $query->where($this->getKeyName(), $id);
        $model = $query->first();
        return $model ? new SearchResult($model->getKey(), $model->lcfGetLabel($context)) : null;
    }
}
