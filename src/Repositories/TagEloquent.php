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

use Lasallecms\Lasallecmsapi\Contracts\TagRepository;

use Lasallecms\Lasallecmsapi\Models\Tag;

use Auth;


/*
 * Eloquent implementation of the Tag repository
 */
class TagEloquent extends BaseEloquent implements TagRepository {


    /*
     * Instance of model
     *
     * @var Lasallecms\Lasallecmsapi\Models\Tag
     */
    protected $model;


    /*
     * Inject the model
     *
     * @param  Lasallecms\Lasallecmsapi\Models\Tag
     */
    public function __construct(Tag $model)
    {
        $this->model = $model;
    }


    /*
     * Display all the tags in the admin listing
     *
     * @return collection
     */
    public function allTagsForDisplayOnAdminListing()
    {
        return $this->model->orderBy('title', 'ASC')->get();
    }


    /*
     * Find all the post records associated with a tag
     *
     * @param id  $id
     * @return int
     */
    public function countAllPostsThatHaveTagId($id)
    {
        $tag = $this->getFind($id);
        $tagsWithPosts = $tag->posts;
        return count($tagsWithPosts);
    }

    /*
     * Create (INSERT)
     *
     * @param  array  $data
     * @return bool
     */
    public function createTag($data)
    {
        $tag = new Tag();

        $tag->title       = $data['title'];
        $tag->description = $data['description'];
        $tag->created_by  = Auth::user()->id;
        $tag->updated_by  = Auth::user()->id;

        return $tag->save();
    }

    /*
     * UPDATE
     *
     * @param  array  $data
     * @return bool
     */
    public function updateTag($data)
    {
        $tag = $this->getFind($data['id']);
        $tag->description = $data['description'];
        return $tag->save();
    }



}