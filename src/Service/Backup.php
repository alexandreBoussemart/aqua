<?php

namespace App\Service;

use App\Config\AppConfig;
use Symfony\Component\Process\Process;

/** Dump SQL compressé de la base (var/backup/dump_<base>_<date>.zip). */
final class Backup
{
    public function __construct(private readonly AppConfig $config, private readonly string $varDir)
    {
    }

    public function dump(): string
    {
        $db = $this->config->database();
        $dir = $this->varDir . '/backup';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $base = sprintf('%s/dump_%s_%s', $dir, $db['database'], date('Ymd-H\hi'));

        $binary = (new Process(['sh', '-c', 'command -v mariadb-dump || command -v mysqldump']))->mustRun()->getOutput();
        $process = new Process(
            [trim($binary), '--host=' . $db['host'], '--user=' . $db['user'], '--single-transaction', '--routines', '--result-file=' . $base . '.sql', '--databases', $db['database']],
            null,
            ['MYSQL_PWD' => $db['passwd']]
        );
        $process->setTimeout(600)->mustRun();

        $zip = new \ZipArchive();
        if ($zip->open($base . '.zip', \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Impossible de créer ' . $base . '.zip');
        }
        $zip->addFile($base . '.sql', basename($base . '.sql'));
        $zip->close();
        unlink($base . '.sql');

        return $base . '.zip';
    }
}
