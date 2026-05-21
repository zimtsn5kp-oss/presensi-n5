<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_dashboard(): void
    {
        $this->seed();

        $admin = User::whereHas('role', fn ($query) => $query->where('name', 'administrator'))->first()
            ?? User::factory()->create(['role_id' => Role::firstOrCreate(['name' => 'administrator'], ['display_name' => 'Administrator'])->id]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Dashboard');
    }
}
