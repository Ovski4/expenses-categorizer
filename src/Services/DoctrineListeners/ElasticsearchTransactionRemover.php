<?php

namespace App\Services\DoctrineListeners;

use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ElasticsearchTransactionRemover
{
    private string $elasticsearchHost;
    private string $elasticsearchIndex;

    public function __construct(ParameterBagInterface $params)
    {
        $this->elasticsearchHost = $params->get('app.elasticsearch_host');
        $this->elasticsearchIndex = $params->get('app.elasticsearch_index');
    }

    /**
     * @param LifecycleEventArgs<EntityManagerInterface> $event
     */
    public function remove(Transaction $transaction, LifecycleEventArgs $event): void
    {
        $client = ClientBuilder::create()->setHosts([$this->elasticsearchHost])->build();

        $params = [
            'index' => $this->elasticsearchIndex,
            'id' => $transaction->getId(),
        ];

        try {
            $client->delete($params);
        } catch (Missing404Exception $e) {
            // if the transaction was never exported in elasticsearch and therefore does not exist, it's fine
        }
    }
}
