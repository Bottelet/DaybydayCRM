<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class UsersTableSeeder extends Seeder
{
    public const ADMIN_EMAIL = 'admin@admin.com';

    /**
     * Seed the well-known admin user, id=1, relied on elsewhere (e.g.
     * UserRoleTableSeeder assigns the owner role to User::orderBy('id')->first()).
     *
     * Idempotent by design, unlike an earlier version of this seeder that
     * unconditionally did `DB::table('users')->delete()` before inserting -
     * fine the first time, but a hard failure (FK constraint violation) the
     * moment any other seeder call re-runs this after clients, tasks, etc.
     * already reference this row. CoreSeeder's other sub-seeders are all
     * genuinely safe to re-run for the same reason; this one wasn't.
     *
     * @return void
     */
    public function run()
    {
        if (DB::table('users')->where('email', self::ADMIN_EMAIL)->exists()) {
            return;
        }

        DB::table('users')->insert([
            0 => [
                'id'               => 1,
                'external_id'      => Uuid::uuid4(),
                'name'             => 'Admin',
                'email'            => self::ADMIN_EMAIL,
                'password'         => bcrypt('admin123'),
                'address'          => '',
                'primary_number'   => null,
                'secondary_number' => null,
                'image_path'       => '',
                'remember_token'   => null,
                'created_at'       => '2016-06-04 13:42:19',
                'updated_at'       => '2016-06-04 13:42:19',
            ],
        ]);
    }
}
