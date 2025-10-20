<?php

namespace Aifnet\Fal\Http\Controllers;

use Aifnet\Fal\Models\FalRequest;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class FalController
{
    public function download(Request $request, $falRequestId)
    {
        $falRequest = FalRequest::findByRequestId($falRequestId, ['data']);

        if (empty($falRequest)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        if ($falRequest->status !== FalRequest::STATUS_COMPLETED) {
            return response()->json(['error' => 'Processing not complete'], 400);
        }

        $output = $falRequest->data->output ?? null;
        if (empty($output)) {
            return response()->json(['error' => 'No output available'], 404);
        }

        $imageIndex = (int) $request->input('index', 0);
        $imageData = $this->extractImageData($output, $imageIndex);

        if (empty($imageData)) {
            return response()->json(['error' => 'Image not found'], 404);
        }

        $url = $imageData['url'] ?? null;

        if (empty($url) || ! $this->isValidFalUrl($url)) {
            return response()->json(['error' => 'Invalid file source'], 403);
        }

        $filename = $request->input('filename');
        $extension = $request->input('extension');
        $downloadFilename = $this->resolveFilename($imageData, $falRequestId, $filename, $extension);
        $contentType = $this->resolveContentType($imageData);

        try {
            $response = $this->fetchRemoteFile($url);

            if ($response->getStatusCode() !== 200) {
                return response()->json(['error' => 'Download failed'], 502);
            }

            return response()->streamDownload(
                function () use ($response) {
                    $body = $response->getBody();
                    while (! $body->eof()) {
                        echo $body->read(8192);
                        flush();
                    }
                },
                $downloadFilename,
                [
                    'Content-Type' => $contentType,
                    'Content-Disposition' => 'attachment; filename="' . $downloadFilename . '"',
                    'Cache-Control' => 'private, max-age=0, must-revalidate',
                    'Pragma' => 'public',
                ]
            );
        } catch (\Exception $e) {
            return response()->json(['error' => 'Download failed'], 502);
        }
    }

    private function extractImageData($output, $index)
    {
        return $output['images'][$index] ?? $output['image'][$index] ?? null;
    }

    private function isValidFalUrl($url)
    {
        $host = parse_url($url, PHP_URL_HOST);
        $allowedHosts = ['fal.ai', 'storage.fal.ai', 'cdn.fal.ai', 'v3.fal.media'];

        return in_array($host, $allowedHosts);
    }

    private function resolveContentType($imageData)
    {
        if (!empty($imageData['content_type'])) {
            return $imageData['content_type'];
        }

        $extension = $this->getExtension($imageData);
        return $this->getContentTypeFromExtension($extension);
    }

    private function getExtension($imageData)
    {
        if (!empty($imageData['file_name'])) {
            return pathinfo($imageData['file_name'], PATHINFO_EXTENSION);
        }

        if (!empty($imageData['url'])) {
            return $this->extractExtensionFromUrl($imageData['url']);
        }

        return null;
    }

    private function getContentTypeFromExtension($extension)
    {
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
        ];

        $ext = strtolower($extension ?? '');
        return $mimeTypes[$ext] ?? 'application/octet-stream';
    }

    private function resolveFilename($imageData, $falRequestId, $filename, $extension)
    {
        $baseName = $filename ?: $falRequestId;

        if ($extension) {
            return $baseName . '.' . $extension;
        }

        $ext = $this->getExtension($imageData) ?: 'jpg';
        return $baseName . '.' . $ext;
    }

    private function extractExtensionFromUrl($url)
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (!$path) {
            return null;
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        return $extension ?: null;
    }

    private function fetchRemoteFile($url)
    {
        $client = new Client([
            'http_errors' => false,
            'timeout' => 60,
            'allow_redirects' => true,
        ]);

        return $client->request('GET', $url, ['stream' => true]);
    }
}