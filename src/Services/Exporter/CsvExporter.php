<?php

namespace App\Services\Exporter;

use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CsvExporter
{
    private $entityManager;

    private $translator;

    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    private function arrayToCsvString($input, $delimiter = ',', $enclosure = '"')
    {
        $fp = fopen('php://temp', 'r+');
        fputcsv($fp, $input, $delimiter, $enclosure);
        rewind($fp);
        $data = fread($fp, 1048576);
        fclose($fp);

        return rtrim($data, "\n");
    }

    public function export()
    {
        $transactions = $this->entityManager
            ->getRepository(Transaction::class)
            ->findBy([], ['createdAt'=>'asc'])
        ;

        $header = [
            'Id',
            $this->translator->trans('Label'),
            $this->translator->trans('Currency'),
            $this->translator->trans('Account'),
            $this->translator->trans('Date'),
            $this->translator->trans('Amount'),
            $this->translator->trans('Transaction type'),
            $this->translator->trans('Top category'),
            $this->translator->trans('Sub category')
        ];

        $lines = $this->arrayToCsvString($header) . "\n";

        foreach ($transactions as $transaction)
        {
            $lines = $lines . $this->arrayToCsvString($transaction->toArray($this->translator)) . "\n";
        }

        return $lines;
    }
}
