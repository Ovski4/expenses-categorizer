<?php

namespace App\Services\FileParser;

class FileParserRegistry
{
    private $fileParsers;

    public function __construct()
    {
        $this->fileParsers = [];
    }

    public function addFileParser(AbstractFileParser $fileParser): self
    {
        $this->fileParsers[] = $fileParser;

        return $this;
    }

    public function getFileParser($name): ?AbstractFileParser
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

        return null;
    }

    public function getFileParsers( string $fileType = null ): array
    {
        if ($fileType) {
            return array_filter(
                $this->fileParsers,
                function( AbstractFileParser $fileParser ) use ($fileType) {
                    return $fileParser->getFileType() == $fileType;
                }
            );
        }

        return $this->fileParsers;
    }
}
