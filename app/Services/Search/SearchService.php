<?php

namespace App\Services\Search;

use Elasticsearch\ClientBuilder;

class SearchService
{
    private $elasticsearch;

    public function getClient()
    {
        if (app()->environment('testing')) {
            return null;
        }

        $hosts = config('elasticsearch.hosts');
        $formattedHosts = [];
        foreach ($hosts as $host) {
            $scheme = $host['scheme'] ?? 'http';
            $formattedHosts[] = $scheme.'://'.(($host['user'] ?? null) ? $host['user'].':'.$host['pass'].'@' : '').$host['host'].':'.$host['port'];
        }

        if (is_null($this->elasticsearch)) {
            $builder = ClientBuilder::create()->setHosts($formattedHosts);
            $this->elasticsearch = $builder->build();
        }

        return $this->elasticsearch;
    }

    public function search($query, $type = 'clients', $prPage = 5, $offset = 0, $sortBy = null, $sortDirection = 'desc')
    {
        $elasticClient = $this->getClient();

        if (is_null($elasticClient)) {
            return ['hits' => ['total' => 0, 'hits' => []]];
        }

        $params = [
            'index' => $type,
            'type' => $type,
            'body' => [
                'size' => $prPage,
                'from' => $offset,
                'query' => [
                    'multi_match' => [
                        'fuzziness' => 'AUTO',
                        'query' => strtolower($query),

                    ],
                ],
            ],
        ];
        if (! is_null($sortBy)) {
            $params['body']['sort'] = [$sortBy => $sortDirection];
        }

        $res = $elasticClient->search($params);

        return $res;
    }
}
