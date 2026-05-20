<?php

namespace App\Observers;

use Elastic\Elasticsearch\ClientBuilder;

class ElasticSearchObserver
{
    private $elasticsearch;

    public function __construct()
    {
        // Disable Elasticsearch integration in testing environment
        if (app()->environment('testing')) {
            $this->elasticsearch = null;

            return;
        }
        $hosts          = config('elasticsearch.hosts');
        $formattedHosts = [];
        foreach ($hosts as $host) {
            $scheme           = $host['scheme'] ?? 'http';
            $formattedHosts[] = $scheme . '://' . (($host['user'] ?? null) ? $host['user'] . ':' . $host['pass'] . '@' : '') . $host['host'] . ':' . $host['port'];
        }

        if (null === $this->elasticsearch) {
            $builder             = ClientBuilder::create()->setHosts($formattedHosts);
            $this->elasticsearch = $builder->build();
        }
    }

    public function saved($model)
    {
        return;
        $this->elasticsearch->index([
            'index' => $model->getSearchIndex(),
            'type'  => $model->getSearchType(),
            'id'    => $model->id,
            'body'  => $model->toSearchArray(),
        ]);
    }

    public function deleted($model)
    {
        return;
        $this->elasticsearch->delete([
            'index' => $model->getSearchIndex(),
            'type'  => $model->getSearchType(),
            'id'    => $model->id,
        ]);
    }
}
