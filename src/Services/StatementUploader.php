<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class StatementUploader
{
    public function upload(UploadedFile $file)
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_-] remove; Lower()', $originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();
        $file->move('/var/statements', $fileName);

        return $fileName;
    }
}
