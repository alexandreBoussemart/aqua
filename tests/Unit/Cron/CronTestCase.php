<?php

namespace App\Tests\Unit\Cron;

use App\Command\Cron\CronTools;
use App\Config\AppConfig;
use App\Mail\Notifier;
use App\Repository\ActivityRepository;
use App\Repository\ConfigRepository;
use App\Repository\LogRepository;
use App\Repository\StateRepository;
use App\Repository\StatusRepository;
use App\Repository\TimerRepository;
use App\Service\Relay;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use PHPUnit\Framework\TestCase;

/**
 * Base des tests de crons : vrais dépôts sur une base SQLite en mémoire (tables utiles seulement),
 * interrupteurs dans un dossier temporaire, faux service GPIO.
 */
abstract class CronTestCase extends TestCase
{
    protected Connection $db;
    protected FakeGpioClient $gpio;
    protected CronTools $tools;
    protected string $varDir;

    protected function setUp(): void
    {
        $this->gpio = new FakeGpioClient();
        $this->db = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $pdo = $this->db->getNativeConnection();
        \assert($pdo instanceof \PDO);
        $pdo->sqliteCreateFunction('now', static fn() => date('Y-m-d H:i:s'), 0);
        foreach ([
            'CREATE TABLE core_config_state (id INTEGER PRIMARY KEY, created_at TEXT, path TEXT, value TEXT, error INT DEFAULT 0, message TEXT, mail_send INT DEFAULT 0, exclude_check INT DEFAULT 1, label TEXT)',
            'CREATE TABLE core_config_value (id INTEGER PRIMARY KEY, name TEXT, value TEXT, type TEXT, groupe INT, label TEXT, show_time_left INT DEFAULT 0, description TEXT)',
            'CREATE TABLE core_config_status (id INTEGER PRIMARY KEY, name TEXT, value INT, label TEXT, description TEXT)',
            'CREATE TABLE last_activity (id INTEGER PRIMARY KEY, created_at TEXT, value TEXT, label TEXT)',
            'CREATE TABLE timer (type TEXT PRIMARY KEY, off_until TEXT)',
            'CREATE TABLE log (id INTEGER PRIMARY KEY, message TEXT, created_at TEXT DEFAULT CURRENT_TIMESTAMP)',
            'CREATE TABLE log_mail (id INTEGER PRIMARY KEY, sujet TEXT, message TEXT, created_at TEXT DEFAULT CURRENT_TIMESTAMP)',
        ] as $sql) {
            $this->db->executeStatement($sql);
        }
        // journal des écritures d'état avec l'état de la pompe à cet instant (vérifie l'ordre « couper puis signaler »)
        $pdo->sqliteCreateFunction('pump_on', fn() => (int)$this->gpio->relays['pompe_osmolateur'], 0);
        $this->db->executeStatement('CREATE TABLE state_writes (path TEXT, value TEXT, pump_on INT)');
        $this->db->executeStatement('CREATE TRIGGER state_written AFTER UPDATE ON core_config_state BEGIN INSERT INTO state_writes VALUES (NEW.path, NEW.value, pump_on()); END');
        foreach (['osmolateur', 'ecumeur', 'bailling'] as $path) {
            $this->db->insert('core_config_state', ['path' => $path, 'value' => 'state_0', 'created_at' => date('Y-m-d H:i:s'), 'label' => $path]);
            $this->db->insert('last_activity', ['value' => 'controle_' . $path, 'created_at' => '2000-01-01 00:00:00']);
        }

        $this->varDir = sys_get_temp_dir() . '/aqua-test-' . bin2hex(random_bytes(4));
        mkdir($this->varDir . '/status', 0777, true);
        $this->enable('osmolateur', 'on_off_osmolateur', 'ecumeur', 'on_off_ecumeur', 'bailling');

        $logs = new LogRepository($this->db);
        $status = new StatusRepository($this->db, $this->varDir);
        $this->tools = new CronTools(
            $status,
            new StateRepository($this->db, $logs, $this->varDir),
            new ActivityRepository($this->db),
            new ConfigRepository($this->db),
            new TimerRepository($this->db),
            $logs,
            new Notifier(new AppConfig($this->varDir . '/absent.json'), $status, $logs),
            new Relay($this->gpio),
        );
    }

    protected function tearDown(): void
    {
        exec('rm -rf ' . escapeshellarg($this->varDir));
    }

    protected function enable(string ...$names): void
    {
        foreach ($names as $name) {
            touch($this->varDir . '/status/' . $name);
        }
    }

    protected function disable(string $name): void
    {
        @unlink($this->varDir . '/status/' . $name);
    }

    /** @return array{value: string, error: int, message: string} */
    protected function state(string $path): array
    {
        $row = $this->db->fetchAssociative('SELECT value, error, message FROM core_config_state WHERE path = ?', [$path]);
        \assert(is_array($row));

        return ['value' => (string)$row['value'], 'error' => (int)$row['error'], 'message' => (string)$row['message']];
    }

    /** Force un état (et son cache fichier) comme s'il datait de $secondsAgo secondes. */
    protected function setState(string $path, string $value, int $secondsAgo = 0): void
    {
        $this->tools->states->set($path, $value, false, 'test');
        $this->db->executeStatement('UPDATE core_config_state SET created_at = ? WHERE path = ?', [date('Y-m-d H:i:s', time() - $secondsAgo), $path]);
    }
}
