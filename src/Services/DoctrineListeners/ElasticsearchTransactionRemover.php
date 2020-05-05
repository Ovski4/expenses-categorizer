<?php

namespace App\Services\DoctrineListeners;

use App\Entity\Transaction;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ElasticsearchTransactionRemover
{
    public function __construct(ParameterBagInterface $params)
    {
        $this->elasticsearchHost = $params->get('app.elasticsearch_host');
        $this->elasticsearchIndex = $params->get('app.elasticsearch_index');
    }

    public function remove(Transaction $transaction, LifecycleEventArgs $event)
    {
        $client = ClientBuilder::create()->setHosts([$this->elasticsearchHost])->build();

        $params = [
            'index' => $this->elasticsearchIndex,
            'id'    => $transaction->getId()
        ];

        try {
            $client->delete($params);
        } catch (Missing404Exception $e) {
            // if the transaction was never exported in elasticsearch and therefore does not exist, it's fine
        }
    }
}
