<?php

namespace Database\Seeders;

use App\Models\Link;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Local demo account. Password is the factory default: "password".
        $user = User::factory()->create([
            'name' => 'Test User',
            'username' => 'test',
            'email' => 'test@example.com',
        ]);

        Link::factory()->for($user)->create(['code' => 'demo', 'url' => 'https://laravel.com/docs', 'title' => 'Laravel docs']);
    }
}
