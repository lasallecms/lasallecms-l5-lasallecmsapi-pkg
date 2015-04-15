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
use Carbon\Carbon;


/*
 * Eloquent implementation of the Post Update repository
 */
class PostupdateEloquent extends BaseEloquent implements PostupdateRepository {


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
    public function preparePostupdateForPersist($data)
    {
        $data['title']            = $this->prepareTitleForPersist($data['title']);
        $data['content']          = $this->prepareContentForPersist($data['content']);
        $data['excerpt']          = $this->prepareExcerptForPersist($data['excerpt'], $data['content']);
        $data['enabled']          = $this->prepareEnabledForPersist($data['enabled']);
        $data['publish_on']       = $this->preparePublishOnForPersist($data['publish_on']);

        return $data;
    }

    /*
     * Create (INSERT)
     *
     * @param  array  $data
     * @return bool
     */
    public function createPostupdate($data)
    {
        $postupdate = new Postupdate();

        $postupdate->post_id          = $data['post_id'];
        $postupdate->title            = $data['title'];
        $postupdate->content          = $data['content'];
        $postupdate->excerpt          = $data['excerpt'];
        $postupdate->enabled          = $data['enabled'];
        $postupdate->publish_on       = $data['publish_on'];


        $postupdate->created_at       = $data['created_at'] = Carbon::now();
        $postupdate->created_by       = $data['created_by'] = Auth::user()->id;

        $postupdate->updated_at       = $data['updated_at'] = Carbon::now();
        $postupdate->updated_by       = $data['updated_by'] = Auth::user()->id;

        $saveWentOk = $postupdate->save();

        if ($saveWentOk) return true;
        return false;
    }


    /*
     * UPDATE
     *
     * @param  array  $data
     * @return bool
     */
    public function updatePostupdate($data)
    {
        $postupdate = $this->getFind($data['id']);

        $postupdate->post_id          = $data['post_id'];
        $postupdate->title            = $data['title'];
        $postupdate->content          = $data['content'];
        $postupdate->excerpt          = $data['excerpt'];
        $postupdate->enabled          = $data['enabled'];
        $postupdate->publish_on       = $data['publish_on'];

        $postupdate->updated_at       = $data['updated_at'] = Carbon::now();
        $postupdate->updated_by       = $data['updated_by'] = Auth::user()->id;

        $saveWentOk = $postupdate->save();

        if ($saveWentOk) return true;
        return false;
    }



}