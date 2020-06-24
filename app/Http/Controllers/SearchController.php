<?php

namespace App\Http\Controllers;

use App\Services\Search\SearchService;
use Illuminate\Http\Request;
use App\Models\Client;

class SearchController extends Controller
{
    public function search($query, $type)
    {
        if (config('services.elasticsearch.enabled')) {
            return response()->json(app(SearchService::class)->search($query, $type));
        }

        $type = ucfirst(rtrim($type, 's'));
        $class = '\\App\\Models\\' . $type;
        $searchClass = new $class();
        $result["hits"] = [];
        foreach ($searchClass->getSearchableFields() as $searchableField) {
            $classes = $searchClass->where($searchableField, 'LIKE', '%' . $query . '%')->get();
            foreach ($classes as $class) {
                $source = new \stdClass();
                $source->_source = new \stdClass();
                if (!$class->displayValue() || !$class->searchLink()) {
                    continue;
                }
                $source->_source->display_value = $class->displayValue();
                $source->_source->link = $class->searchLink();
                $result["hits"]["hits"][] = $source;
            }
        }
        return response()->json($result);
    }
}
