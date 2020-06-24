<?php

namespace App\Observers;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Aws\Credentials\CredentialProvider;

class ElasticSearchObserver
{
    private $elasticsearch;

    public function __construct()
    {
        $host = config('elasticsearch.hosts');
        $provider = CredentialProvider::defaultProvider();
        if (is_null($this->elasticsearch)) {
            $builder = ClientBuilder::create()->setHosts($host);
            $this->elasticsearch = $builder->build();
        }
    }

    public function saved($model)
    {
        return;
        $this->elasticsearch->index([
            'index' => $model->getSearchIndex(),
            'type' => $model->getSearchType(),
            'id' => $model->id,
            'body' => $model->toSearchArray(),
        ]);
    }

    public function deleted($model)
    {
        return;
        $this->elasticsearch->delete([
            'index' => $model->getSearchIndex(),
            'type' => $model->getSearchType(),
            'id' => $model->id,
        ]);
    }
}
