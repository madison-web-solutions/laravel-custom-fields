<?php
namespace MadisonSolutions\LCF\Fields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MadisonSolutions\Coerce\Coerce;
use MadisonSolutions\LCF\FindableInterface;
use MadisonSolutions\LCF\FindableModelRule;
use MadisonSolutions\LCF\ScalarField;
use MadisonSolutions\LCF\LCF;

class ModelIdField extends ScalarField
{
    public function __construct(array $options)
    {
        parent::__construct($options);
        $this->options['string_keys'] = ($this->newInstance()->getKeyType() == 'string');
    }

    public function optionDefaults() : array
    {
        $defaults = parent::optionDefaults();
        $defaults['finder_context'] = null;
        return $defaults;
    }

    public function optionRules() : array
    {
        $rules = parent::optionRules();
        $rules['model_class'] = ['required', new FindableModelRule()];
        $rules['finder_context'] = 'nullable|string';
        $rules['load_with'] = 'nullable|array';
        return $rules;
    }

    public function inputComponent() : string
    {
        return 'lcf-search-input';
    }

    public function jsonSerialize()
    {
        $data = parent::jsonSerialize();
        $data['settings']['searchType'] = 'model';
        $data['settings']['searchSettings'] = [
            'model_class' => Arr::pull($data['settings'], 'modelClass'),
            'finder_context' => Arr::pull($data['settings'], 'finderContext'),
        ];
        return $data;
    }

    public function newInstance() : Model
    {
        return new $this->model_class;
    }

    public function validateNotNull(string $path, $value, &$messages, array $data)
    {
        if (! ($this->string_keys ? is_string($value) : is_int($value))) {
            $messages[$path][] = $this->trans('invalid');
            return;
        }
        if (! $this->newInstance()->lcfLookup($value, $this->finder_context)) {
            $model_name = class_basename($this->model_class);
            $messages[$path][] = $this->trans('missing', ['id' => $value, 'model' => $model_name]);
        }
    }

    protected function coerceNotNull($input, &$output, bool $keep_invalid = false) : bool
    {
        if ($input instanceof $this->model_class) {
            $output = $input->getKey();
            return true;
        }
        if ($this->string_keys) {
            if (is_string($input) || is_int($input)) {
                $output = (string) $input;
                return true;
            }
        } else {
            if (Coerce::toInt($input, $output, Coerce::REJECT_BOOL)) {
                return true;
            }
        }
        $output = ($keep_invalid ? $input : null);
        return false;
    }

    protected $models_to_load = [];
    protected $models_loaded = [];

    public function queueModelToLoad($id)
    {
        if (isset($this->models_loaded[$id])) {
            return;
        }
        $this->models_to_load[$id] = true;
    }

    public function fetchQueuedModels()
    {
        if (empty($this->models_to_load)) {
            return;
        }
        $query = $this->newInstance()->query();
        if ($this->load_with) {
            $query->with($this->load_with);
        }
        $ids = array_keys($this->models_to_load);
        foreach ($query->findMany($ids) as $model) {
            $this->models_loaded[$model->getKey()] = $model;
        }
        $this->models_to_load = [];
    }

    protected function expandPrepareNotNull($cast_value)
    {
        $this->queueModelToLoad($cast_value);
    }

    protected function doExpandNotNull($cast_value)
    {
        $this->fetchQueuedModels();
        return $this->models_loaded[$cast_value] ?? null;
    }

    protected function expandKey(string $key)
    {
        return preg_replace('/_id$/', '', $key);
    }
}
