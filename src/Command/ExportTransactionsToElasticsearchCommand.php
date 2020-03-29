<?php

namespace App\Command;

use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportTransactionsToElasticsearchCommand extends Command
{
    protected static $defaultName = 'app:export-transactions';

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Export transactions to elasticsearch')
            ->setHelp('This command send transactions in elasticsearch')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = ClientBuilder::create()->setHosts(['elasticsearch:9200'])->build();

        $this->createIndexIfNotExists($client);
        $output->writeln('The <info>transactions</info> index has been created');

        $transactions = $this->entityManager
            ->getRepository(Transaction::class)
            ->findBy([], ['created_at'=>'asc'])
        ;

        foreach ($transactions as $transaction) {
            $params = [
                'index' => 'transactions',
                'id'    => $transaction->getId(),
                'body'  => $transaction->toArray()
            ];

            $response = $client->index($params);

            if (!in_array($response['result'], ['created', 'updated'])) {
                throw new \Exception('Error creating or updating transaction');
            }
        }

        $output->writeln(sprintf('<info>%s</info> transactions indexed', count($transactions)));
    }

    private function createIndexIfNotExists(Client $client)
    {
        $indexParams['index']  = 'transactions';   
        if ($client->indices()->exists($indexParams)) {
            return;
        }

        $params = [
            'index' => 'transactions',
            'body' => [
                'mappings' => [ 
                    'properties' => [
                        'label' => [
                            'type' => 'text'
                        ],
                        'created_at' => [
                            'type' => 'date'
                        ],
                        'sub_category' => [
                            'type' => 'keyword'
                        ],
                        'top_category' => [
                            'type' => 'keyword'
                        ],
                        'type' => [
                            'type' => 'keyword'
                        ],
                        'amount' => [
                            'type' => 'float'
                        ]
                    ]
                ]
            ]
        ];

        $client->indices()->create($params);
    }
}
