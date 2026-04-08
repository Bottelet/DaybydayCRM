<?php

namespace App\Http\Controllers;

use App\Services\Search\SearchService;

class SearchController extends Controller
{
    public function search($query, $type)
    {
        if (config('services.elasticsearch.enabled')) {
            return response()->json(app(SearchService::class)->search($query, $type));
        }

        // Allowlist of searchable model types to prevent arbitrary class instantiation
        $allowedTypes = [
            'client' => \App\Models\Client::class,
            'clients' => \App\Models\Client::class,
            'task' => \App\Models\Task::class,
            'tasks' => \App\Models\Task::class,
            'project' => \App\Models\Project::class,
            'projects' => \App\Models\Project::class,
            'lead' => \App\Models\Lead::class,
            'leads' => \App\Models\Lead::class,
            'user' => \App\Models\User::class,
            'users' => \App\Models\User::class,
        ];

        $typeLower = strtolower($type);
        if (!isset($allowedTypes[$typeLower])) {
            return response()->json(['error' => 'Invalid search type'], 400);
        }

        $class = $allowedTypes[$typeLower];
        $searchClass = new $class;
        $result['hits'] = [];
        foreach ($searchClass->getSearchableFields() as $searchableField) {
            $classes = $searchClass->where($searchableField, 'LIKE', '%'.$query.'%')->get();
            foreach ($classes as $class) {
                $source = new \stdClass;
                $source->_source = new \stdClass;
                if (! $class->displayValue() || ! $class->searchLink()) {
                    continue;
                }
                $source->_source->display_value = $class->displayValue();
                $source->_source->link = $class->searchLink();
                $result['hits']['hits'][] = $source;
            }
        }

        return response()->json($result);
    }
}
