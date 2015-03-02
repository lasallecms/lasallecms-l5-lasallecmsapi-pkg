<?php

/**
 *
 * User Management package for the LaSalle Content Management System, based on the Laravel 5 Framework
 * Copyright (C) 2015  The South LaSalle Trading Corporation
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @package    User Management package for the LaSalle Content Management System
 * @version    1.0.0
 * @link       http://LaSalleCMS.com
 * @copyright  (c) 2015, The South LaSalle Trading Corporation
 * @license    http://www.gnu.org/licenses/gpl-3.0.html
 * @author     The South LaSalle Trading Corporation
 * @email      info@southlasalle.com
 *
 */

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LasallecmsSetupUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
    {
        if (!Schema::hasTable('categories'))
        {
            Schema::create('categories', function (Blueprint $table)
            {
                $table->engine = 'InnoDB';

                $table->increments('id')->unsigned();

                $table->string('title')->unique();
                $table->string('slug')->unique;
                $table->string('description');

                $table->boolean('enabled')->default(true);;

                $table->timestamp('created_at');
                $table->integer('created_by')->unsigned();
                $table->foreign('created_by')->references('id')->on('users');

                $table->timestamp('updated_at');
                $table->integer('updated_by')->unsigned();
                $table->foreign('updated_by')->references('id')->on('users');

                $table->timestamp('locked_at')->nullable();
                $table->integer('locked_by')->nullable()->unsigned();
                $table->foreign('locked_by')->references('id')->on('users');
            });
        }


        if (!Schema::hasTable('tags'))
        {
            Schema::create('tags', function (Blueprint $table)
            {
                $table->engine = 'InnoDB';

                $table->increments('id')->unsigned();

                $table->string('title')->unique();
                $table->string('slug')->unique;
                $table->string('description');

                $table->boolean('enabled')->default(true);;

                $table->timestamp('created_at');
                $table->integer('created_by')->unsigned();
                $table->foreign('created_by')->references('id')->on('users');

                $table->timestamp('updated_at');
                $table->integer('updated_by')->unsigned();
                $table->foreign('updated_by')->references('id')->on('users');

                $table->timestamp('locked_at')->nullable();
                $table->integer('locked_by')->nullable()->unsigned();
                $table->foreign('locked_by')->references('id')->on('users');
            });
        }




        if (!Schema::hasTable('posts'))
        {
            Schema::create('posts', function (Blueprint $table)
            {
                $table->engine = 'InnoDB';

                $table->increments('id')->unsigned();

                $table->integer('user_id')->unsigned();
                // Yes, just one category per post
                $table->integer('category_id')->unsigned();

                $table->string('title');
                $table->string('slug')->unique();
                $table->text('content');
                $table->text('excerpt');
                $table->string('meta_title');
                $table->string('meta_description');
                $table->string('meta_keywords');
                $table->boolean('publish');
                $table->string('featured_image');


                $table->boolean('enabled')->default(true);

                $table->timestamp('created_at');
                $table->integer('created_by')->unsigned();
                $table->foreign('created_by')->references('id')->on('users');

                $table->timestamp('updated_at');
                $table->integer('updated_by')->unsigned();
                $table->foreign('updated_by')->references('id')->on('users');

                $table->timestamp('locked_at')->nullable();
                $table->integer('locked_by')->nullable()->unsigned();
                $table->foreign('locked_by')->references('id')->on('users');
            });
        }


        if (!Schema::hasTable('user_groups'))
        {
            Schema::create('user_groups', function (Blueprint $table)
            {
                $table->engine = 'InnoDB';

                $table->increments('id')->unsigned();

                $table->integer('user_id')->unsigned()->index();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->integer('group_id')->unsigned()->index();
                $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            });
        }


    }

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::dropIfExists('categories');
        Schema::table('categories', function($table){
            $table->drop_index('categories_title_unique');
            $table->drop_index('categories_slug_unique');
        });

        Schema::dropIfExists('tags');
        Schema::table('tags', function($table){
            $table->drop_index('tags_title_unique');
            $table->drop_index('tags_slug_unique');
        });


        Schema::dropIfExists('posts');
        Schema::table('posts', function($table){
            $table->drop_index('posts_user_id_foreign');
            $table->drop_index('posts_category_id_foreign');
            $table->drop_foreign('posts_user_id_foreign');
            $table->drop_foreign('posts_category_id_foreign');
        });




        Schema::dropIfExists('users');
        Schema::table('users', function($table){
            $table->drop_index('users_email_unique');
        });
	}


}
