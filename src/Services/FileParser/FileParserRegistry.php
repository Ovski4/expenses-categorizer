<?php

namespace App\Services\FileParser;

class FileParserRegistry
{
    private $fileParsers;

    public function __construct()
    {
        $this->fileParsers = [];
    }

    public function addFileParser(AbstractFileParser $fileParser)
    {
        $this->fileParsers[] = $fileParser;

        return $this;
    }

    public function getFileParser($name)
    {
        if (!is_string($name)) {
            throw new \Exception(sprintf(
                'Expected argument of type "string", "%s" given',
                is_object($name) ? get_class($name) : gettype($name)
            ));
        }

        foreach ($this->fileParsers as $fileParser) {
            if ($fileParser->getName() === $name) {
                return $fileParser;
            }
        }

        throw new \InvalidArgumentException(sprintf(
            'Could not load file parser "%s". Available file parsers are %s',
            $name,
            implode(', ', array_map(function($fileParser) {
                return $fileParser->getName();
            }, $this->fileParsers))
        ));
    }

    public function getfileParsers()
    {
        return $this->fileParsers;
    }
}
