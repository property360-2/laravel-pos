<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryUniquenessTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_category_name_is_unique_case_insensitively(): void
    {
        $this->actingAs($this->admin())->postJson('/api/categories', ['name' => 'Beverages'])->assertStatus(201);

        $response = $this->actingAs($this->admin())->postJson('/api/categories', ['name' => 'beverages']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    public function test_category_name_can_be_reused_after_soft_delete(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->postJson('/api/categories', ['name' => 'Snacks'])->assertStatus(201);

        $category = Category::where('name', 'Snacks')->first();
        $this->actingAs($admin)->deleteJson("/api/categories/{$category->id}")->assertNoContent();

        $this->actingAs($admin)->postJson('/api/categories', ['name' => 'snacks'])->assertStatus(201);
    }

    public function test_renaming_to_an_existing_case_variant_is_rejected(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->postJson('/api/categories', ['name' => 'Raw Materials'])->assertStatus(201);
        $second = $this->actingAs($admin)->postJson('/api/categories', ['name' => 'Packaging'])->json('id');

        $response = $this->actingAs($admin)->putJson("/api/categories/{$second}", ['name' => 'RAW MATERIALS']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }
}
