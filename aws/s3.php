<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

interface StorageInterface
{
    public function upload(string $sourcePath, string $destinationName): string;
    public function download(string $storedName, string $targetPath): bool;
    public function delete(string $storedName): bool;
}

class LocalStorage implements StorageInterface
{
    private string $baseDirectory;

    public function __construct(string $baseDirectory)
    {
        $this->baseDirectory = rtrim($baseDirectory, DIRECTORY_SEPARATOR);
        if (!is_dir($this->baseDirectory)) {
            mkdir($this->baseDirectory, 0775, true);
        }
    }

    public function upload(string $sourcePath, string $destinationName): string
    {
        $safeName = basename($destinationName);
        $targetPath = $this->baseDirectory . DIRECTORY_SEPARATOR . $safeName;

        if (!is_file($sourcePath)) {
            throw new RuntimeException('Upload source file does not exist.');
        }

        if (!copy($sourcePath, $targetPath)) {
            throw new RuntimeException('The assignment file could not be stored locally.');
        }

        return $safeName;
    }

    public function download(string $storedName, string $targetPath): bool
    {
        $sourcePath = $this->baseDirectory . DIRECTORY_SEPARATOR . basename($storedName);
        if (!is_file($sourcePath)) {
            return false;
        }

        return copy($sourcePath, $targetPath);
    }

    public function delete(string $storedName): bool
    {
        $path = $this->baseDirectory . DIRECTORY_SEPARATOR . basename($storedName);
        if (!is_file($path)) {
            return false;
        }

        return unlink($path);
    }
}

class S3Storage implements StorageInterface
{
    private string $bucket;
    private ?string $region;

    public function __construct(string $bucket, ?string $region = null)
    {
        $this->bucket = $bucket;
        $this->region = $region;
    }

    public function upload(string $sourcePath, string $destinationName): string
    {
        if (!class_exists('Aws\\S3\\S3Client')) {
            throw new RuntimeException('The AWS SDK for PHP is not installed.');
        }

        $client = new Aws\S3\S3Client([
            'version' => 'latest',
            'region' => $this->region ?? 'us-east-1',
            'credentials' => [
                'key' => getenv('AWS_ACCESS_KEY_ID') ?: '',
                'secret' => getenv('AWS_SECRET_ACCESS_KEY') ?: '',
            ],
        ]);

        $client->putObject([
            'Bucket' => $this->bucket,
            'Key' => $destinationName,
            'SourceFile' => $sourcePath,
            'ACL' => 'private',
        ]);

        return $destinationName;
    }

    public function download(string $storedName, string $targetPath): bool
    {
        if (!class_exists('Aws\\S3\\S3Client')) {
            return false;
        }

        $client = new Aws\S3\S3Client([
            'version' => 'latest',
            'region' => $this->region ?? 'us-east-1',
            'credentials' => [
                'key' => getenv('AWS_ACCESS_KEY_ID') ?: '',
                'secret' => getenv('AWS_SECRET_ACCESS_KEY') ?: '',
            ],
        ]);

        $result = $client->getObject([
            'Bucket' => $this->bucket,
            'Key' => $storedName,
            'SaveAs' => $targetPath,
        ]);

        return $result !== null;
    }

    public function delete(string $storedName): bool
    {
        if (!class_exists('Aws\\S3\\S3Client')) {
            return false;
        }

        $client = new Aws\S3\S3Client([
            'version' => 'latest',
            'region' => $this->region ?? 'us-east-1',
            'credentials' => [
                'key' => getenv('AWS_ACCESS_KEY_ID') ?: '',
                'secret' => getenv('AWS_SECRET_ACCESS_KEY') ?: '',
            ],
        ]);

        $client->deleteObject([
            'Bucket' => $this->bucket,
            'Key' => $storedName,
        ]);

        return true;
    }
}

function uploadFileToS3(string $sourcePath, string $key, string $bucket, ?string $region = null): string
{
    $storage = new S3Storage($bucket, $region);
    return $storage->upload($sourcePath, $key);
}

function downloadFileFromS3(string $storedName, string $targetPath, string $bucket, ?string $region = null): bool
{
    $storage = new S3Storage($bucket, $region);
    return $storage->download($storedName, $targetPath);
}

function deleteFileFromS3(string $storedName, string $bucket, ?string $region = null): bool
{
    $storage = new S3Storage($bucket, $region);
    return $storage->delete($storedName);
}
