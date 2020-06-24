<?php

namespace App\Traits;

use App\Observers\ElasticSearchObserver;

trait SearchableTrait
{
    public static function boot()
    {
        // This makes it easy to toggle the search feature flag
        // on and off. This is going to prove useful later on
        // when deploy the new search engine to a live app.
        //if (config('services.search.enabled')) {
        static::observe(ElasticSearchObserver::class);
        //}
    }

    public function getSearchIndex()
    {
        return $this->getSearchType() ?? 'unknown';
    }

    public function getSearchType()
    {
        if (property_exists($this, 'useSearchType')) {
            return $this->useSearchType;
        }

        return $this->getTable();
    }

    public function toSearchArray()
    {
        $model = [];
        foreach ($this->toArray() as $key => $value) {
            if (key_exists($key, array_flip($this->searchableFields))) {
                $model[$key] = $value;
            }
        }

        $model['link'] = $this->searchLink();
        $model['display_value'] = $this->displayValue();

        // By having a custom method that transforms the model
        // to a searchable array allows us to customize the
        // data that's going to be searchable per model.
        return $model;
    }

    public function searchLink()
    {
        return '/' .$this->getSearchType() . '/' . $this->external_id;
    }
}
