<?php

namespace App\Command;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ExportModulImmoDataCommand extends Command
{
    protected static $defaultName = 'app:export-modulimmo';

    private $client;

    public function __construct(ParameterBagInterface $params)
    {
        $this->client = ClientBuilder::create()->setHosts([$params->get('app.elasticsearch_host')])->build();

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Export modulimmo data to elasticsearch')
            ->setHelp('This command creates an index and sends data in elasticsearch')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->createIndexIfNotExists();
        $output->writeln('The <info>modulimmo</info> index has been created');

        $string = file_get_contents(sprintf('%s/tableau_ammortissement.json', dirname(__FILE__)));
        $records = json_decode($string, true);

        foreach ($records as $record) {
            $this->createRecord($record, 'interests_month');
            $this->createRecord($record, 'capital_month');
        }

        $output->writeln(sprintf('<info>%s</info> records indexed', count($records)*2));
    }

    private function createRecord($data, $keyword)
    {
        $params = [
            'index' => 'modulimmo',
            'id'    => sha1(sprintf('%s%s', $data['date'], $keyword)),
            'body'  => [
                'date' => $data['date'],
                'amount' => $data[$keyword],
                'keyword' => $keyword
            ]
        ];

        $response = $this->client->index($params);

        if (!in_array($response['result'], ['created', 'updated'])) {
            throw new \Exception('Error creating or updating record');
        }
    }

    private function createIndexIfNotExists()
    {
        $indexParams['index']  = 'modulimmo';
        if ($this->client->indices()->exists($indexParams)) {
            return;
        }

        $params = [
            'index' => 'modulimmo',
            'body' => [
                'mappings' => [
                    'properties' => [
                        'date' => [
                            'type' => 'date'
                        ],
                        'amount' => [
                            'type' => 'float'
                        ],
                        'type' => [
                            'type' => 'keyword'
                        ]
                    ]
                ]
            ]
        ];

        $this->client->indices()->create($params);
    }
}
