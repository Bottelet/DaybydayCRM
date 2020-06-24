<?php
namespace App\Services\Search;

use Elasticsearch\ClientBuilder;

class SearchService
{
    private $elasticsearch;

    public function getClient()
    {
        $host = config('elasticsearch.hosts');

        if (is_null($this->elasticsearch)) {
            $builder = ClientBuilder::create()->setHosts($host);
            $this->elasticsearch = $builder->build();
        }
        return $this->elasticsearch;
    }

    public function search($query, $type = 'clients', $prPage = 5, $offset = 0, $sortBy = null, $sortDirection = 'desc')
    {
        $elasticClient = $this->getClient();
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
        if (!is_null($sortBy)) {
            $params['body']['sort'] = [$sortBy => $sortDirection];
        }

        $res = $elasticClient->search($params);

        return $res;
    }
}
