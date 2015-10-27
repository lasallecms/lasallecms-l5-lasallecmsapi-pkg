<?php

namespace Lasallecms\Lasallecmsapi\Repositories;

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

// LaSalle Software
use Lasallecms\Lasallecmsapi\Repositories\BaseRepository;
use Lasallecms\Lasallecmsapi\Models\Post;
use Lasallecms\Helpers\Dates\DatesHelper;

// Laravel facades
use Illuminate\Support\Facades\DB;

class PostRepository extends BaseRepository
{
    /**
     * Instance of model
     *
     * @var Lasallecms\Lasallecmsapi\Models\Post
     */
    protected $model;


    /**
     * Inject the model
     *
     * @param  Lasallecms\Lasallecmsapi\Models\Category
     */
    public function __construct(Post $model)
    {
        $this->model = $model;
    }


    /**
     * Get all the posts
     *
     * ENABLED, PUBLISH_ON <= TODAY, DESC
     *
     * @return  collection
     */
    public function getAllPublishablePosts()
    {
        $collection = $this->getAll()->sortByDesc('updated_at');

        $filtered = $collection->filter(function ($item) {
            if ($item->enabled == 1) return true;
        });

        $filtered = $filtered->filter(function ($item) {
            $todaysDate = DatesHelper::todaysDateSetToLocalTime();
            if ($item->publish_on <= $todaysDate) return true;
        });

        // TODO: HAVE TO MANUALLY PAGINATE THE FILTERED COLLECTION :-(
        // https://laracasts.com/discuss/channels/laravel/laravel-pagination-not-working-with-array-instead-of-collection?page=1#reply-63860
        // http://www.reddit.com/r/laravel/comments/32kxn8/creating_a_paginator_manually/
        return $filtered->all();
    }


    /**
     * Find all the post records associated with a category
     *
     * ENABLED, PUBLISH_ON <= TODAY, DESC
     *
     * @param   $numberOfPostsToTake    The number of post records to select from the db
     * @return  collection
     */
    public function getSomePublishablePosts($numberOfPostsToTake)
    {
        $collection = $this->getAll();

        $filtered = $collection->filter(function ($item) {
            if ($item->enabled == 1) return true;
        });

        $filtered = $filtered->filter(function ($item) {
            $todaysDate = DatesHelper::todaysDateSetToLocalTime();
            if ($item->publish_on <= $todaysDate) return true;
        });

        return $filtered
            ->sortByDesc('updated_at')
            ->take($numberOfPostsToTake)
        ;
    }
}