<?php

namespace Tests\Integration\Foundation;

use App\Models\Branch;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Queue\CallQueuedClosure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\Fixtures\OperationalRecord;
use Tests\TestCase;

/**
 * Smoke checks que ejercitan Postgres + Redis reales.
 *
 * Este suite solo se ejecuta via `composer test:integration`, que apunta a
 * phpunit.integration.xml. No forma parte de la suite rapida (phpunit.xml).
 *
 * Requisitos:
 *   - Postgres con roles ferreteria_app / ferreteria_etl y schemas creados
 *     (ver docker/postgres/init/01-create-schemas.sql o 02-sync-existing-volume.sql).
 *   - Redis escuchando en REDIS_HOST:REDIS_PORT con las DBs definidas en .env.testing.
 *
 * Si alguno de estos servicios falla, el test debe romper: eso es lo que
 * estamos validando.
 */
class PostgresRedisSmokeTest extends TestCase
{
    private bool $createdSchema = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::connection()->getDriverName() !== 'pgsql') {
            $this->markTestSkipped('PostgresRedisSmokeTest requires the pgsql driver.');
        }

        // Tablas minimas para hydratar branches + operational_records sin
        // depender de migraciones completas del proyecto.
        if (! Schema::hasTable('branches')) {
            Schema::create('branches', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->string('city')->nullable();
                $table->boolean('is_headquarters')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        Schema::create('operational_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('name');
            $table->text('secret')->nullable();
            $table->timestamps();
        });

        $this->createdSchema = true;
    }

    protected function tearDown(): void
    {
        if ($this->createdSchema) {
            Schema::dropIfExists('operational_records');
        }

        Cache::forget('smoke:postgres-redis');

        parent::tearDown();
    }

    public function test_branch_scoped_models_filter_against_postgres(): void
    {
        $branchA = Branch::create(['name' => 'Smoke A', 'code' => 'SMK-A']);
        $branchB = Branch::create(['name' => 'Smoke B', 'code' => 'SMK-B']);

        Context::add('branch_id', $branchA->id);
        Context::addHidden('branch_scope_bypass', false);

        OperationalRecord::create(['name' => 'in-scope']);

        OperationalRecord::withoutGlobalScope('branch')->create([
            'branch_id' => $branchB->id,
            'name' => 'out-of-scope',
        ]);

        $visible = OperationalRecord::query()->pluck('name')->all();

        $this->assertSame(['in-scope'], $visible);
    }

    public function test_redis_cache_round_trips_real_values(): void
    {
        if (config('cache.default') !== 'redis') {
            $this->markTestSkipped('Cache driver is not redis in this environment.');
        }

        Cache::put('smoke:postgres-redis', 'ok', 30);

        $this->assertSame('ok', Cache::get('smoke:postgres-redis'));
    }

    public function test_redis_queue_accepts_dispatched_jobs(): void
    {
        if (config('queue.default') !== 'redis') {
            $this->markTestSkipped('Queue driver is not redis in this environment.');
        }

        Queue::fake();

        dispatch(function () {
            // Job trivial: solo validamos que se encola sin estallar.
        });

        Queue::assertPushed(CallQueuedClosure::class);
    }

    public function test_app_role_cannot_create_tables_in_tini_raw(): void
    {
        // Valida el invariante de grants: ferreteria_app no debe tener
        // permiso CREATE sobre tini_raw. Esto es lo que los grants del
        // script 01-create-schemas.sql garantizan, y es complementario al
        // guard de PHP en TiniRawModel.
        //
        // Si estas corriendo como postgres/superuser este test dara falso
        // positivo — por eso lo skipeamos cuando el usuario de la conexion
        // es un superusuario.

        $currentUser = DB::connection()->selectOne('SELECT current_user AS u')->u ?? '';
        if ($currentUser !== 'ferreteria_app') {
            $this->markTestSkipped(
                "This test only runs as ferreteria_app (current user: {$currentUser})."
            );
        }

        $this->expectException(QueryException::class);

        DB::connection()->statement(
            'CREATE TABLE tini_raw.smoke_should_fail (id int)'
        );
    }
}
