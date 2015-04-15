<?php namespace Lasallecms\Lasallecmsapi\Models;

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

use Lasallecms\Lasallecmsapi\Models\BaseModel;

use Carbon\Carbon;
use Illuminate\Support\Facades\Url;


class Post extends BaseModel {


    /**
     * The database table used by the model.
     *
     * Want this for my slug method(s), instead of passing table as param
     *
     * @var string
     */
    public $table = 'posts';


    /**
     * Which fields may be mass assigned
     * @var array
     */
    protected $fillable = [
        'title', 'slug', 'content', 'excerpt', 'meta_description', 'enabled', 'featured_image'
    ];

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


    /**
     * Validation rules for  Create (INSERT)
     *
     * NOTE: content field has 7 chars when blank!
     *
     * @var array
     */
    public $validationRulesForCreate = [
        'title'            => 'required|min:4',
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
        'content'          => 'required|min:11',
    ];


    /*
    * Many to many relationship with categories
    *
    * @return Eloquent
    */
    public function category()
    {
        return $this->belongsToMany('Lasallecms\Lasallecmsapi\Models\Category', 'post_category');
    }

    /*
     * Many to many relationship with tags
     *
     * @return Eloquent
     */
    public function tag()
    {
        return $this->belongsToMany('Lasallecms\Lasallecmsapi\Models\Tag', 'post_tag');
    }

    /*
     * One to one relationship with user_id
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
     * @return Eloquent
     */
    public function postupdate()
    {
        return $this->hasMany('Lasallecms\Lasallecmsapi\Models\Postupdate');
    }


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