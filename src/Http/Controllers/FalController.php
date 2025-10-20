<?php

namespace Aifnet\Fal\Http\Controllers;

use Aifnet\Fal\Models\FalRequest;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class FalController
{
    public function download(Request $request, $falRequestId)
    {
        $falRequest = FalRequest::findByRequestId($falRequestId, $with = ['data']);
        $filename = $request->input('filename');
        $extension = $request->input('extension');
        $imageIndex = (int) ($request->input('index', 0));

        if (empty($falRequest) || empty($falRequest->id)) {
            return response()->json(['error' => 'Not found.'], 404);
        }

        if ($falRequest->status !== FalRequest::STATUS_COMPLETED) {
            return response()->json(['error' => 'Not completed yet.'], 400);
        }

        $output = $falRequest->data->output ?? null;

        if (empty($output)) {
            return response()->json(['error' => 'No output found.'], 404);
        }

        $imageData = $this->extractImageData($output, $imageIndex);

        if (! $imageData) {
            return response()->json(['error' => 'No image data found.'], 404);
        }

        $url = $imageData['url'];

        if (! $this->isValidFalUrl($url)) {
            return response()->json(['error' => 'Invalid URL.'], 403);
        }

        $downloadFilename = $this->resolveFilename($imageData, $falRequestId, $filename, $extension);
        $contentType = $this->resolveContentType($imageData);

        $res = $this->fetchRemoteFile($url);

        if ($res->getStatusCode() !== 200) {
            return response()->json(['error' => 'Failed to fetch file.'], 502);
        }

        return response()->streamDownload(function () use ($res) {
            $body = $res->getBody();
            while (! $body->eof()) {
                echo $body->read(8192);
                flush();
            }
        }, $downloadFilename, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'attachment; filename="' . $downloadFilename . '"',
            'Cache-Control' => 'private, max-age=0, must-revalidate',
            'Pragma' => 'public',
        ]);
    }

    private function extractImageData($output, int $index): ?array
    {
        if (isset($output['images']) && is_array($output['images'])) {
            if (! isset($output['images'][$index])) {
                return null;
            }

            return $output['images'][$index];
        }

        if (isset($output['url'])) {
            return [
                'url' => $output['url'],
                'file_name' => null,
                'content_type' => null,
            ];
        }

        return null;
    }

    private function isValidFalUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        return in_array($host, ['fal.ai', 'storage.fal.ai', 'cdn.fal.ai', 'v3.fal.media']);
    }

    private function resolveContentType(array $imageData): string
    {
        if (! empty($imageData['content_type'])) {
            return $imageData['content_type'];
        }

        $extension = null;

        if (!empty($imageData['file_name'])) {
            $extension = pathinfo($imageData['file_name'], PATHINFO_EXTENSION);
        } elseif (!empty($imageData['url'])) {
            $extension = $this->extractExtensionFromUrl($imageData['url']);
        }

        return $this->getContentTypeFromExtension($extension);
    }

    private function getContentTypeFromExtension(?string $extension): string
    {
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            'bmp' => 'image/bmp',
            'ico' => 'image/x-icon',
        ];

        $ext = strtolower($extension ?? '');

        return $mimeTypes[$ext] ?? 'application/octet-stream';
    }

    private function resolveFilename(
        array $imageData,
        string $falRequestId,
        ?string $filename,
        ?string $extension
    ): string {
        $baseName = $filename ?? $falRequestId;

        if ($extension !== null) {
            $ext = $extension;
        } else {
            $outputFileName = $imageData['file_name'] ?? null;
            if ($outputFileName) {
                $ext = pathinfo($outputFileName, PATHINFO_EXTENSION) ?: 'jpg';
            } else {
                $url = $imageData['url'] ?? null;
                $ext = $url ? ($this->extractExtensionFromUrl($url) ?? 'jpg') : 'jpg';
            }
        }

        return $baseName . '.' . $ext;
    }

    private function extractExtensionFromUrl(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (!$path) {
            return null;
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);

        return $extension ?: null;
    }

    private function fetchRemoteFile(string $url)
    {
        $client = new Client([
            'http_errors' => false,
            'timeout' => 60,
            'allow_redirects' => true,
        ]);

        return $client->request('GET', $url, ['stream' => true]);
    }
}
