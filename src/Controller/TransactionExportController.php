<?php

namespace App\Controller;

use App\Event\TransactionExportedEvent;
use App\Services\Exporter\CsvExporter;
use App\Services\Exporter\ElasticsearchExporter;
use Doctrine\ORM\EntityManagerInterface;
use Elasticsearch\Common\Exceptions\NoNodesAvailableException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/transaction/export')]
class TransactionExportController extends AbstractController
{
    #[Route('/elasticsearch', name: 'elasticsearch_export', methods: ['PATCH', 'GET'])]
    public function exportToElasticsearch(
        Request $request,
        ElasticsearchExporter $exporter,
        TranslatorInterface $translator,
        EventDispatcherInterface $dispatcher,
        EntityManagerInterface $entityManager)
    : Response
    {
        if ($request->isMethod('PATCH')) {
            try {
                $createdTransactions = [];
                $updatedTransactions = [];

                $dispatcher->addListener(
                    TransactionExportedEvent::NAME,
                    function (TransactionExportedEvent $event) use (&$createdTransactions, &$updatedTransactions) {
                        $response = $event->getResponse();
                        $transaction = $event->getTransaction();

                        if ($response['result'] === 'created') {
                            $createdTransactions[] = $transaction;
                        } else if ($response['result'] === 'updated') {
                            $updatedTransactions[] = $transaction;
                        }

                        $transaction->setToSyncInElasticsearch(false);
                    }
                );

                $exporter->exportAllSync();

                $entityManager->flush();

                return $this->render('transaction/export.html.twig', [
                    'total_transactions_count' => count($createdTransactions) + count($updatedTransactions),
                    'created_transactions_count' => count($createdTransactions),
                    'updated_transactions_count' => count($updatedTransactions)
                ]);

            } catch(NoNodesAvailableException $e) {
                return $this->render('transaction/export.html.twig', [
                    'error' => $translator->trans('Elasticsearch seems to be down')
                ]);
            }
        }

        return $this->render('transaction/export.html.twig');
    }

    #[Route('/csv', name: 'csv_export', methods: ['GET'])]
    public function exportToCsv(CsvExporter $csvExporter): Response
    {
        $csv = $csvExporter->export();

        $response = new Response($csv);
        $response->headers->set('Content-Type', 'application/csv');
        $response->headers->set(
            'Content-Disposition',
            sprintf(
                'attachment; filename="transactions_%s.csv"',
                (new \DateTime('now'))->format('Y-m-d')
            )
        );

        return $response;
    }
}
