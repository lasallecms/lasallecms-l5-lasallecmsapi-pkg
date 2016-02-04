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

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


/**
 * Class UserGroups
 *
 * Trait made for specific use in Lasallecms\Lasallecmsapi\Repositories\BaseRepository.php
 *
 * @package Lasallecms\Lasallecmsapi\Repositories
 */
trait UserGroups
{
    ///////////////////////////////////////////////////////////////////
    ////////////////////      USER GROUPS         /////////////////////
    ///////////////////////////////////////////////////////////////////

    /*
     * Is the user allowed to do an action
     *
     * @param   string   $action   Generally: index, create, store, edit, insert, destroy
     * @return  bool
     */
    public function isUserAllowed($action)
    {
        $this->groupIdTitle(1);

        // Get the user's group.
        // Returns array of objects.
        $userGroups = $this->userBelongsToTheseGroups();

        // Array of allowed groups from the model
        $allowedGroups = $this->allowedGroupsForThisActionByModel($action);

        // Cycle through all the allowed groups, to see if the user belongs to one of these allowed groups.
        // One match is all it takes!
        foreach ($allowedGroups as $allowedGroup)
        {
            // Cycle through all the groups the user belongs to
            foreach ($userGroups as $userGroup)
            {
                //debug
                //echo "<br>".$this->groupIdTitle($userGroup->group_id)." and ".$allowedGroup;
                if (
                    (strtolower($this->groupIdTitle($userGroup->group_id)))
                    ==
                    (strtolower($allowedGroup))
                ) return true;
            }
        }
        return false;
    }

    /*
     * What groups does the model specify are allowed to do the controller's action.
     * Put another way, what group can do the index() for a specific controller?
     * This array resides in the model class.
     *
     * @param string   $action   A particular controller's action (method) -- just for that controller,
     *                                                                        *NOT* generically for all controllers!
     * @return array
     */
    public function allowedGroupsForThisActionByModel($action)
    {
        $allowedUserGroupsForAllActions = $this->model->allowed_user_groups;

        //http://laravel.com/docs/4.2/helpers#arrays
        return array_flatten( array_pluck($allowedUserGroupsForAllActions, $action) );
    }

    /*
     * What groups does the user belong?
     *
     * @return object
     */
    public function userBelongsToTheseGroups()
    {
        return DB::table('user_group')->where('user_id', '=', Auth::user()->id)->get();
    }

    /*
     * What is the title field for a given group_id, in the groups database table?
     *
     * @param  int   $group_id
     * @return string
     */
    public function groupIdTitle($group_id)
    {
        return DB::table('groups')->where('id', $group_id)->value('title');
    }

}