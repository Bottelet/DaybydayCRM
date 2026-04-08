<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class SearchController extends Controller
{
    public function search($query, $type)
    {
        // Allowlist of searchable model types to prevent arbitrary class instantiation
        $allowedTypes = [
            'client' => Client::class,
            'clients' => Client::class,
            'task' => Task::class,
            'tasks' => Task::class,
            'project' => Project::class,
            'projects' => Project::class,
            'lead' => Lead::class,
            'leads' => Lead::class,
            'user' => User::class,
            'users' => User::class,
        ];

        // Normalize and validate type before selecting backend
        $typeLower = strtolower($type);
        if (! isset($allowedTypes[$typeLower])) {
            return response()->json(['error' => 'Invalid search type'], 400);
        }

        // Use the validated class from allowlist to prevent arbitrary class instantiation
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
