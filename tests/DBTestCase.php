<?php

namespace Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Laravel\Sanctum\Sanctum;
use PDO;
use PDOException;
use Testcontainers\Container\StartedGenericContainer;
use Testcontainers\Modules\PostgresContainer;
use Testcontainers\Wait\WaitForLog;
use Modules\Core\Models\User;
use Modules\Core\Models\Person;
use Modules\Core\Models\Company;
use Modules\Core\Models\UserCompany;

abstract class DBTestCase extends TestCase
{
    protected static ?StartedGenericContainer $postgresContainer = null;
    protected ?string $seeder = null;
    protected static bool $containersStarted = false;
    protected static int $activeTestClasses = 0;
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
            version: '17-alpine',
            username: 'test',
            password: 'test',
            database: 'postgres',
        ))
            ->withWait(new WaitForLog('database system is ready to accept connections'))
            ->start();

        self::waitForPostgres();

        self::$containersStarted = true;

        echo "✅ Containers prontos\n";
    }

    protected static function waitForPostgres(int $maxAttempts = 40, int $sleepMs = 1500): void
    {
        $host = 'host.docker.internal';
        $port = self::$postgresContainer->getFirstMappedPort();
        $dsn = "pgsql:host={$host};port={$port};dbname=postgres";

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $pdo = new PDO(
                    $dsn,
                    'test',
                    'test',
                    [
                        PDO::ATTR_TIMEOUT => 3,
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    ]
                );

                $pdo->query('SELECT 1');

                echo "\n✅ Postgres pronto após {$attempt} tentativa(s)\n";
                return;
            } catch (PDOException $e) {
                echo '.';
                usleep($sleepMs * 1000);
            }
        }

        throw new \RuntimeException('Postgres não ficou pronto.');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->databaseName = 'test_' . bin2hex(random_bytes(8));

        $this->createDatabase();
        $this->configureLaravel();

        Artisan::call('migrate', ['--force' => true]);

        $this->seedModules();

        app(\Spatie\Permission\PermissionRegistrar::class)
            ->forgetCachedPermissions();
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

    protected function autenticarComPermissao(
        string $permission,
        ?Company $company = null,
        ?User $user = null,
        ?Person $person = null
    ): User {
        $company ??= Company::factory()->create();
        $person ??= Person::factory()->create();
        $user ??= User::factory()->create();
        $userCompany = UserCompany::factory()->create([
            'userId' => $user->id,
            'companyId' => $company->id,
            'personId' => $person->id,
        ]);

        session(['companyId' => $company->id]);

        app(\Spatie\Permission\PermissionRegistrar::class)
            ->setPermissionsTeamId($company->id);

        $userCompany->givePermissionTo($permission);

        Sanctum::actingAs($user);

        return $user;
    }

    protected function autenticarComRole(
        string $role,
        ?Company $company = null,
        ?User $user = null,
        ?Person $person = null
    ): User {
        $company ??= Company::factory()->create();
        $person ??= Person::factory()->create();
        $user ??= User::factory()->create();
        $userCompany = UserCompany::factory()->create([
            'userId' => $user->id,
            'companyId' => $company->id,
            'personId' => $person->id,
        ]);

        session(['companyId' => $company->id]);

        app(\Spatie\Permission\PermissionRegistrar::class)
            ->setPermissionsTeamId($company->id);

        $userCompany->assignRole($role);

        Sanctum::actingAs($user);

        return $user;
    }

    protected function autenticarSemPermissao(
        ?Company $company = null,
        ?User $user = null
    ): User {
        $company ??= Company::factory()->create();
        $user ??= User::factory()->create();
        $userCompany = UserCompany::factory()->create([
            'userId' => $user->id,
            'companyId' => $company->id,
        ]);

        session(['companyId' => $company->id]);

        app(\Spatie\Permission\PermissionRegistrar::class)
            ->setPermissionsTeamId($company->id);

        Sanctum::actingAs($user);

        return $user;
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
            'driver' => 'pgsql',
            'host' => 'host.docker.internal',
            'port' => self::$postgresContainer->getFirstMappedPort(),
            'database' => $this->databaseName,
            'username' => 'test',
            'password' => 'test',
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ]);

        DB::purge('pgsql');
        DB::reconnect('pgsql');
    }

    protected function adminPdo(): PDO
    {
        return new PDO(
            sprintf(
                'pgsql:host=host.docker.internal;port=%d;dbname=postgres',
                self::$postgresContainer->getFirstMappedPort()
            ),
            'test',
            'test',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]
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
             FROM pg_stat_activity
             WHERE datname = '%s'
             AND pid <> pg_backend_pid()",
            $this->databaseName
        ));

        $pdo->exec(
            sprintf('DROP DATABASE IF EXISTS "%s"', $this->databaseName)
        );
    }

    public static function tearDownAfterClass(): void
    {
        static::$activeTestClasses--;

        if (static::$activeTestClasses <= 0) {
            echo "\n🛑 Parando containers...\n";

            self::$postgresContainer?->stop();

            self::$postgresContainer = null;
            self::$containersStarted = false;
            static::$activeTestClasses = 0;
        }

        parent::tearDownAfterClass();
    }
}
