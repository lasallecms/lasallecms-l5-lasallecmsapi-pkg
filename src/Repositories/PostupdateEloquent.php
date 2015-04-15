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

use Lasallecms\Lasallecmsapi\Contracts\PostupdateRepository;

use Lasallecms\Lasallecmsapi\Models\Postupdate;

use Illuminate\Support\Facades\Auth;


/*
 * Eloquent implementation of the Post Update repository
 */
class PostupdateEloquent extends BaseEloquent implements TagRepository {


    /*
     * Instance of model
     *
     * @var Lasallecms\Lasallecmsapi\Models\Postupdate
     */
    protected $model;


    /*
     * Inject the model
     *
     * @param  Lasallecms\Lasallecmsapi\Models\Postupdate
     */
    public function __construct(Postupdate $model)
    {
        $this->model = $model;
    }


    /*
      * Display all post updatess in the admin listing
      *
      * @return collection
      */
    public function allPostupdatesForDisplayOnAdminListing()
    {
        //return $this->model->orderBy('updated_at', 'DESC')->get();
        return $this->model
            ->orderBy('post_id', 'DESC')
            ->orderBy('updated_at', 'DESC')
            ->get();
    }



    /*
     * All post updates associated with a specific post ID
     *
     * @param  integer    $id   Post ID
     * @return collection
     */
    public function allPostupdatesForPostID($id)
    {
        return $this->model
            ->where('post_id', '=', $id)
            ->orderBy('updated_at', 'ASC')
            ->get();
    }

    /*
     * Create (INSERT)
     *
     * @param  array  $data
     * @return bool
     */
    public function createPostupdate($data)
    {

        // WHOA --> pretty involved process at
        // https://github.com/bbloom/lasallecms/blob/master/src/Lasallecms/repositories/PostupdateRepositoryEloquent.php

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
    public function updatePostupdate($data)
    {
        $tag = $this->getFind($data['id']);
        $tag->description = $data['description'];
        return $tag->save();
    }



}