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
use Lasallecms\Lasallecmsapi\Models\Category;
use Lasallecms\Helpers\Dates\DatesHelper;

// Laravel facades
use Illuminate\Support\Facades\DB;

class CategoryRepository extends BaseRepository
{
    /**
     * Instance of model
     *
     * @var Lasallecms\Lasallecmsapi\Models\Category
     */
    protected $model;


    /**
     * Inject the model
     *
     * @param  Lasallecms\Lasallecmsapi\Models\Category
     */
    public function __construct(Category $model)
    {
        $this->model = $model;
    }


    /**
     * Display all the categories in the admin listing
     *
     * @return collection
     */
    public function allCategoriesForDisplayOnAdminListing()
    {
        return $this->model->orderBy('title', 'ASC')->get();
    }


    /**
     * What is the category's ID for a given category slug?
     *
     * Category must be enabled!
     *
     * @param   string   $categoryTitle
     * @return  collection
     */
    public function findCategoryIdByTitle($categoryTitle)
    {
        return $this->model
            //->where ('title', '=', ucwords($categoryTitle))
            ->where ('title', '=', $categoryTitle)
            ->where ('enabled', '=', 1)
            ->first()
            ;
    }


    /**
     * Get the category record from the category ID
     *
     * Enabled categories only!
     *
     * @param   int   $categoryId   The ID of the category
     * @return  int
     */
    public function findCategoryById($categoryId)
    {
        return $this->model
            ->where ('id', '=', $categoryId)
            ->where ('enabled', '=', 1)
            ->first()
            ;
    }


    /**
     * Find all the post records associated with a category
     *
     * @param  int   $catId
     * @return int
     */
    public function countAllPostsThatHaveCategoryId($catId)
    {
        return count($this->model->find($catId)->post);
    }


    /**
     * Find all the post records associated with a category
     *
     * ENABLED, PUBLISH_ON <= TODAY, DESC
     *
     * @param   int           $catId
     * @return  collection
     */
    public function findEnabledAllPostsThatHaveCategoryId($catId)
    {
        $collection = $this->model->find($catId)->post->sortByDesc('updated_at');

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
     * @param id  $id
     * @return array
     */
    public function foreignKeyConstraintTest($id)
    {
        // How many posts is this category associated with?
        $results = DB::table('post_category')->where('category_id', '=', $id)->get();

        if ( (count($results)) == 0 || (empty($results)) )
        {
            $post_category = 0;
        } else {
            $post_category = count($results);
        }

        // How many times is this category a parent category?
        $results = DB::table('categories')->where('parent_id', '=', $id)->get();

        if ( (count($results)) == 0 || (empty($results)) )
        {
            $parent_id = 0;
        } else {
            $parent_id  = count($results);
        }

        //return ['post_category' => $post_category, 'parent_id' => $parent_id];

        if ( ($post_category > 0) || ($parent_id > 0)  ) return false;
        return true;
    }
}