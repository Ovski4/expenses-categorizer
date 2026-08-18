<?php

namespace App\Services\FileParser;

class FileParserRegistry
{
    /**
     * @var array<int, AbstractFileParser>
     */
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

    /**
     * @param mixed $name accepts a non-string value on purpose: it is validated
     *                    (and rejected) at runtime below rather than by the
     *                    type system, since callers may pass unvalidated
     *                    route/request input here
     */
    public function getFileParser($name): ?AbstractFileParser
    {
        if (!is_string($name)) {
            throw new \Exception(sprintf('Expected argument of type "string", "%s" given', is_object($name) ? get_class($name) : gettype($name)));
        }

        foreach ($this->fileParsers as $fileParser) {
            if ($fileParser->getName() === $name) {
                return $fileParser;
            }
        }

        return null;
    }

    /**
     * @return array<int, AbstractFileParser>
     */
    public function getFileParsers(?string $fileType = null): array
    {
        if ($fileType) {
            return array_filter(
                $this->fileParsers,
                function (AbstractFileParser $fileParser) use ($fileType) {
                    return $fileParser->getFileType() == $fileType;
                }
            );
        }

        return $this->fileParsers;
    }
}
