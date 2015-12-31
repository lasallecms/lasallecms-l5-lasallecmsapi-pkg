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



///////////////////////////////////////////////////////////////////
//// USER MANAGEMENT AND AUTHENTICATION IS SO BESPOKE THAT     ////
////      IT IS NOT PART OF LASALLE's FORM AUTOMATION          ////
///////////////////////////////////////////////////////////////////



// LaSalle Software
use Lasallecms\Usermanagement\Models\User;

// Laravel facades
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UserRepository extends BaseRepository
{
    /*
     * Instance of model
     *
     * @var Lasallecms\Lasallecmsapi\Models\User
     */
    protected $model;


    /*
     * Inject the model
     *
     * @param  Lasallecms\Lasallecmsapi\Models\User
     */
    public function __construct(User $model)
    {
        $this->model = $model;
    }


    /*
     * Display all the tags in the admin listing
     *
     * @return collection
     */
    public function allUsersForDisplayOnAdminListing()
    {
        return $this->model->orderBy('title', 'ASC')->get();
    }


    /*
     * Find all the post records associated with a tag
     *
     * @param id  $id
     * @return array
     */
    public function countAllPostsThatHaveUserId($id)
    {
        // How many posts did this user create (created_by)?
        $results = DB::table('posts')->where('created_by', '=', $id)->get();

        if ( (count($results)) == 0 || (empty($results)) )
        {
            $created_by = 0;
        } else {
            $created_by = count($results);
        }

        // How many posts did this user update (updated_by)?
        $results = DB::table('posts')->where('updated_by', '=', $id)->get();

        if ( (count($results)) == 0 || (empty($results)) )
        {
            $updated_by = 0;
        } else {
            $updated_by = count($results);
        }

        return ['created_by' => $created_by, 'updated_by' => $updated_by];
    }

    /*
     * Is the "First Among Equals" user specified in the auth config (which is set by my user
     * management package) actually in the database?
     *
     * @return bool
     */
    public function isFirstAmongEqualsUserInDatabase()
    {
        $config = config('auth.administrator_first_among_equals_email');

        $results = count(
            $this->model
            ->where('email', '=', $config)
            ->get()
        );

        if ( $results == 0 ) return false;

        return true;
    }


    /*
     * Get validation array for UPDATE WITH PASSWORD from the user model
     *
     * @return array
     */
    public function getValidationRulesForUpdateWithPassword()
    {
        return $this->model->validationRulesForUpdateWithPassword;
    }

    /*
     * Get validation array for UPDATE WITH *NO* PASSWORD from the user model
     *
     * @return array
     */
    public function getValidationRulesForUpdateNoPassword()
    {
        return $this->model->validationRulesForUpdateNoPassword;
    }


    /*
     * Create (INSERT)
     *
     * @param  array  $data
     * @return bool
     */
    public function createUser($data)
    {
        $user = new User;

        $user->name       = $data['name'];
        $user->email      = $data['email'];
        $user->password   = bcrypt($data['password']);

        if ($data['activated'] != "1")
        {
            $user->activated = 0;
        }

        if ($data['enabled'] != "1")
        {
            $user->enabled = 0;
        }

        // When the admin is adding a new user, use their user id
        // for the created_by and updated_by fields. When no
        // one is logged in during this user creation process, it
        // means that someone is initiating their own registration
        // via the front-end. In which case, use the user id 
        // specified in the auth config file.

        if (isset(Auth::user()->id)) {
            $user->created_by  = Auth::user()->id;
            $user->updated_by  = Auth::user()->id;
        } else {
            $user->created_by  = config('auth.auth_user_id_for_created_by_for_frontend_user_registration');
            $user->updated_by  = config('auth.auth_user_id_for_created_by_for_frontend_user_registration');
        }

        // INSERT!
        $saveWentOk = $user->save();

        // If the save to the database table went ok, then let's INSERT related IDs into the pivot tables,
        if ($saveWentOk)
        {
            // INSERT into the pivot table
            $this->associateRelatedRecordsToNewRecord(
                $user,
                $data['groups'],
                'Lasallecms\Usermanagement\Models',
                'Group'
            );

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
    public function updateUser($data)
    {
        $user = $this->getFind($data['id']);

        $user->name       = $data['name'];
        $user->email      = $data['email'];

        // The password need not be changed
        if ($data['password'] != "")
        {
            $user->password   = bcrypt($data['password']);
        }

        if ($data['activated'] != "1")
        {
            $user->activated = 0;
        }

        if ($data['enabled'] != "1")
        {
            $user->enabled = 0;
        }

        $user->created_by  = Auth::user()->id;
        $user->updated_by  = Auth::user()->id;

        $saveWentOk = $user->save();

        // If the save to the database table went ok, then let's UPDATE/INSERT related IDs into the pivot tables,
        if ($saveWentOk)
        {
            // INSERT into the pivot table
            $this->associateRelatedRecordsToUpdatedRecord(
                $user,
                $data['groups'],
                'Group'
            );

            return true;
        }

        return false;
    }


    ///////////////////////////////////////////////////////////
    //            The LaSalleCRM PEOPLES table               //
    ///////////////////////////////////////////////////////////

    /*
     * Get the ID from the PEOPLES table
     *
     * @param  int     $id      Users table ID
     * @return mixed
     */
    public function getPeopleIdForIndexListing($id)
    {
        // Does the PEOPLES table exist?
        if (Schema::hasTable('peoples'))
        {
            $person = DB::table('peoples')->where('user_id', '=', $id)->first();

            if (!$person) return "Not in LaSalleCRM";

            $full_url = route('admin.crmpeoples.edit', $person->id);

            $html  = '<a href="';
            $html .= $full_url;
            $html .= '">';
            $html .= 'Edit this LaSalle Customer';
            $html .= '</a>';

            return $html;
        }

        return "LaSalleCRM is not installed";
    }
}