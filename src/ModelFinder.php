<?php

namespace MadisonSolutions\LCF;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ModelFinder
{
    protected $model_class;
    protected $criteria;
    protected $search_fields;
    protected $label_attribute;

    public function __construct(string $model_class, ?array $criteria, array $search_fields, string $label_attribute)
    {
        $this->model_class = $model_class;
        $this->criteria = $criteria;
        $this->search_fields = $search_fields;
        $this->label_attribute = $label_attribute;
    }

    public function __get($key)
    {
        switch ($key) {
            case 'model_class':
            case 'criteria':
            case 'search_fields':
            case 'label_attribute':
                return $this->$key;
        }
    }

    public function __isset($key)
    {
        switch ($key) {
            case 'model_class':
            case 'criteria':
            case 'search_fields':
            case 'label_attribute':
                return true;
        }
        return false;
    }

    protected function newInstance() : Model
    {
        return new $this->model_class;
    }

    protected function getQuery() : Builder
    {
        return $this->newInstance()->query();
    }

    protected function applyCriteria(Builder $query)
    {
        if ($this->criteria) {
            $query->where($this->criteria);
        }
    }

    protected function applySearch(Builder $query, string $search)
    {
        $ilike = LCF::iLikeOperator($query->getConnection());

        $query->where(function ($q) use ($ilike, $search) {
            $search_esc = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $search) . '%';
            foreach (array_values($this->search_fields) as $i => $search_field) {
                if ($i == 0) {
                    $q->where($search_field, $ilike, $search_esc);
                } else {
                    $q->orWhere($search_field, $ilike, $search_esc);
                }
            }
        });
    }

    protected function applyId(Builder $query, $id)
    {
        $query->where($this->newInstance()->getKeyName(), $id);
    }

    protected function getResult(Model $model) : SearchResult
    {
        return new SearchResult($model->getKey(), $model->getAttribute($this->label_attribute));
    }

    public function getSuggestions(string $search, int $page = 1) : SearchResultSet
    {
        $num_per_page = 20;
        $query = $this->getQuery();
        $this->applyCriteria($query);
        $this->applySearch($query, $search);
        $has_more = ($query->count() > ($num_per_page * $page));
        $results = new SearchResultSet($has_more);
        $models = $query->limit($num_per_page)->offset($num_per_page * ($page - 1))->get();
        foreach ($models as $model) {
            $results->addResult($this->getResult($model));
        }
        return $results;
    }

    public function lookup($id) : ?SearchResult
    {
        $query = $this->getQuery();
        $this->applyCriteria($query);
        $this->applyId($query, $id);
        $model = $query->first();
        return $model ? $this->getResult($model) : null;
    }
}
