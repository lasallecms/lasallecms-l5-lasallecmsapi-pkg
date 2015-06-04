<?php
namespace Lasallecms\Lasallecmsapi\Models;

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
 * @version    1.0.0
 * @link       http://LaSalleCMS.com
 * @copyright  (c) 2015, The South LaSalle Trading Corporation
 * @license    http://www.gnu.org/licenses/gpl-3.0.html
 * @author     The South LaSalle Trading Corporation
 * @email      info@southlasalle.com
 *
 */

// LaSalle Software
use Lasallecms\Lasallecmsapi\Models\BaseModel;

// Laravel facades
use Illuminate\Support\Facades\Url;

// Third party classes
use Carbon\Carbon;


class Post extends BaseModel
{
    ///////////////////////////////////////////////////////////////////
    ///////////     MANDATORY USER DEFINED PROPERTIES      ////////////
    ///////////              MODIFY THESE!                /////////////
    ///////////////////////////////////////////////////////////////////


    // LARAVEL MODEL CLASS PROPERTIES

    /**
     * The database table used by the model.
     *
     * The convention is plural -- and plural is assumed.
     *
     * Lowercase.
     *
     * @var string
     */
    public $table = "posts";

    /**
     * Which fields may be mass assigned
     * @var array
     */
    protected $fillable = [
        'title', 'slug', 'content', 'excerpt', 'meta_description', 'enabled', 'featured_image', 'publish_on'
    ];


    // PACKAGE PROPERTIES

    /*
     * Name of this package
     *
     * @var string
     */
    public $package_title = "LaSalleCMS";


    // MODEL PROPERTIES

    /*
     * Model class namespace.
     *
     * Do *NOT* specify the model's class.
     *
     * @var string
     */
    public $model_namespace = "Lasallecms\Lasallecmsapi\Models";

    /*
     * Model's class.
     *
     * Convention is capitalized and singular -- which is assumed.
     *
     * @var string
     */
    public $model_class = "Post";


    // RESOURCE ROUTE PROPERTIES

    /*
     * The base URL of the resource routes.
     *
     * Frequently is the same as the table name.
     *
     * By convention, plural.
     *
     * Lowercase.
     *
     * @var string
     */
    public $resource_route_name   = "posts";


    // FORM PROCESSORS PROPERTIES.
    // THESE ARE THE ADMIN CRUD COMMAND HANDLERS.
    // THE ONLY REASON YOU HAVE TO CREATE THESE COMMAND HANDLERS AT ALL IS THAT
    // THE EVENTS DIFFER. EVERYTHING THAT HAPPENS UP TO THE "PERSIST" IS PRETTY STANDARD.

    /*
     * Namespace of the Form Processors
     *
     * MUST *NOT* have a slash at the end of the string
     *
     * @var string
     */
    public $namespace_formprocessor = 'Lasallecms\Lasallecmsapi\Posts';

    /*
     * Class name of the CREATE Form Processor command
     *
     * @var string
     */
    public $classname_formprocessor_create = 'CreatePostFormProcessing';

    /*
     * Namespace and class name of the UPDATE Form Processor command
     *
     * @var string
     */
    public $classname_formprocessor_update = 'UpdatePostFormProcessing';

    /*
     * Namespace and class name of the DELETE (DESTROY) Form Processor command
     *
     * @var string
     */
    public $classname_formprocessor_delete = 'DeletePostFormProcessing';


    // SANITATION RULES PROPERTIES

    /**
     * Sanitation rules for Create (INSERT)
     *
     * @var array
     */
    public $sanitationRulesForCreate = [
        'title'            => 'trim|strip_tags',
        'slug'             => 'trim',
        'canonical_url'    => 'trim',
        'content'          => 'trim',
        'excerpt'          => 'trim|strip_tags',
        'meta_description' => 'trim',
        'featured_image'   => 'trim',
    ];

    /**
     * Sanitation rules for UPDATE
     *
     * @var array
     */
    public $sanitationRulesForUpdate = [
        'title'            => 'trim|strip_tags',
        'slug'             => 'trim',
        'canonical_url'    => 'trim',
        'content'          => 'trim',
        'excerpt'          => 'trim|strip_tags',
        'meta_description' => 'trim',
        'featured_image'   => 'trim',
    ];


    // VALIDATION RULES PROPERTIES

    /**
     * Validation rules for  Create (INSERT)
     *
     * NOTE: content field has 7 chars when blank!
     *
     * @var array
     */
    public $validationRulesForCreate = [
        'title'            => 'required|min:4',
        'categories'       => 'required',
        'content'          => 'required|min:11',
    ];

    /**
     * Validation rules for UPDATE
     *
     * NOTE: content field has 7 chars when blank!
     *
     * @var array
     */
    public $validationRulesForUpdate = [
        'title'            => 'required|min:4',
        'categories'       => 'required',
        'content'          => 'required|min:11',
    ];


    // USER GROUPS & ROLES PROPERTIES

    /*
     * User groups that are allowed to execute each controller action
     *
     * @var array
     */
    public $allowed_user_groups = [
        ['index'   => ['Super Administrator']],
        ['create'  => ['Super Administrator']],
        ['store'   => ['Super Administrator']],
        ['edit'    => ['Super Administrator']],
        ['update'  => ['Super Administrator']],
        ['destroy' => ['Super Administrator']],
    ];


    // FIELD LIST PROPERTIES

    /*
     * Field list
     *
     * ID and TITLE must go first.
     *
     * Forms will list fields in the order fields are listed in this array.
     *
     * @var array
     */
    public $field_list = [
        [
            'name'                 => 'id',
            'type'                 => 'int',
            'info'                 => false,
            'index_skip'           => false,
            'index_align'          => 'center',
        ],
        [
            'name'                  => 'title',
            'type'                  => 'varchar',
            'info'                  => false,
            'index_skip'            => false,
            'index_align'           => 'center',
            'persist_wash'          => 'title',
        ],
        [
            'name'                  => 'slug',
            'type'                  => 'varchar',
            'info'                  => 'No spaces! A unique slug will be generated automatically when left blank.',
            'index_skip'            => true,
        ],
        [
            'name'                  => 'content',
            'type'                  => 'text-with-editor',
            'info'                  => false,
            'index_skip'            => true,
            'persist_wash'          => 'content',
        ],
        [
            'name'                  => 'excerpt',
            'type'                  => 'text-no-editor',
            'info'                  => "Teaser text displayed on your site's post listing. You can leave blank, or hand-craft your excerpt. Note the config settings for excerpts.",
            'index_skip'            => false,
            'index_align'           => 'left',
        ],
        [
            'name'                  => 'meta_description',
            'type'                  => 'varchar',
            'info'                  => 'This is the blurb that displays in Google search results. Excerpt is used when left blank.',
            'index_skip'            => true,
        ],
        [
            'name'                  => 'canonical_url',
            'type'                  => 'varchar',
            'info'                  => 'Preferred URL for search engines. Auto created when blank.',
            'index_skip'            => true,
        ],
        [
            'name'                  => 'featured_image',
            'type'                  => 'varchar',
            'info'                  => 'The one single image that represents this post, displayed in lists, and at top of the post.',
            'index_skip'            => true,
        ],
        [
            'name'                  => 'enabled',
            'type'                  => 'boolean',
            'info'                  => false,
            'index_skip'            => false,
            'index_align'           => 'center',
            'persist_wash'          => 'enabled',
        ],
        [
            'name'                  => 'publish_on',
            'type'                  => 'date',
            'info'                  => false,
            'index_skip'            => false,
            'index_align'           => 'center',
            'persist_wash'          => 'publish_on',
        ],
        [
            'name'                  => 'categories',
            'type'                  => 'related_table',
            'related_table_name'    => 'categories',
            'related_namespace'     => 'Lasallecms\Lasallecmsapi\Models',
            'related_model_class'   => 'Category',
            'related_fk_constraint' => false,
            'related_pivot_table'   => true,
            'nullable'              => false,
            'info'                  => 'LaSalleCMS uses categories to group posts in the front-end.',
            'index_skip'            => false,
            'index_align'           => 'center',
        ],
        [
            'name'                  => 'tags',
            'type'                  => 'related_table',
            'related_table_name'    => 'tags',
            'related_namespace'     => 'Lasallecms\Lasallecmsapi\Models',
            'related_model_class'   => 'Tag',
            'related_fk_constraint' => false,
            'related_pivot_table'   => true,
            'nullable'              => true,
            'info'                  => false,
            'index_skip'            => true,
        ]
    ];


    // MISC PROPERTIES

    /*
     * Suppress the delete button when just one record to list, in the listings (index) page
     *
     * true  = suppress the delete button when just one record to list
     * false = display the delete button when just one record to list
     *
     * @var bool
     */
    public $suppress_delete_button_when_one_record = false;


    /*
     * DO NOT DELETE THESE CORE RECORDS.
     *
     * Specify the TITLE of these records
     *
     * Assumed that this database table has a "title" field
     *
     * @var array
     */
    public $do_not_delete_these_core_records = [];



    ///////////////////////////////////////////////////////////////////
    //////////////        RELATIONSHIPS             ///////////////////
    ///////////////////////////////////////////////////////////////////

    /*
    * Many to many relationship with categories.
    *
    * Method name must be:
    *    * the model name,
    *    * NOT the table name,
    *    * singular;
    *    * lowercase.
    *
    * @return Eloquent
    */
    public function category()
    {
        return $this->belongsToMany('Lasallecms\Lasallecmsapi\Models\Category', 'post_category');
    }

    /*
     * Many to many relationship with tags.
     *
     * Method name must be:
     *    * the model name,
     *    * NOT the table name,
     *    * singular;
     *    * lowercase.
     *
     * @return Eloquent
     */
    public function tag()
    {
        return $this->belongsToMany('Lasallecms\Lasallecmsapi\Models\Tag', 'post_tag');
    }

    /*
     * One to one relationship with user_id.
     *
     * Method name must be:
     *    * the model name,
     *    * NOT the table name,
     *    * singular;
     *    * lowercase.
     *
     * @return Eloquent
     */
    public function user()
    {
        return $this->belongsTo('Lasallecms\Lasallecmsapi\Models\User');
    }

    /*
     * One to many relationship with postupdate_id
     *
     * Method name must be:
     *    * the model name,
     *    * NOT the table name,
     *    * singular;
     *    * lowercase.
     *
     * @return Eloquent
     */
    public function postupdate()
    {
        return $this->hasMany('Lasallecms\Lasallecmsapi\Models\Postupdate');
    }



    ///////////////////////////////////////////////////////////////////
    //////////////        OTHER METHODS             ///////////////////
    ///////////////////////////////////////////////////////////////////

    /**
     * Returns a formatted post content entry,
     * this ensures that line breaks are returned.
     *
     * @return string
     */
    public function content()
    {
        return nl2br($this->content);
    }

    /**
     * Get the date the post was created.
     *
     * @param \Carbon|null $date
     * @return string
     */
    public function date($date=null)
    {
        if(is_null($date)) {
            $date = $this->created_at;
        }
        return String::date($date);
    }

    /**
     * Get the URL to the post.
     *
     * @return string
     */
    public function url()
    {
        return Url::to($this->slug);
    }
}