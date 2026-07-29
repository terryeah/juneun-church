<?php

namespace Tests\Feature;

use App\Filament\Resources\Photos\Pages\CreatePhoto;
use App\Filament\Resources\Photos\Pages\ListPhotos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Guards the photo admin against the default "만들기" wording; every
 * create-style control must read 업로드 instead.
 */
class PhotoAdminLabelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_photo_list_and_create_pages_say_upload_not_create(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('super_admin');

        foreach (['ViewAny:Photo', 'View:Photo', 'Create:Photo'] as $permission) {
            \Spatie\Permission\Models\Permission::query()->firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            $user->givePermissionTo($permission);
        }

        Livewire::actingAs($user)->test(ListPhotos::class)
            ->assertSee('업로드')
            ->assertDontSee('만들기');

        Livewire::actingAs($user)->test(CreatePhoto::class)
            ->assertSee('업로드')
            ->assertDontSee('만들기');
    }
}
