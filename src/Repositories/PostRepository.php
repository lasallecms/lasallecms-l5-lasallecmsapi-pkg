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


    /**
     * Find the categories belonging to a post
     *
     * @param $postId  The post's ID
     * @return array
     */
    public function findCategoryForPostById($postId)
    {
        return $users = DB::table('post_category')
            ->where('post_id', '=', $postId)
            ->get()
        ;
    }



    /**
     * For single post display navigation: get the next post
     *
     * @param  int         $categoryId     The category id.
     * @param  date        $publish_on     The displayed post's publish_on date
     * @return stdClass Object
     */
    public function getNextPost($categoryId, $publish_on)
    {
        return DB::table('posts')
            ->join('post_category', 'posts.id', '=', 'post_category.post_id')
            ->select('posts.slug', 'posts.title')
            ->where('post_category.category_id', '=', $categoryId)
            ->where('posts.enabled', '=', 1)
            ->where('posts.publish_on', '>', $publish_on)
            ->orderBy('publish_on', 'asc')
            ->first()
        ;
    }

    /**
     * For single post display navigation: get the previous post
     *
     * @param  int         $categoryId     The category id.
     * @param  date        $publish_on     The displayed post's publish_on date
     * @return stdClass Object
     */
    public function getPreviousPost($categoryId, $publish_on)
    {
        return DB::table('posts')
            ->join('post_category', 'posts.id', '=', 'post_category.post_id')
            ->select('posts.slug', 'posts.title')
            ->where('post_category.category_id', '=', $categoryId)
            ->where('posts.enabled', '=', 1)
            ->where('posts.publish_on', '<', $publish_on)
            ->orderBy('publish_on', 'desc')
            ->first()
        ;
    }




    /**
     * Find the category title by the category's id
     *
     * I have this little function here because I do not want to inject the category's repository.
     *
     * The purpose of having the category's title is to build a URL to all posts with that category.
     *
     * @param  int     $categoryId   The category's ID
     * @return string
     */
    public function getCategoryTitleById($categoryId)
    {
        $category =  DB::table('categories')
            ->where('id', '=', $categoryId)
            ->first()
        ;

        return $category->title;
    }


    /**
     * Find the tag's titles for those tags that are associated with a post.
     *
     * I have this little function here because I do not want to inject the tag's repository.
     *
     * @param   int   $postId   The post's ID
     * @return  array
     */
    public function getTagTitlesByPostId($postId)
    {
        // Grab the tag ID's from the post_tag table
        $post_tags = DB::table('post_tag')
            ->where('post_id', '=', $postId)
            ->get()
        ;

        // if there are no tags associated with this post, then return emptiness
        if (empty($post_tags)) {
            return $post_tags;
        }


        // ah, there are tags associated with the post

        // initialize the array that will be returned
        $tagTitles = [];

        // go through each tag_id and grab the tag's title
        foreach ($post_tags as $post_tag) {

            $tag = DB::table('tags')
                ->select('title')
                ->where('id', '=', $post_tag->tag_id)
                ->first();

            $tagTitles[] = $tag->title;
        }

        return $tagTitles;
    }
}