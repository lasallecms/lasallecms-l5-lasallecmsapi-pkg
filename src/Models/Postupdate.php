<?php namespace
Lasallecms\Lasallecmsapi\Models;

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

class Postupdate extends BaseModel
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
    public $table = "postupdates";

    /**
     * Which fields may be mass assigned
     * @var array
     */
    protected $fillable = [
        'title', 'content', 'excerpt', 'post_id', 'enabled', 'publish_on'
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
    public $model_class = "Postupdate";


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
    public $resource_route_name   = "postupdates";


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
    public $namespace_formprocessor = 'Lasallecms\Lasallecmsapi\Postupdates';

    /*
     * Class name of the CREATE Form Processor command
     *
     * @var string
     */
    public $classname_formprocessor_create = 'CreatePostupdateFormProcessing';

    /*
     * Namespace and class name of the UPDATE Form Processor command
     *
     * @var string
     */
    public $classname_formprocessor_update = 'UpdatePostupdateFormProcessing';

    /*
     * Namespace and class name of the DELETE (DESTROY) Form Processor command
     *
     * @var string
     */
    public $classname_formprocessor_delete = 'DeletePostupdateFormProcessing';


    // SANITATION RULES PROPERTIES

    /**
     * Sanitation rules for Create (INSERT)
     *
     * @var array
     */
    public $sanitationRulesForCreate = [
        'title'       => 'trim|strip_tags',
        'content'     => 'trim',
        'excerpt'     => 'trim|strip_tags',
    ];

    /**
     * Sanitation rules for UPDATE
     *
     * @var array
     */
    public $sanitationRulesForUpdate = [
        'title'       => 'trim|strip_tags',
        'content'     => 'trim',
        'excerpt'     => 'trim|strip_tags',
    ];


    // VALIDATION RULES PROPERTIES

    /**
     * Validation rules for Create (INSERT)
     *
     * @var array
     */
    public $validationRulesForCreate = [
        'title'       => 'required|min:4',
        'content'     => 'required|min:4',
    ];

    /**
     * Validation rules for UPDATE
     *
     * @var array
     */
    public $validationRulesForUpdate = [
        'title'       => 'required|min:4',
        'content'     => 'required|min:4',
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
            'name'                  => 'id',
            'type'                  => 'int',
            'info'                  => false,
            'index_skip'            => false,
            'index_align'           => 'center',
        ],
        [
            'name'                  => 'post_id',
            'type'                  => 'related_table',
            'related_table_name'    => 'posts',
            'related_namespace'     => 'Lasallecms\Lasallecmsapi\Models',
            'related_model_class'   => 'Post',
            'related_fk_constraint' => false,
            'related_pivot_table'   => false,
            'nullable'              => false,
            'info'                  => false,
            'index_skip'            => false,
            'index_align'           => 'left',
        ],
        [
            'name'                   => 'title',
            'type'                   => 'varchar',
            'info'                   => false,
            'index_skip'             => false,
            'index_align'            => 'center',
            'persist_wash'           => 'title',
        ],
        [
            'name'                   => 'content',
            'type'                   => 'text-with-editor',
            'info'                   => false,
            'index_skip'             => true,
            'persist_wash'           => 'content',
        ],
        [
            'name'                   => 'excerpt',
            'type'                   => 'text-no-editor',
            'info'                   => "The update excerpt is displayed in a list of excerpts for the post. Generally, the content is displayed in the actual post, not the excerpt.",
            'index_skip'             => false,
            'index_align'            => 'left',
        ],
        [
            'name'                   => 'enabled',
            'type'                   => 'boolean',
            'info'                   => false,
            'index_skip'             => false,
            'index_align'            => 'center',
            'persist_wash'           => 'enabled',
        ],
        [
            'name'                   => 'publish_on',
            'type'                   => 'date',
            'info'                   => "When do you want this update to display?",
            'index_skip'             => false,
            'index_align'            => 'center',
            'persist_wash'           => 'publish_on',
        ],
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
     * One to one relationship with post table
     *
     * @return Eloquent
     */
    public function post()
    {
        return $this->belongsTo('Lasallecms\Lasallecmsapi\Models\Post');
    }
}