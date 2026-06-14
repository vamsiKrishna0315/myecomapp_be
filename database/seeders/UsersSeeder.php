<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

final class UsersSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@mail.com'],
            [
                'id' => 1,
                'name' => 'Admin',
                'user_mobile_no' => null,
                'user_role' => 'admin',
                'email_verified_at' => null,
                'password' => '$2y$12$CcCo75ZF7OVukJuuL44djeDGX.yiNSRpf9D3gg0fN0956kAruYytq',
                'address' => null,
                'address_proof' => null,
                'finger_print' => null,
                'joining_date' => null,
                'alternate_number' => null,
                'dob' => null,
                'salary' => null,
                'store_id' => null,
                'location' => null,
                'store_lat' => null,
                'store_lng' => null,
                'status' => 1,
                'remember_token' => null,
                'reputation' => 0,
                'created_at' => '2025-10-19 06:08:05',
                'updated_at' => '2025-10-24 13:10:51',
            ]
        );

        $user = User::query()->where('email', 'admin@mail.com')->first();

        if ($user && $user->user_role && Role::where('name', $user->user_role)->exists()) {
            $user->syncRoles([$user->user_role]);
        }
    }
}
