<?php

namespace App\Controller;

use App\Message\CsvImportMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class CsvImportController extends AbstractController
{
    #[Route('/import', name: 'import_csv', methods: ['GET', 'POST'])]
    public function upload(Request $request, MessageBusInterface $bus): Response
    {
        if ($request->isMethod('POST')) {
            $file = $request->files->get('csv_file');

            if ($file instanceof UploadedFile && $file->getClientOriginalExtension() === 'csv') {
                $uploadDir = $this->getParameter('csv_directory'); // Ensure this is set in services.yaml
                $newFilename = uniqid('import_', true) . '.csv';

                try {
                    $file->move($uploadDir, $newFilename);

                    $bus->dispatch(new CsvImportMessage($newFilename));

                    $this->addFlash('success', 'CSV file uploaded! Processing in background.');
                } catch (FileException $e) {
                    $this->addFlash('error', 'Upload failed.');
                }
            } else {
                $this->addFlash('error', 'Invalid file type. Please upload a CSV file.');
            }
        }

        return $this->render('import.html.twig');
    }
}
