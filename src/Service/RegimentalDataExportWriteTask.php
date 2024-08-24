<?php

declare(strict_types=1);

namespace App\Service;

use League\Csv\Writer;
use Psr\Log\LoggerInterface;
use Symfony\Component\Scheduler\Attribute\AsCronTask;

#[AsCronTask("*/13 * * * *", method: "s1ApiOperationsOperationsRecords")]
#[AsCronTask("*/15 * * * *", method: "s1ApiMasterRawMilpacs")]
#[AsCronTask("*/17 * * * *", method: "profiles")]
class RegimentalDataExportWriteTask
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface                         $milpacUpdatesLogger,
        private readonly RegimentalDataExporter $regimentalDataExporter,
    )
    {
        $this->logger = $milpacUpdatesLogger;
    }

    public function s1ApiOperationsOperationsRecords()
    {
        $rows = iterator_to_array($this->regimentalDataExporter->s1ApiOperationsOperationsRecords());
        $writer = Writer::createFromString();
        if ($rows) {
            $writer->insertOne(array_keys($rows[0]));
            $writer->insertAll($rows);
        }
        file_put_contents(\BASE_PATH . "/public/export/s1-operations-operations-records.csv", $writer->toString());
        $this->logger->info(sprintf("written %d rows to s1-operations-operations-records.csv", count($rows)));
    }

    public function s1ApiMasterRawMilpacs()
    {
        $rows = iterator_to_array($this->regimentalDataExporter->s1ApiMasterRawMilpacs());
        $writer = Writer::createFromString();
        if ($rows) {
            $writer->insertOne(array_keys($rows[0]));
            $writer->insertAll($rows);
        }
        file_put_contents(\BASE_PATH . "/public/export/s1-api-master-rawmilpacs.csv", $writer->toString());
        $this->logger->info(sprintf("written %d rows to s1-api-master-rawmilpacs.csv", count($rows)));
    }

    public function profiles()
    {
        $rows = iterator_to_array($this->regimentalDataExporter->profiles());
        $writer = Writer::createFromString();
        if ($rows) {
            $writer->insertOne(array_keys($rows[0]));
            $writer->insertAll($rows);
            $writer->forceEnclosure();
        }
        file_put_contents(\BASE_PATH . "/public/export/profiles.csv", $writer->toString());
        $this->logger->info(sprintf("written %d rows to profiles.csv", count($rows)));
    }
}
