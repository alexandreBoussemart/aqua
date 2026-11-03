<?php

namespace App\Tests\Unit\Cron;

use App\Command\Cron\OsmolateurCommand;
use Symfony\Component\Console\Tester\CommandTester;

/** Sécurités de l'osmolateur (rejouées à chaque `make test`). */
final class OsmolateurCommandTest extends CronTestCase
{
    private OsmolateurCommand $command;

    protected function setUp(): void
    {
        parent::setUp();
        $this->command = new OsmolateurCommand($this->tools, $this->gpio);
    }

    private function pump(): bool
    {
        return $this->gpio->relays['pompe_osmolateur'];
    }

    public function testLowLevelStartsFilling(): void
    {
        $this->gpio->inputs['osmolateur_niveau_ok'] = 0;
        $this->command->tick(25, 0);

        self::assertSame('state_3', $this->state('osmolateur')['value']);
        self::assertTrue($this->pump());
        self::assertNotSame('2000-01-01 00:00:00', $this->db->fetchOne("SELECT created_at FROM last_activity WHERE value = 'controle_osmolateur'"), 'contrôle mis à jour');
    }

    public function testLevelReachedTopsUpThenStops(): void
    {
        $this->setState('osmolateur', 'state_3');
        $this->gpio->relays['pompe_osmolateur'] = true;
        $start = microtime(true);
        $this->command->tick(25, 1);

        self::assertGreaterThanOrEqual(0.9, microtime(true) - $start, 'appoint d\'au moins 1 s');
        self::assertContains('pompe_osmolateur:on', $this->gpio->commands, 'pompe confirmée pendant l\'appoint (watchdog)');
        self::assertSame('state_2', $this->state('osmolateur')['value']);
        self::assertFalse($this->pump());
    }

    public function testTooHighDuringTopUpStopsPumpImmediately(): void
    {
        $this->setState('osmolateur', 'state_3');
        $this->gpio->relays['pompe_osmolateur'] = true;
        $this->gpio->onRead = static function (FakeGpioClient $gpio, int $read): void {
            if ($read === 3) { // 1re lecture = flotteurs, puis surveillance pendant l'appoint
                $gpio->inputs['osmolateur_trop_haut'] = 1;
            }
        };
        $start = microtime(true);
        $this->command->tick(25, 5);

        self::assertLessThan(2, microtime(true) - $start, 'appoint interrompu tout de suite');
        self::assertSame('state_1', $this->state('osmolateur')['value']);
        self::assertSame(1, $this->state('osmolateur')['error']);
        self::assertFalse($this->pump());
    }

    public function testTooHighStopsPump(): void
    {
        $this->setState('osmolateur', 'state_3');
        $this->gpio->relays['pompe_osmolateur'] = true;
        $this->gpio->inputs = ['osmolateur_trop_haut' => 1, 'osmolateur_niveau_ok' => 0] + $this->gpio->inputs;
        $this->command->tick(25, 0);

        self::assertSame('state_1', $this->state('osmolateur')['value']);
        self::assertFalse($this->pump());
    }

    public function testFillingTooLongRaisesErrorAndStopsPump(): void
    {
        $this->gpio->inputs['osmolateur_niveau_ok'] = 0;
        $this->setState('osmolateur', 'state_3', 30);
        $this->gpio->relays['pompe_osmolateur'] = true;
        $this->command->tick(25, 0);

        self::assertSame('state_8', $this->state('osmolateur')['value']);
        self::assertSame(1, $this->state('osmolateur')['error']);
        self::assertFalse($this->pump());

        $this->command->tick(25, 0); // reste bloqué en erreur tant que le niveau n'est pas atteint
        self::assertSame('state_8', $this->state('osmolateur')['value']);
        self::assertFalse($this->pump());
    }

    public function testFillingWithinMaxTimeKeepsPumping(): void
    {
        $this->gpio->inputs['osmolateur_niveau_ok'] = 0;
        $this->setState('osmolateur', 'state_3', 20);
        $this->command->tick(25, 0);

        self::assertSame('state_3', $this->state('osmolateur')['value']);
        self::assertTrue($this->pump());
    }

    public function testSwitchedOffNeverPumps(): void
    {
        $this->disable('on_off_osmolateur');
        $this->gpio->inputs['osmolateur_niveau_ok'] = 0;
        $this->command->tick(25, 0);

        self::assertSame('state_5', $this->state('osmolateur')['value']);
        self::assertFalse($this->pump());
    }

    public function testPauseTimerStopsPump(): void
    {
        $this->db->insert('timer', ['type' => 'osmolateur', 'off_until' => date('Y-m-d H:i:s', time() + 600)]);
        $this->gpio->inputs['osmolateur_niveau_ok'] = 0;
        $this->gpio->relays['pompe_osmolateur'] = true;
        $this->command->tick(25, 0);

        self::assertSame('state_97', $this->state('osmolateur')['value']);
        self::assertFalse($this->pump());
    }

    public function testGpioFailureStopsPumpFirstAndRecordsError(): void
    {
        $this->gpio->relays['pompe_osmolateur'] = true;
        $this->gpio->onRead = static function (FakeGpioClient $gpio): void {
            throw new \RuntimeException('BDD ou capteur en panne');
        };
        $tester = new CommandTester($this->command);

        self::assertSame(1, $tester->execute([]));
        self::assertSame('pompe_osmolateur:off', $this->gpio->commands[0], 'la pompe est coupée avant tout le reste');
        self::assertSame('state_7', $this->state('osmolateur')['value']);
        self::assertSame(0, (int)$this->db->fetchOne("SELECT pump_on FROM state_writes WHERE value = 'state_7'"), 'pompe déjà coupée quand l\'erreur est enregistrée');
    }

    public function testDatabaseDownStillStopsPump(): void
    {
        $this->gpio->relays['pompe_osmolateur'] = true;
        $this->gpio->inputs['osmolateur_niveau_ok'] = 0;
        $this->db->executeStatement('DROP TABLE core_config_state'); // la BDD ne répond plus
        $tester = new CommandTester($this->command);

        self::assertSame(1, $tester->execute([]));
        self::assertFalse($this->pump());
    }

    public function testDisabledEquipmentForcesPumpOff(): void
    {
        $this->disable('osmolateur');
        $this->gpio->relays['pompe_osmolateur'] = true;
        $tester = new CommandTester($this->command);

        self::assertSame(0, $tester->execute([]));
        self::assertSame('state_99', $this->state('osmolateur')['value']);
        self::assertFalse($this->pump());
    }
}
