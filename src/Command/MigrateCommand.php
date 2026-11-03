<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Migrations SQL simples : chaque fichier migrations/*.sql est exécuté une seule fois (ordre alphabétique),
 * la liste des fichiers appliqués est conservée dans la table app_migration.
 */
#[AsCommand('app:db:migrate', 'Applique les migrations SQL de migrations/ pas encore exécutées')]
final class MigrateCommand extends Command
{
    public function __construct(private readonly Connection $db, private readonly string $projectDir)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Liste les migrations à appliquer sans les exécuter');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->db->executeStatement(
            'CREATE TABLE IF NOT EXISTS `app_migration` (`name` varchar(255) NOT NULL PRIMARY KEY, `applied_at` datetime NOT NULL DEFAULT current_timestamp()) DEFAULT CHARSET=utf8mb4'
        );
        $applied = array_flip($this->db->fetchFirstColumn('SELECT `name` FROM `app_migration`'));
        $files = glob($this->projectDir . '/migrations/*.sql') ?: [];
        sort($files);

        $count = 0;
        foreach ($files as $file) {
            $name = basename($file);
            if (isset($applied[$name])) {
                continue;
            }
            $output->writeln(($input->getOption('dry-run') ? '[à appliquer] ' : '[appliquée] ') . $name);
            if (!$input->getOption('dry-run')) {
                foreach ($this->statements((string)file_get_contents($file)) as $sql) {
                    $output->writeln('  ' . $sql, OutputInterface::VERBOSITY_VERBOSE);
                    $this->db->executeStatement($sql);
                }
                $this->db->insert('app_migration', ['name' => $name]);
            }
            $count++;
        }
        $output->writeln($count ? "$count migration(s)." : 'Base à jour.');

        return Command::SUCCESS;
    }

    /** Découpe un fichier SQL simple (pas de procédures, une requête se termine par « ; » en fin de ligne). @return list<string> */
    private function statements(string $sql): array
    {
        $lines = array_filter(preg_split('/\R/', $sql), static fn(string $l) => !str_starts_with(ltrim($l), '--'));

        // une requête se termine par « ; » en fin de ligne (les « ; » à l'intérieur d'un texte sont conservés)
        return array_values(array_filter(array_map('trim', preg_split('/;\s*$/m', implode("\n", $lines)))));
    }
}
