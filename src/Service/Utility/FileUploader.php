<?php

namespace App\Service\Utility;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    public function __construct(
        private readonly FilesystemOperator $profileStorage,
    ) {
    }

    /**
     * @throws FilesystemException
     */
    public function uploadProfilePicture(UploadedFile $file, string $userId): string
    {
        if (!in_array($file->getMimeType(), self::ALLOWED_MIME_TYPES, true)) {
            throw new \InvalidArgumentException('Invalid file type. Only images are allowed.');
        }

        return $this->uploadFile($this->profileStorage, $file, $userId);
    }

    private function uploadFile(FilesystemOperator $storage, UploadedFile $file, string $prefix): string
    {
        try {
            $fileName = sprintf('%s.%s', $prefix, $file->guessExtension());
            $stream = fopen($file->getPathname(), 'r');

            if (!$stream) {
                throw new \RuntimeException('Unable to open file.');
            }

            $storage->writeStream($fileName, $stream);
            fclose($stream);

            return $fileName;
        } catch (\Throwable $e) {
            throw new \RuntimeException('An error occurred while uploading the file: '.$e->getMessage());
        }
    }
}
