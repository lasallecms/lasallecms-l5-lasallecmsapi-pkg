<?php namespace Lasallecms\Lasallecmsapi\Repositories;

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

use Lasallecms\Lasallecmsapi\Contracts\PostRepository;

use Lasallecms\Lasallecmsapi\Contracts\CategoryRepository;
use Lasallecms\Lasallecmsapi\Contracts\TagRepository;
use Lasallecms\Lasallecmsapi\Models\Post;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


/*
 * Eloquent implementation of the Post repository
 */
class PostEloquent extends BaseEloquent implements PostRepository {


    /*
     * Instance of model
     *
     * @var Lasallecms\Lasallecmsapi\Models\Post
     */
    protected $model;

    /*
     * Instance of Category Repository
     *
     * @var Lasallecms\Lasallecmsapi\Contracts\CategoryRepository
     */
    protected $categoryRepository;

    /*
     * Instance of Tag Repository
     *
     * @var Lasallecms\Lasallecmsapi\Contracts\TagRepository
     */
    protected $tagRepository;


    /*
     * Inject the model
     *
     * @param  Lasallecms\Lasallecmsapi\Models\Post
     * @param  Lasallecms\Lasallecmsapi\Contracts\CategoryRepository
     * @param  Lasallecms\Lasallecmsapi\Contracts\TagRepository
     */
    public function __construct(Post $model, CategoryRepository $categoryRepository, TagRepository $tagRepository)
    {
        $this->model              = $model;
        $this->categoryRepository = $categoryRepository;
        $this->tagRepository      = $tagRepository;
    }


    /*
     * Display all the Posts in the admin listing
     *
     * @return collection
     */
    public function allPostsForDisplayOnAdminListing()
    {
        return $this->model->orderBy('publish_on', 'title', 'DESC')->get();
    }


    /*
     * Find all the post records associated with a Post
     *
     * @param id  $id
     * @return int
     */
    public function countAllPostsThatHavePostId($id)
    {
        $Post = $this->getFind($id);
        $PostsWithPosts = $Post->posts;
        return count($PostsWithPosts);
    }


    /*
     * Find all Categories for a Post ID
     *
     * @param  int                  $id
     * @param  sortBy               $sortBy  Sort by this column in ASC order
     * @return eloquent collection
     */
    public function findCategoriesByPostId($id, $sortBy = "title")
    {
        return $this->model->find($id)->category->sortBy($sortBy);
    }

    /*
     * Find all Tags for a Post ID
     *
     * @param  int                  $id
     * @param  sortBy               $sortBy  Sort by this column in ASC order
     * @return eloquent collection
     */
    public function findTagsByPostId($id, $sortBy = "title")
    {
        return $this->model->find($id)->tag->sortBy($sortBy);
    }


    ///////////////////////////////////////////////////////////////////
    ///////////////////////////    PERSIST    /////////////////////////
    ///////////////////////////////////////////////////////////////////

    /*
     * Prepare input data for save
     *
     * Basically ignoring the sanitizing that has already been applied, in the interests
     * of being thorough
     *
     * @param  array   $data  The sanitized input data array
     * @return array
     */
    public function preparePostForPersist($data)
    {
        $data['title']            = $this->prepareTitleForPersist($data['title']);
        $data['slug']             = $this->prepareSlugForPersist($data['title'], $data['slug']);
        $data['canonical_url']    = $this->prepareCanonicalURLForPersist($data['slug']);
        $data['content']          = $this->prepareContentForPersist($data['content']);
        $data['excerpt']          = $this->prepareExcerptForPersist($data['excerpt'], $data['content']);
        $data['meta_description'] = $this->prepareMetaDescriptionForPersist($data['meta_description'], $data['excerpt']);
        $data['featured_image']   = $this->prepareFeaturedImageForPersist($data['featured_image']);
        $data['enabled']          = $this->prepareEnabledForPersist($data['enabled']);
        $data['publish_on']       = $this->preparePublishOnForPersist($data['publish_on']);

        $data['created_at']       = Carbon::now();
        $data['created_by']       = Auth::user()->id;

        $data['updated_at']       = Carbon::now();
        $data['updated_by']       = Auth::user()->id;

        return $data;
    }






    /*
     * Create (INSERT)
     *
     * @param  array  $data
     * @return bool
     */
    public function createPost($data)
    {
        $post = new Post();

        $post->title            = $data['title'];
        $post->slug             = $data['slug'];
        $post->canonical_url    = $data['canonical_url'];
        $post->content          = $data['content'];
        $post->excerpt          = $data['excerpt'];
        $post->meta_description = $data['meta_description'];
        $post->featured_image   = $data['featured_image'];
        $post->enabled          = $data['enabled'];
        $post->publish_on       = $data['publish_on'];

        $post->created_at       = $data['created_at'] = Carbon::now();
        $post->created_by       = $data['created_by'] = Auth::user()->id;

        $post->updated_at       = $data['updated_at'] = Carbon::now();
        $post->updated_by       = $data['updated_by'] = Auth::user()->id;

        //return $post->save();
        $saveWentOk = $post->save();

        // If the save to the posts table went ok, then let's update the pivot tables,
        // since we now have the new post_id (actually, just the "id")

        if ($saveWentOk)
        {
            // save categories to pivot table
            $this->associateCategoriesToNewPost($post, $data['categories']);

            // save tags to pivot table
            $this->associateTagsToNewPost($post, $data['tags']);

            return true;
        }

        return false;
    }


    /*
     * UPDATE
     *
     * @param  array  $data
     * @return bool
     */
    public function updatePost($data)
    {
        $post = $this->getFind($data['id']);

        $post->title            = $data['title'];
        $post->slug             = $data['slug'];
        $post->canonical_url    = $data['canonical_url'];
        $post->content          = $data['content'];
        $post->excerpt          = $data['excerpt'];
        $post->meta_description = $data['meta_description'];
        $post->featured_image   = $data['featured_image'];
        $post->enabled          = $data['enabled'];
        $post->publish_on       = $data['publish_on'];

        //$post->created_at       = $data['created_at'] = Carbon::now();
        //$post->created_by       = $data['created_by'] = Auth::user()->id;

        $post->updated_at       = $data['updated_at'] = Carbon::now();
        $post->updated_by       = $data['updated_by'] = Auth::user()->id;

        //return $post->save();
        $saveWentOk = $post->save();

        // If the save to the posts table went ok, then let's update the pivot tables,
        // since we now have the new post_id (actually, just the "id")

        if ($saveWentOk)
        {
            // save categories to pivot table
            $this->associateCategoriesToUpdatedPost($post, $data);

            // save tags to pivot table
            $this->associateTagsToUpdatedPost($post, $data);

            return true;
        }

        return false;
    }


    /*
    * Associate each tag in the admin post form's input with the post just created
    *
    * @param  $post   Post object
    * @param  array   $categories
    * @return void
    */
    public function associateCategoriesToNewPost($post, $categories)
    {
        if (count($categories) > 0)
        {
            foreach ( $categories as $categoryId   )
            {
                $category = $this->categoryRepository->getFind($categoryId);
                $post->category()->save($category);
            }
        }
    }

    /*
    * Associate each category in the admin post form's input with the post just created
    *
    * @param   $post   Post object
    * @param   $data   Sanitized input from admin post form
    */
    public function associateCategoriesToUpdatedPost($post, $data)
    {
        if (count($data['categories']) > 0) {
            // There's probably a function for this, but for now:
            //  * create an array of updated tag IDs
            //  * detach the existing tag IDs and attach the new tag IDs, by using SYNC
            $newIds = array();
            // Attached the updated tags to the post
            foreach ($data['categories'] as $categoryId) {
                $newIds[] = $categoryId;
            }
            $post->category()->sync($newIds);
        }
    }


    /*
    * Associate each tag in the admin post form's input with the post just created
    *
    * @param  $post   Post object
    * @param  array   $tags
    * @return void
    */
    public function associateTagsToNewPost($post, $tags)
    {
        if (count($tags) > 0)
        {
            foreach ( $tags as $tagId   )
            {
                $tag = $this->tagRepository->getFind($tagId);
                $post->tag()->save($tag);
            }
        }
    }

    /*
    * Associate each tag in the admin post form's input with the post just created
    *
    * @param   $post   Post object
    * @param   $data   Sanitized input from admin post form
    */
    public function associateTagsToUpdatedPost($post, $data)
    {
        if (count($data['tags']) > 0) {
            // There's probably a function for this, but for now:
            //  * create an array of updated tag IDs
            //  * detach the existing tag IDs and attach the new tag IDs, by using SYNC
            $newIds = array();
            // Attached the updated tags to the post
            foreach ($data['tags'] as $tagId) {
                $newIds[] = $tagId;
            }
            $post->tag()->sync($newIds);
        }
    }






}