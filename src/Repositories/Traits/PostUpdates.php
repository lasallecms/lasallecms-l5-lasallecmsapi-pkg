<?php

namespace Lasallecms\Lasallecmsapi\Repositories\Traits;

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

// Laravel facades
use Illuminate\Support\Facades\DB;

/**
 * Class PostUpdates
 *
 * Trait made for specific use in Lasallecms\Lasallecmsapi\Repositories\BaseRepository.php
 *
 * @package Lasallecms\Lasallecmsapi\Repositories
 */
trait PostUpdates
{
    ///////////////////////////////////////////////////////////////////
    ////////////               POST UPDATES                   /////////
    ///////////////////////////////////////////////////////////////////

    /**
     * When a post update is created, update the POSTS table to indicate that a post update for that post exists.
     *
     * @param  int    $post_id    The ID of the post that the post update pertains.
     * @return void
     */
    public function postupdateExists($post_id)
    {
        DB::table('posts')
            ->where('id', '=', $post_id)
            ->update(['postupdate' => 1])
        ;
    }


    /**
     * What is the post_id of a given postupdate?
     *
     * @param  int    $id     The ID of a postupdate
     * @return mixed
     */
    public function postIdOfPostupdate($id)
    {
        // $record is a stdClass
        $record = DB::table('postupdates')
            ->select('post_id')
            ->where('id', '=', $id)
            ->first()
        ;

        return $record->post_id;
    }


    /**
     * When a post update is deleted, and no more post updates exist for that post, then update the
     * POSTS table to indicate that no post updates for that post exist.
     *
     * @param  int    $post_id    The ID of the post that the post update pertains.
     * @return void
     */
    public function postupdateNotExist($post_id)
    {
        if ($this->countPostUpdates($post_id) == 0)
        {
            DB::table('posts')
                ->where('id', '=', $post_id)
                ->update(['postupdate' => 0])
            ;
        }
    }


    /**
     * How many post update records exist for a particular post?
     *
     * @param   int    $post_id    The ID of the post that the post update pertains.
     * @return  int
     */
    public function countPostUpdates($post_id)
    {
        //$users = DB::table('users')->count();
        return DB::table('postupdates')
            ->where('post_id', $post_id)
            ->count()
            ;
    }
}