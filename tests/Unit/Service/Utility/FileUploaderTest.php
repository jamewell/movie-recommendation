<?php

namespace App\Tests\Unit\Service\Utility;

use App\Service\Utility\FileUploader;
use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploaderTest extends TestCase
{
    public function testUploadProfilePictureSuccessfully(): void
    {
        $profileStorage = $this->createProfileStorageMock();
        $fileUploader = $this->createFileUploader($profileStorage);
        $uploadedFile = $this->createUploadedFileMock();
        $tempFile = $this->createTemporaryFilePath();

        $uploadedFile
            ->method('getMimeType')
            ->willReturn('image/jpeg');

        $uploadedFile
            ->method('guessExtension')
            ->willReturn('jpg');
        $uploadedFile
            ->method('getPathname')
            ->willReturn($tempFile);
        $uploadedFile
            ->method('getMimeType')
            ->willReturn('image/jpeg');

        $profileStorage
            ->expects(self::once())
            ->method('writeStream')
            ->willReturnCallback(function (string $filename, $stream) {
                self::assertSame('user1.jpg', $filename);
                self::assertIsResource($stream);
            });

        $fileName = $fileUploader->uploadProfilePicture($uploadedFile, 'user1');

        self::assertSame('user1.jpg', $fileName);

        unlink($tempFile);
    }

    public function testUploadProfilePictureWithInvalidMimeType(): void
    {
        $profileStorage = $this->createProfileStorageMock();
        $fileUploader = $this->createFileUploader($profileStorage);
        $uploadedFile = $this->createUploadedFileMock();
        $tempFile = $this->createTemporaryFilePath();

        $uploadedFile
            ->method('getMimeType')
            ->willReturn('application/pdf');

        $uploadedFile
            ->method('guessExtension')
            ->willReturn('pdf');
        $uploadedFile
            ->method('getPathname')
            ->willReturn($tempFile);
        $uploadedFile
            ->method('getMimeType')
            ->willReturn('application/pdf');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid file type. Only images are allowed.');

        $fileUploader->uploadProfilePicture($uploadedFile, 'user1');

        unlink($tempFile);
    }

    public function testUploadProfilePictureWithFilesystemException(): void
    {
        $profileStorage = $this->createProfileStorageMock();
        $fileUploader = $this->createFileUploader($profileStorage);
        $uploadedFile = $this->createUploadedFileMock();
        $tempFile = $this->createTemporaryFilePath();

        $uploadedFile
            ->method('getMimeType')
            ->willReturn('image/jpeg');
        $uploadedFile
            ->method('guessExtension')
            ->willReturn('jpg');
        $uploadedFile
            ->method('getPathname')
            ->willReturn($tempFile);
        $uploadedFile
            ->method('getMimeType')
            ->willReturn('image/jpeg');

        $profileStorage
            ->method('writeStream')
            ->willThrowException(new \RuntimeException('Unable to write file.'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to write file.');

        $fileUploader->uploadProfilePicture($uploadedFile, 'user1');

        unlink($tempFile);
    }

    public function testUploadProfilePictureWithInvalidFileStream(): void
    {
        $profileStorage = $this->createProfileStorageMock();
        $fileUploader = $this->createFileUploader($profileStorage);
        $uploadedFile = $this->createUploadedFileMock();

        $uploadedFile
            ->method('getMimeType')
            ->willReturn('image/jpeg');
        $uploadedFile
            ->method('guessExtension')
            ->willReturn('jpg');
        $uploadedFile
            ->method('getPathname')
            ->willReturn('/path/to/non/existing/file.jpg');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('An error occurred while uploading the file: fopen(/path/to/non/existing/file.jpg): Failed to open stream: No such file or directory');

        $fileUploader->uploadProfilePicture($uploadedFile, 'user1');
    }

    public function testUploadProfilePictureWithEmptyExtension(): void
    {
        $profileStorage = $this->createProfileStorageMock();
        $fileUploader = $this->createFileUploader($profileStorage);
        $uploadedFile = $this->createUploadedFileMock();
        $tempFile = $this->createTemporaryFilePath();

        $uploadedFile
            ->method('getMimeType')
            ->willReturn('image/jpeg');
        $uploadedFile
            ->method('guessExtension')
            ->willReturn('');
        $uploadedFile
            ->method('getPathname')
            ->willReturn($tempFile);
        $uploadedFile
            ->method('getMimeType')
            ->willReturn('image/jpeg');

        $profileStorage
            ->method('writeStream')
            ->willThrowException(new \RuntimeException('Unable to determine file extension.'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to determine file extension.');

        $fileUploader->uploadProfilePicture($uploadedFile, 'user1');

        unlink($tempFile);
    }

    private function createFileUploader(FilesystemOperator $storage): FileUploader
    {
        return new FileUploader($storage);
    }

    private function createProfileStorageMock(): FilesystemOperator&MockObject
    {
        return $this->createMock(FilesystemOperator::class);
    }

    private function createUploadedFileMock(): UploadedFile&MockObject
    {
        return $this->createMock(UploadedFile::class);
    }

    private function createTemporaryFilePath(): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test');

        if (false === $tempFile) {
            throw new \RuntimeException('Failed to create temporary file.');
        }

        file_put_contents($tempFile, 'test content');

        return $tempFile;
    }
}
