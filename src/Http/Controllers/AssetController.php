<?php

declare(strict_types=1);

namespace ItsRD\BladeStorybook\Http\Controllers;

use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class AssetController
{
    private const TYPES = [
        'css' => 'text/css',
        'js' => 'application/javascript',
    ];

    public function __invoke(string $file): Response
    {
        $path = __DIR__.'/../../../resources/dist/'.$file;

        if (! is_file($path)) {
            throw new NotFoundHttpException('Storybook asset not found. Run "npm run build" in the package.');
        }

        $extension = pathinfo($file, PATHINFO_EXTENSION);

        return response(
            (string) file_get_contents($path),
            Response::HTTP_OK,
            [
                'Content-Type' => self::TYPES[$extension] ?? 'text/plain',
                'Cache-Control' => 'no-cache, must-revalidate',
            ]
        );
    }
}
