<?php

namespace Tests\Feature\Foundation;

use App\Domain\EtlBridge\Models\TiniRawModel;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Fixtures\OperationalRecord;
use Tests\TestCase;

class ArchitectureFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('operational_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('name');
            $table->text('secret')->nullable();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('operational_records');

        parent::tearDown();
    }

    public function test_user_model_supports_roles_and_branch_access(): void
    {
        $branch = Branch::factory()->create();
        $otherBranch = Branch::factory()->create();
        $user = User::factory()->create([
            'active_branch_id' => $branch->id,
        ]);

        $user->branches()->attach($branch);

        $permission = Permission::create([
            'name' => 'branches.view-all',
            'guard_name' => 'web',
        ]);

        $role = Role::create([
            'name' => 'Dueño',
            'guard_name' => 'web',
        ]);

        $role->givePermissionTo($permission);
        $user->assignRole($role);

        $this->assertTrue($user->hasRole('Dueño'));
        $this->assertTrue($user->canAccessBranch($branch->id));
        $this->assertTrue($user->canAccessBranch($otherBranch->id));
        $this->assertTrue($user->hasGlobalBranchAccess());
    }

    public function test_branch_scoped_models_fail_closed_and_autofill_branch_id(): void
    {
        $branch = Branch::factory()->create();
        $otherBranch = Branch::factory()->create();

        Context::add('branch_id', $branch->id);
        Context::addHidden('branch_scope_bypass', false);

        $first = OperationalRecord::create([
            'name' => 'Scoped',
        ]);

        OperationalRecord::withoutGlobalScope('branch')->create([
            'branch_id' => $otherBranch->id,
            'name' => 'Other Branch',
        ]);

        $records = OperationalRecord::query()->pluck('name')->all();

        $this->assertSame($branch->id, $first->branch_id);
        $this->assertSame(['Scoped'], $records);
    }

    public function test_encryptable_trait_uses_native_encrypted_casts_and_audits_context(): void
    {
        $branch = Branch::factory()->create();
        $user = User::factory()->create([
            'active_branch_id' => $branch->id,
        ]);

        Context::add([
            'branch_id' => $branch->id,
            'user_id' => $user->id,
        ]);
        Context::addHidden('branch_scope_bypass', false);

        $record = OperationalRecord::create([
            'name' => 'Sensitive',
            'secret' => 'top-secret',
        ]);

        $rawSecret = DB::table('operational_records')
            ->where('id', $record->id)
            ->value('secret');

        $activity = DB::table('activity_log')
            ->where('subject_type', OperationalRecord::class)
            ->where('subject_id', $record->id)
            ->first();

        $this->assertNotSame('top-secret', $rawSecret);
        $this->assertSame('top-secret', $record->fresh()->secret);
        $this->assertNotNull($activity);
        $this->assertStringContainsString((string) $branch->id, (string) $activity->properties);
    }

    public function test_tini_raw_models_are_read_only_by_default(): void
    {
        $model = new class extends TiniRawModel
        {
            protected $table = 'tini_raw.products';

            protected $fillable = ['name'];
        };

        $model->fill(['name' => 'Should fail']);

        $this->expectExceptionMessage('tini_raw models are read-only');

        $model->save();
    }
}
