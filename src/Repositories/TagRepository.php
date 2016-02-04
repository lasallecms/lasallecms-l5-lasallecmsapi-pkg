<?php

namespace Lasallecms\Lasallecmsapi\Repositories;

/**
 *
 * Internal API package for the LaSalle Content Management System, based on the Laravel 5 Framework
 * Copyright (C) 2015 - 2016  The South LaSalle Trading Corporation
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
 * @copyright  (c) 2015 - 2016, The South LaSalle Trading Corporation
 * @license    http://www.gnu.org/licenses/gpl-3.0.html
 * @author     The South LaSalle Trading Corporation
 * @email      info@southlasalle.com
 *
 */

// LaSalle Software
use Lasallecms\Lasallecmsapi\Repositories\BaseRepository;
use Lasallecms\Lasallecmsapi\Models\Tag;

// Laravel facades
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TagRepository extends BaseRepository
{
    /*
     * Instance of model
     *
     * @var Lasallecms\Lasallecmsapi\Models\Tag
     */
    protected $model;


    /*
     * Inject the model
     *
     * @param  Lasallecms\Lasallecmsapi\Models\Category
     */
    public function __construct(Tag $model)
    {
        $this->model = $model;
    }


    /**
     * What is the tag's ID for a given tag title?
     *
     * Tag must be enabled!
     *
     * @param   string   $tagTitle
     * @return  collection
     */
    public function findTagIdByTitle($tagTitle)
    {
        return $this->model
            //->where ('title', '=', ucwords($tagTitle))
            ->where ('title', '=', $tagTitle)
            ->where ('enabled', '=', 1)
            ->first()
        ;
    }


    /**
     * Get the tag record from the tag ID
     *
     * Enabled tags only!
     *
     * @param   int   $tagId   The ID of the category
     * @return  int
     */
    public function findTagById($tagId)
    {
        return $this->model
            ->where ('id', '=', $tagId)
            ->where ('enabled', '=', 1)
            ->first()
        ;
    }



    /**
     * Find all the post records associated with a tag
     *
     * ENABLED, PUBLISH_ON <= TODAY, DESC
     *
     * @param   int           $tagId    Tag ID
     * @return  collection
     */
    public function findEnabledAllPostsThatHaveTagId($tagId)
    {
        // Well, it's late, and the exact eloquent relational query that works for categories
        // is not working for tags. All seems well, it really should be working.
        // So, let's just use DB to get the job done.
        //$collection = $this->model->find($tagId)->post->sortByDesc('updated_at');

        $raw = DB::raw('SELECT * FROM posts JOIN post_tag on posts.id = post_tag.post_id WHERE post_tag.tag_id = '.$tagId.' AND  posts.enabled = 1 AND posts.publish_on < CURDATE() ORDER BY updated_at DESC');

        $results = DB::select($raw);

        if ( count($results) == 0 ) {
            return 0;
        }

        return $results;
    }






}