<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\Routing\Annotation\Route;

class ProgressController extends AbstractController
{
    private CacheInterface $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    #[Route('/progress', name: 'import_progress')]
    public function progress(): JsonResponse
    {
        $progressFile = __DIR__.'/../../var/csv/csv_progress.json';
        $errorsFile = __DIR__.'/../../var/csv/csv_errors.json';

        $progressData = file_exists($progressFile) ? json_decode(file_get_contents($progressFile), true) : ['progress' => 0];

        $errorsData = file_exists($errorsFile) ? json_decode(file_get_contents($errorsFile), true) : [];

        return new JsonResponse([
            'progress' => $progressData['progress'] ?? 0,
            'errors' => $errorsData
        ]);
    }


}
