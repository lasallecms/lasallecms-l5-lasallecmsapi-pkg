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

/*
 * TAGS IS A LOOKUP TABLE!
 */

// LaSalle Software
use Lasallecms\Lasallecmsapi\Models\BaseModel;

// Laravel facades
use Illuminate\Support\Facades\DB;

class Tag extends BaseModel
{
    ///////////////////////////////////////////////////////////////////
    //////////////          PROPERTIES              ///////////////////
    ///////////////////////////////////////////////////////////////////

    /**
     * The database table used by the model.
     *
     * @var string
     */
    public $table = 'tags';

    /**
     * Which fields may be mass assigned
     * @var array
     */
    protected $fillable = [
        'title', 'description', 'enabled'
    ];

    /*
     * User groups that are allowed to execute each controller action
     */
    public $allowed_user_groups = [
        ['index'   => ['Super Administrator']],
        ['create'  => ['Super Administrator']],
        ['store'   => ['Super Administrator']],
        ['edit'    => ['Super Administrator']],
        ['update'  => ['Super Administrator']],
        ['destroy' => ['Super Administrator']],
    ];



    ///////////////////////////////////////////////////////////////////
    //////////////        RELATIONSHIPS             ///////////////////
    ///////////////////////////////////////////////////////////////////

    /*
     * Many tags per single post
     *
     *  Method name must be the model name, *not* the table name
     *
     * @return Eloquent
     */
    public function posts()
    {
        return $this->belongsToMany('Lasallecms\Lasallecmsapi\Models\Post', 'post_tag');
    }



    ///////////////////////////////////////////////////////////////////
    ////////////        FOREIGN KEY CONSTRAINTS       /////////////////
    ///////////////////////////////////////////////////////////////////

    /*
     * Return an array of all the tables using a specified lookup table id.
     * The array is in the form ['table related to the lookup table' => 'count']
     *
     * @param   int   $id   Table ID
     * @return  array
     */
    public function foreignKeyCheck($id)
    {
        // 'related_table' is the table name
        return  [
            [ 'related_table' => 'posts', 'count' => $this->postsCount($id) ],
        ];
    }

    /*
     * Count of related table using lookup table.
     *
     * Method name is the table name (no techie reason, just a convention to adopt)
     *
     * @return int
     */
    public function postsCount($id)
    {
        // I know eloquent does this, but having trouble so hand crafting using DB
        // Note the use of a pivot table due tot he "many" relationship
        $record =  DB::table('post_tag')->where('tag_id', '=', $id)->get();
        return count($record);
    }
}