<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Lasallecms\Lasallecmsapi\Models\Category;
use Lasallecms\Lasallecmsapi\Models\Tag;

class LasallecmsapiTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        Category::create([
            'parent_id'   => 0,
            'title'       => 'Blog',
            'description' => 'The main blog category',
            'enabled'     => 1,
            'created_at' => new DateTime,
            'created_by' => 1,
            'updated_at' => new DateTime,
            'updated_by' => 1,
        ]);

        Tag::create([
            'title'       => 'blog',
            'description' => 'The main blog tag',
            'enabled'     => 1,
            'created_at' => new DateTime,
            'created_by' => 1,
            'updated_at' => new DateTime,
            'updated_by' => 1,
        ]);
    }
}