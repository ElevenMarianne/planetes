<?php

declare(strict_types=1);

namespace App\Service;

use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FileUploadService
{
    private const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    private const MAX_SIZE_BYTES = 5 * 1024 * 1024; // 5 MB

    public function __construct(
        private readonly FilesystemOperator $uploadsStorage,
        private readonly HttpClientInterface $httpClient,
    ) {}

    /**
     * @param string $subdirectory Ex: 'astronauts', 'planets', 'trophies'
     * @return string Le chemin stocké (relatif à la racine du storage)
     */
    public function upload(UploadedFile $file, string $subdirectory, ?string $basename = null): string
    {
        if (!in_array($file->getMimeType(), self::ALLOWED_MIMES, true)) {
            throw new \InvalidArgumentException('Format de fichier non autorisé. Utilisez JPEG, PNG, WebP ou GIF.');
        }

        if ($file->getSize() > self::MAX_SIZE_BYTES) {
            throw new \InvalidArgumentException('Le fichier est trop volumineux (max 5 Mo).');
        }

        $extension = $file->guessExtension() ?? 'jpg';
        $name = $basename !== null ? $basename : bin2hex(random_bytes(16));
        $filename = $subdirectory . '/' . $name . '.' . $extension;

        $stream = fopen($file->getRealPath(), 'r');
        $this->uploadsStorage->writeStream($filename, $stream);
        fclose($stream);

        return $filename;
    }

    public function uploadFromUrl(string $url, string $subdirectory): string
    {
        $response = $this->httpClient->request('GET', $url);
        $content  = $response->getContent();

        $contentType = $response->getHeaders()['content-type'][0] ?? 'image/jpeg';
        $mime        = strtok($contentType, ';');

        $extension = match ($mime) {
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
            default      => 'jpg',
        };

        $filename = $subdirectory . '/' . bin2hex(random_bytes(16)) . '.' . $extension;
        $this->uploadsStorage->write($filename, $content);

        return $filename;
    }

    public function remove(string $path): void
    {
        if ($this->uploadsStorage->fileExists($path)) {
            $this->uploadsStorage->delete($path);
        }
    }

    public function getPublicUrl(string $path): string
    {
        // En mode local, les fichiers sont dans public/uploads/
        return '/uploads/' . $path;
    }
}
