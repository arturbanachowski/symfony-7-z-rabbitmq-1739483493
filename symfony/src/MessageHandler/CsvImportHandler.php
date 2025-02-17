<?php

namespace App\MessageHandler;

use App\Message\CsvImportMessage;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Psr\Log\LoggerInterface;

#[AsMessageHandler]
class CsvImportHandler
{
    private LoggerInterface $logger;
    private Filesystem $filesystem;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->filesystem = new Filesystem();
    }

    public function __invoke(CsvImportMessage $message)
    {
        $filePath = __DIR__.'/../../var/csv/'.$message->getFilename();
        $progressFile = __DIR__.'/../../var/csv/csv_progress.json';
        $errorsFile = __DIR__.'/../../var/csv/csv_errors.json';

        if (!file_exists($filePath)) {
            $this->logger->error("File not found: $filePath");
            return;
        }

        $handle = fopen($filePath, 'r');
        fgetcsv($handle, 1000, ",");

        $totalRows = count(file($filePath)) - 1;
        $processed = 0;
        $errors = [];

        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            $processed++;

            echo "Processing row: $processed\n";
            flush();

            if (count($data) < 4) {
                $errors[] = ['row' => $processed, 'reason' => 'Missing columns'];
                continue;
            }

            [$id, $fullName, $email, $city] = $data;

            if (!ctype_digit($id) || (int)$id <= 0) {
                $errors[] = ['row' => $processed, 'reason' => "Invalid ID: $id"];
            }

            if (str_word_count($fullName) < 2) {
                $errors[] = ['row' => $processed, 'reason' => "Invalid Full Name: $fullName"];
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = ['row' => $processed, 'reason' => "Invalid Email: $email"];
            }

            if (empty(trim($city))) {
                $errors[] = ['row' => $processed, 'reason' => "Invalid City: $city"];
            }

            $this->filesystem->dumpFile($errorsFile, json_encode($errors));

            $progress = round(($processed / $totalRows) * 100);
            $this->filesystem->dumpFile($progressFile, json_encode(['progress' => $progress]));
        }

        $summaryFile = __DIR__.'/../../var/csv/csv_summary.json';

        $summaryData = [
            'totalRows' => $totalRows,
            'processedRows' => $processed,
            'errorsCount' => count($errors),
            'success' => count($errors) === 0,
            'errors' => $errors
        ];

        $this->filesystem->dumpFile($summaryFile, json_encode($summaryData, JSON_PRETTY_PRINT));

        fclose($handle);
        unlink($filePath);
    }
}
