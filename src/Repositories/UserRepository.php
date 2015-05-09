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
 * @version    1.0.0
 * @link       http://LaSalleCMS.com
 * @copyright  (c) 2015, The South LaSalle Trading Corporation
 * @license    http://www.gnu.org/licenses/gpl-3.0.html
 * @author     The South LaSalle Trading Corporation
 * @email      info@southlasalle.com
 *
 */

// LaSalle Software
use Lasallecms\Usermanagement\Models\User;

// Laravel facades
use Illuminate\Support\Facades\Auth;

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
     * @return int
     */
    public function countAllPostsThatHaveUserId($id)
    {
        $user = $this->getFind($id);
        $usersWithPosts = $user->posts;
        return count($usersWithPosts);
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


//config('auth.administrator_first_among_equals_email')

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

        $user->created_by  = Auth::user()->id;
        $user->updated_by  = Auth::user()->id;

        return $user->save();
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
        $user->password   = $data['password'];

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

        return $user->save();
    }
}