<?php

namespace App\Service;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    public function __construct(
        private readonly FilesystemOperator $profileStorage,
    ) {
    }

    /**
     * @throws FilesystemException
     */
    public function uploadProfilePicture(UploadedFile $file, string $userId): string
    {
        return $this->uploadFile($this->profileStorage, $file, $userId);
    }

    /**
     * @throws FilesystemException
     */
    private function uploadFile(FilesystemOperator $storage, UploadedFile $file, string $prefix): string
    {
        $fileName = sprintf('%s.%s', $prefix, $file->guessExtension());
        $stream = fopen($file->getPathname(), 'r');

        if (!$stream) {
            throw new \RuntimeException('Unable to open file.');
        }

        $storage->writeStream($fileName, $stream);
        fclose($stream);

        return $fileName;
    }
}
