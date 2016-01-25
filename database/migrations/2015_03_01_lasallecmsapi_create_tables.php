<?php

/**
 *
 * Internal API package for the LaSalle Content Management System, based on the Laravel 5 Framework
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
 * @package    Internal API package for the LaSalle Content Management System

 * @link       http://LaSalleCMS.com
 * @copyright  (c) 2015, The South LaSalle Trading Corporation
 * @license    http://www.gnu.org/licenses/gpl-3.0.html
 * @author     The South LaSalle Trading Corporation
 * @email      info@southlasalle.com
 *
 */

// Laravel classes
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateTables
 */
class CreateTables extends Migration {

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

                $table->integer('parent_id')->unsigned()->default(0);

                $table->string('title')->unique();
                $table->text('content');
                $table->string('description');
                $table->string('featured_image');

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


        // TAGS is a lookup table, although the table name is not prefixed with "lookup_"
        if (!Schema::hasTable('tags'))
        {
            Schema::create('tags', function (Blueprint $table)
            {
                $table->engine = 'InnoDB';

                $table->increments('id')->unsigned();

                $table->string('title')->unique();
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

                $table->string('title');
                $table->string('slug')->unique();
                $table->text('content');
                $table->text('excerpt');
                $table->string('meta_description');
                $table->text('canonical_url');
                $table->string('featured_image');

                $table->boolean('enabled')->default(true);

                $table->boolean('postupdate')->default(false);

                $table->date('publish_on');

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


        if (!Schema::hasTable('post_category'))
        {
            Schema::create('post_category', function (Blueprint $table)
            {
                $table->engine = 'InnoDB';

                $table->increments('id')->unsigned();

                $table->integer('post_id')->unsigned()->index();
                $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
                $table->integer('category_id')->unsigned()->index();
                $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            });
        }


        if (!Schema::hasTable('post_tag'))
        {
            Schema::create('post_tag', function (Blueprint $table)
            {
                $table->engine = 'InnoDB';

                $table->increments('id')->unsigned();

                $table->integer('post_id')->unsigned()->index();
                $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
                $table->integer('tag_id')->unsigned()->index();
                $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
            });
        }


        if (!Schema::hasTable('postupdates'))
        {
            Schema::create('postupdates', function (Blueprint $table)
            {
                $table->engine = 'InnoDB';

                $table->increments('id')->unsigned();

                $table->integer('post_id')->unsigned();
                $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');

                $table->string('title')->unique();
                $table->text('content');
                $table->text('excerpt');

                $table->boolean('enabled')->default(true);

                $table->date('publish_on');

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


    }

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        // Disable foreign key constraints or these DROPs will not work
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');


		Schema::table('post_tag', function($table){
		    $table->dropIndex('post_tag_post_id_index');
		    $table->dropForeign('post_tag_post_id_foreign');
		    $table->dropIndex('post_tag_tag_id_index');
		    $table->dropForeign('post_tag_tag_id_foreign');
		});
		Schema::dropIfExists('post_tag');


		Schema::table('post_category', function($table){
		    $table->dropIndex('post_category_post_id_index');
		    $table->dropForeign('post_category_post_id_foreign');
		    $table->dropIndex('post_category_category_id_index');
		    $table->dropForeign('post_category_category_id_foreign');
		});
        Schema::dropIfExists('post_category');


		Schema::table('categories', function($table){
		    $table->dropIndex('categories_title_unique');
		    $table->dropForeign('categories_created_by_foreign');
		    $table->dropForeign('categories_updated_by_foreign');
		    $table->dropForeign('categories_locked_by_foreign');
		});
        Schema::dropIfExists('categories');


		Schema::table('tags', function($table){
		    $table->dropIndex('tags_title_unique');
		    $table->dropForeign('tags_created_by_foreign');
		    $table->dropForeign('tags_updated_by_foreign');
		    $table->dropForeign('tags_locked_by_foreign');
		});
        Schema::dropIfExists('tags');


		Schema::table('posts', function($table){
		    $table->dropIndex('posts_slug_unique');
		    $table->dropForeign('posts_created_by_foreign');
		    $table->dropForeign('posts_updated_by_foreign');
		    $table->dropForeign('posts_locked_by_foreign');
		});
        Schema::dropIfExists('posts');


		Schema::table('postupdates', function($table){
		    $table->dropForeign('postupdates_post_id_foreign');
		    $table->dropForeign('postupdates_created_by_foreign');
		    $table->dropForeign('postupdates_updated_by_foreign');
		    $table->dropForeign('postupdates_locked_by_foreign');
		});
        Schema::dropIfExists('postupdates');

        // Enable foreign key constraints
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
	}
}
