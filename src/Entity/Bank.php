<?php

namespace App\Entity;

abstract class Bank
{
    const CREDIT_MUTUEL = [
        'name' => 'Crédit Mutuel',
        'parserName' => 'credit-mutuel'
    ];

    const CAISSE_EPARGNE = [
        'name' => 'Caisse d\'épargne',
        'parserName' => 'caisse-epargne'
    ];

    public static function getAll(): array {
        return [
            self::CAISSE_EPARGNE,
            self::CREDIT_MUTUEL
        ];
    }

    public static function getByParserName($parserName) {
        foreach (self::getAll() as $bank) {
            if ($bank['parserName'] === $parserName) {
                return $bank;
            }
        }

        throw new \Exception(sprintf('Bank with parser "%s" not found', $parserName));
    }
}
