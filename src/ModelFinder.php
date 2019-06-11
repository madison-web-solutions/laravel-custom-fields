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

    protected function getResult(Model $model)
    {
        return [
            'id' => $model->getKey(),
            'display_name' => $model->getAttribute($this->label_attribute),
        ];
    }

    public function getSuggestions(string $search)
    {
        $query = $this->getQuery();
        $this->applyCriteria($query);
        $this->applySearch($query, $search);
        $suggestions = [];
        foreach ($query->take(10)->get() as $model) {
            $suggestions[] = $this->getResult($model);
        }
        return $suggestions;
    }

    public function lookup($id)
    {
        $query = $this->getQuery();
        $this->applyCriteria($query);
        $this->applyId($query, $id);
        $model = $query->first();
        return $model ? $this->getResult($model) : null;
    }
}
