<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Lasallecms\Lasallecmsapi\Models\Category;
use Lasallecms\Lasallecmsapi\Models\Tag;
use Lasallecms\Lasallecmsapi\Models\Lookup_workflow_status;

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


        ////////////////////////////////////////////////////
        //           Lookup_workflow_status               //
        ////////////////////////////////////////////////////

        Lookup_workflow_status::create([
            'title'       => 'In Progress',
            'description' => 'Currently being edited.',
            'enabled'     => 1,
            'created_at' => new DateTime,
            'created_by' => 1,
            'updated_at' => new DateTime,
            'updated_by' => 1,
        ]);

        Lookup_workflow_status::create([
            'title'       => 'Awaiting Approval',
            'description' => 'Waiting for approval of edits.',
            'enabled'     => 1,
            'created_at' => new DateTime,
            'created_by' => 1,
            'updated_at' => new DateTime,
            'updated_by' => 1,
        ]);

        Lookup_workflow_status::create([
            'title'       => 'Approved',
            'description' => 'Edits are approved.',
            'enabled'     => 1,
            'created_at' => new DateTime,
            'created_by' => 1,
            'updated_at' => new DateTime,
            'updated_by' => 1,
        ]);

        Lookup_workflow_status::create([
            'title'       => 'Published',
            'description' => 'Published.',
            'enabled'     => 1,
            'created_at' => new DateTime,
            'created_by' => 1,
            'updated_at' => new DateTime,
            'updated_by' => 1,
        ]);

        Lookup_workflow_status::create([
            'title'       => 'Send to List',
            'description' => 'Send to a LaSalleCRM email list.',
            'enabled'     => 1,
            'created_at' => new DateTime,
            'created_by' => 1,
            'updated_at' => new DateTime,
            'updated_by' => 1,
        ]);
    }
}