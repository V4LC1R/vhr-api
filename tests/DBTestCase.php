<?php

namespace Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use PDO;
use PDOException;
use Testcontainers\Container\GenericContainer;
use Testcontainers\Container\StartedGenericContainer;
use Testcontainers\Modules\PostgresContainer;
use Testcontainers\Wait\WaitForLog;
use Illuminate\Support\Facades\File;

abstract class DBTestCase extends TestCase
{
    protected static ?StartedGenericContainer $postgresContainer = null;
    protected static ?StartedGenericContainer $redisContainer    = null;
    protected ?string $seeder = null;
    protected static bool $containersStarted   = false;
    protected static int $activeTestClasses   = 0;

    protected string $databaseName;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::bootContainers();
        static::$activeTestClasses++;
    }

    protected static function bootContainers(): void
    {
        if (self::$containersStarted) {
            return;
        }

        echo "\n🐳 Iniciando containers...\n";

        self::$postgresContainer = (new PostgresContainer(
            version:  '17-alpine',
            username: 'test',
            password: 'test',
            database: 'postgres',
        ))
            ->withWait(new WaitForLog('database system is ready to accept connections'))
            ->start();

        // self::$redisContainer = (new GenericContainer('redis:alpine'))
        //     ->withExposedPorts(6379)
        //     ->withWait(new WaitForLog('Ready to accept connections'))
        //     ->start();

        self::waitForPostgres();

        self::$containersStarted = true;

        echo "✅ Containers prontos\n";
    }

    protected static function waitForPostgres(int $maxAttempts = 40, int $sleepMs = 1500): void
    {
        // 127.0.0.1 é o loopback do container de testes, não do host.
        // host.docker.internal aponta para o host real no Docker Desktop.
        $host = 'host.docker.internal';
        $port = self::$postgresContainer->getFirstMappedPort();
        $dsn  = "pgsql:host={$host};port={$port};dbname=postgres";

        echo "\n⏳ Aguardando Postgres em {$host}:{$port}";

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $socket = @fsockopen($host, $port, $errno, $errstr, 2);
            if ($socket) {
                fclose($socket);
            } else {
                echo '.';
                usleep($sleepMs * 1_000);
                continue;
            }

            try {
                $pdo = new PDO($dsn, 'test', 'test', [
                    PDO::ATTR_TIMEOUT => 3,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);
                $pdo->query('SELECT 1');

                echo "\n✅ Postgres pronto após {$attempt} tentativa(s)\n";
                return;
            } catch (PDOException $e) {
                echo "\n⚠️  PDO falhou (tentativa {$attempt}): " . $e->getMessage();
                usleep($sleepMs * 1_000);
            }
        }

        throw new \RuntimeException(
            "Postgres não ficou pronto após {$maxAttempts} tentativas ({$sleepMs}ms cada)."
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->databaseName = 'test_' . bin2hex(random_bytes(8));

        $this->createDatabase();
        $this->configureLaravel();

        Artisan::call('migrate', ['--force' => true]);

        $this->seedModules();

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function seedModules(): void
    {
        foreach (File::directories(base_path('Modules')) as $modulePath) {
            $moduleName = basename($modulePath);
            $fqcn = "Modules\\{$moduleName}\\Database\\Seeders\\{$moduleName}DatabaseSeeder";

            if (class_exists($fqcn)) {
                Artisan::call('db:seed', [
                '--class' => $fqcn,
                '--force' => true,
                ]);
            }
        }
    }

    protected function tearDown(): void
    {
        DB::disconnect('pgsql');
        $this->dropDatabase();
        parent::tearDown();
    }

    protected function configureLaravel(): void
    {
        Config::set('database.default', 'pgsql');
        Config::set('database.connections.pgsql', [
            'driver'         => 'pgsql',
            'host'           => 'host.docker.internal',
            'port'           => self::$postgresContainer->getFirstMappedPort(),
            'database'       => $this->databaseName,
            'username'       => 'test',
            'password'       => 'test',
            'charset'        => 'utf8',
            'prefix'         => '',
            'prefix_indexes' => true,
            'schema'         => 'public',
            'sslmode'        => 'prefer',
        ]);

        DB::purge('pgsql');
        DB::reconnect('pgsql');
    }

    protected function adminPdo(): PDO
    {
        return new PDO(
            sprintf(
                'pgsql:host=host.docker.internal;port=%d;dbname=postgres',
                self::$postgresContainer->getFirstMappedPort(),
            ),
            'test',
            'test',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
        );
    }

    protected function createDatabase(): void
    {
        $this->adminPdo()->exec(
            sprintf('CREATE DATABASE "%s"', $this->databaseName)
        );
    }

    protected function dropDatabase(): void
    {
        $pdo = $this->adminPdo();

        $pdo->exec(sprintf(
            "SELECT pg_terminate_backend(pid)
             FROM   pg_stat_activity
             WHERE  datname = '%s'
             AND    pid <> pg_backend_pid()",
            $this->databaseName,
        ));

        $pdo->exec(sprintf('DROP DATABASE IF EXISTS "%s"', $this->databaseName));
    }

    public static function tearDownAfterClass(): void
    {
        static::$activeTestClasses--;

        if (static::$activeTestClasses <= 0) {
            echo "\n🛑 Parando containers...\n";

            self::$postgresContainer?->stop();
            //self::$redisContainer?->stop();

            self::$postgresContainer   = null;
            //self::$redisContainer      = null;
            self::$containersStarted   = false;
            static::$activeTestClasses = 0;
        }

        parent::tearDownAfterClass();
    }
}
