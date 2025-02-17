<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
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

    #[Route('/summary', name: 'import_summary')]
    public function summary(): Response
    {
        $summaryFile = __DIR__.'/../../var/csv/csv_summary.json';

        if (!file_exists($summaryFile)) {
            return $this->render('import_summary.html.twig', [
                'summary' => null
            ]);
        }

        $summaryData = json_decode(file_get_contents($summaryFile), true);

        $summary = [
            'totalRows' => $summaryData['totalRows'] ?? 0,
            'processedRows' => $summaryData['processedRows'] ?? 0,
            'errorsCount' => isset($summaryData['errors']) ? count($summaryData['errors']) : 0,
            'errors' => $summaryData['errors'] ?? [],
            'success' => ($summaryData['processedRows'] ?? 0) > 0 && empty($summaryData['errors'])
        ];

        return $this->render('import_summary.html.twig', [
            'summary' => $summary
        ]);
    }
}
