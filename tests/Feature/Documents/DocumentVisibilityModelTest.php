<?php

namespace Tests\Feature\Documents;

use App\Enums\DocumentVisibility;
use App\Models\Building;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentVisibilityModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_visibility_is_cast_and_scope_relations_resolve(): void
    {
        $owner = User::factory()->create();
        $landlord = User::factory()->create();
        $building = Building::factory()->create(['landlord_id' => $landlord->id]);
        $student = User::factory()->create();

        $buildingDoc = $owner->documents()->create([
            'name' => 'Huisregels',
            'type' => 'other',
            'visibility' => DocumentVisibility::Building,
            'building_id' => $building->id,
        ]);

        $userDoc = $owner->documents()->create([
            'name' => 'Voor student',
            'type' => 'other',
            'visibility' => DocumentVisibility::User,
            'shared_with_user_id' => $student->id,
        ]);

        $this->assertInstanceOf(DocumentVisibility::class, $buildingDoc->refresh()->visibility);
        $this->assertSame(DocumentVisibility::Building, $buildingDoc->visibility);
        $this->assertTrue($buildingDoc->building->is($building));
        $this->assertTrue($userDoc->sharedWithUser->is($student));
    }

    public function test_default_visibility_is_private(): void
    {
        $owner = User::factory()->create();
        $doc = $owner->documents()->create(['name' => 'X', 'type' => 'other']);

        $this->assertSame(DocumentVisibility::Private, $doc->refresh()->visibility);
    }
}
