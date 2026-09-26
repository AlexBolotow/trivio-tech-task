<?php

namespace Tests\Integration\Modules\Import;

use App\Modules\Import\Domain\Model\SupplierImport;
use App\Modules\Import\Domain\Model\SupplierImportChunk;
use App\Modules\Import\Domain\Repository\SupplierImportChunkRepository;
use App\Modules\Import\Domain\Repository\SupplierImportRepository;
use App\Modules\Import\Domain\ValueObject\SupplierImportChunkStatus;
use App\Modules\Import\Domain\ValueObject\SupplierImportStatus;
use Database\Seeders\SupplierSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use Tests\TestCase;

final class SupplierImportPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_supplier_seeder_is_idempotent_and_uses_supplier_codes_as_keys(): void
    {
        $seeder = new SupplierSeeder();
        $seeder->run();
        $seeder->run();

        $this->assertDatabaseCount('suppliers', 2);
        $this->assertDatabaseHas('suppliers', ['code' => 'expedia', 'name' => 'Expedia']);
        $this->assertDatabaseHas('suppliers', ['code' => 'pegas', 'name' => 'Pegas']);
    }

    public function test_import_repository_round_trips_uuid_status_counters_and_binary_checksum(): void
    {
        $this->seed(SupplierSeeder::class);
        $supplierId = (int) $this->app['db']->table('suppliers')->where('code', 'expedia')->value('id');
        $id = (string) Str::uuid();
        $checksum = hash('sha256', 'catalog contents');
        $repository = $this->app->make(SupplierImportRepository::class);
        $import = new SupplierImport(
            id: $id,
            supplierId: $supplierId,
            sourceVersion: 'daily-2026-09-26',
            sourceChecksum: $checksum,
            sourceUri: 'file:///supplier/expedia.ndjson',
            childTaskSizeRecords: 500,
            minSuccessRate: 80,
        );
        $import->planChunks(5);
        $import->start(new \DateTimeImmutable('2026-09-26T10:00:00Z'));
        $import->finish(4, 1, new \DateTimeImmutable('2026-09-26T10:05:00Z'));

        $repository->save($import);
        $restored = $repository->find($id);

        self::assertNotNull($restored);
        self::assertSame($checksum, $restored->sourceChecksum);
        self::assertSame(SupplierImportStatus::Done, $restored->status());
        self::assertSame(5, $restored->totalChunks());
        self::assertSame(4, $restored->successfulChunks());
        self::assertSame(1, $restored->failedChunks());
    }

    public function test_chunk_repository_round_trips_json_and_domain_state(): void
    {
        $this->seed(SupplierSeeder::class);
        $supplierId = (int) $this->app['db']->table('suppliers')->where('code', 'pegas')->value('id');
        $importId = (string) Str::uuid();
        $import = new SupplierImport(
            id: $importId,
            supplierId: $supplierId,
            sourceVersion: null,
            sourceChecksum: hash('sha256', 'pegas xml'),
            sourceUri: 'file:///supplier/pegas.xml',
            childTaskSizeRecords: 250,
            minSuccessRate: 80,
        );
        $import->planChunks(1);
        $import->start(new \DateTimeImmutable('2026-09-26T10:00:00Z'));
        $this->app->make(SupplierImportRepository::class)->save($import);

        $repository = $this->app->make(SupplierImportChunkRepository::class);
        $chunk = $repository->add(new SupplierImportChunk(
            id: null,
            supplierImportId: $importId,
            chunkNumber: 1,
            sourceLocator: ['xml_path' => '/catalog/hotel', 'batch' => 1],
            recordsCount: 250,
        ));
        $chunk->start(new \DateTimeImmutable('2026-09-26T10:01:00Z'));
        $repository->save($chunk);

        $restored = $repository->find((int) $chunk->id);

        self::assertNotNull($restored);
        self::assertEquals(['xml_path' => '/catalog/hotel', 'batch' => 1], $restored->sourceLocator);
        self::assertSame(SupplierImportChunkStatus::InProgress, $restored->status());
    }

    public function test_it_prevents_duplicate_external_hotel_ids_for_the_same_supplier(): void
    {
        $this->seed(SupplierSeeder::class);
        $supplierId = (int) $this->app['db']->table('suppliers')->where('code', 'expedia')->value('id');
        $supplierHotel = [
            'supplier_id' => $supplierId,
            'external_hotel_id' => 'EXP-001',
            'name' => 'Example Hotel',
            'city_name' => 'London',
            'country_code' => 'GB',
            'link_status' => 'unmatched',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        $this->app['db']->table('supplier_hotels')->insert($supplierHotel);

        $this->expectException(QueryException::class);
        $this->app['db']->table('supplier_hotels')->insert($supplierHotel);
    }

    public function test_database_rejects_supplier_settings_outside_supported_ranges(): void
    {
        $this->seed(SupplierSeeder::class);
        $supplierId = (int) $this->app['db']->table('suppliers')->where('code', 'expedia')->value('id');

        $this->expectException(QueryException::class);
        $this->app['db']->table('supplier_import_settings')->insert([
            'supplier_id' => $supplierId,
            'enabled' => true,
            'check_interval_minutes' => 60,
            'child_task_size_records' => 500,
            'min_success_rate' => 101,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
