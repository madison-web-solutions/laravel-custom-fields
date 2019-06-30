<?php

namespace MadisonSolutions\LCF;

use Laravel\Scout\Builder;

trait UsesScoutToFindModels
{
    abstract public function lcfGetLabel(?string $context = null) : string;

    public function lcfFilterScoutQuery(Builder $query, ?string $context = null)
    {
        // Override to set search index / where clauses etc
    }

    public function lcfGetSuggestions(string $search, int $page = 1, ?string $context = null) : SearchResultSet
    {
        $num_per_page = 20;
        $class = get_class($this);
        $query = $class::search($search);
        $this->lcfFilterScoutQuery($query, $context);
        $paginator = $query->paginate($num_per_page, '', $page);
        $results = new SearchResultSet($paginator->hasMorePages());
        foreach ($paginator->items() as $model) {
            $results->addResult(new SearchResult($model->getKey(), $model->lcfGetLabel($context)));
        }
        return $results;
    }

    public function lcfLookup($id, ?string $context = null) : ?SearchResult
    {
        $model = $this->query()->where($this->getKeyName(), $id)->first();
        return $model ? new SearchResult($model->getKey(), $model->lcfGetLabel($context)) : null;
    }
}
