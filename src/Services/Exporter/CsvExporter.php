<?php

namespace App\Services\Exporter;

use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CsvExporter
{
    private EntityManagerInterface $entityManager;

    private TranslatorInterface $translator;

    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    /**
     * @param array<array-key, mixed> $input
     */
    private function arrayToCsvString(array $input, string $delimiter = ',', string $enclosure = '"'): string
    {
        $fp = fopen('php://temp', 'r+');

        if (false === $fp) {
            throw new \RuntimeException('Unable to open a temporary stream to build the CSV content.');
        }

        fputcsv($fp, $input, $delimiter, $enclosure);
        rewind($fp);
        $data = fread($fp, 1048576);
        fclose($fp);

        if (false === $data) {
            throw new \RuntimeException('Unable to read the CSV content back from the temporary stream.');
        }

        return rtrim($data, "\n");
    }

    public function export(): string
    {
        $transactions = $this->entityManager
            ->getRepository(Transaction::class)
            ->findBy([], ['createdAt' => 'asc'])
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
            $this->translator->trans('Sub category'),
        ];

        $lines = $this->arrayToCsvString($header)."\n";

        foreach ($transactions as $transaction) {
            $lines = $lines.$this->arrayToCsvString($transaction->toArray($this->translator))."\n";
        }

        return $lines;
    }
}
