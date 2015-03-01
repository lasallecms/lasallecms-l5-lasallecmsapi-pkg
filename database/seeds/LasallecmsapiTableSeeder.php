<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Lasallecms\Usermanagement\Models\User;
use Lasallecms\Usermanagement\Models\Group;
use Lasallecms\Usermanagement\Models\User_group;

class UsermanagementTableSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();

        User::create([
            'name'       => 'Bob Bloom',
            'email'      => 'admin@example.org',
            'password'   => bcrypt('password'),
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);

        Group::create([
            'title'       => 'Registered',
            'description' => 'Basic front-end access only.',
            'created_at' => new DateTime,
            'created_by' => 1,
            'updated_at' => new DateTime,
            'updated_by' => 1,
        ]);

        Group::create([
            'title'       => 'Administrator',
            'description' => 'Basic back-end administration access only.',
            'created_at' => new DateTime,
            'created_by' => 1,
            'updated_at' => new DateTime,
            'updated_by' => 1,
        ]);

        Group::create([
            'title'       => 'Super Administrator',
            'description' => 'Highest back-end administration access.',
            'created_at' => new DateTime,
            'created_by' => 1,
            'updated_at' => new DateTime,
            'updated_by' => 1,
        ]);


        User_group::create([
            'user_id'   => 1,
            'group_id'  => 1,
        ]);

        User_group::create([
            'user_id'   => 1,
            'group_id'  => 3,
        ]);

	}
}
