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



///////////////////////////////////////////////////////////////////
//// USER MANAGEMENT AND AUTHENTICATION IS SO BESPOKE THAT     ////
////      IT IS NOT PART OF LASALLE's FORM AUTOMATION          ////
///////////////////////////////////////////////////////////////////



// LaSalle Software
use Lasallecms\Lasallecmsapi\Repositories\BaseRepository;
use Lasallecms\Usermanagement\Models\User;

// Laravel classes
use Illuminate\Http\Request;
use Illuminate\Support\Str;

// Laravel facades
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Third party classes
use Carbon\Carbon;

/**
 * Class UserRepository
 * @package Lasallecms\Lasallecmsapi\Repositories
 */
class UserRepository extends BaseRepository
{
    /**
     * Instance of model
     *
     * @var Lasallecms\Lasallecmsapi\Models\User
     */
    protected $model;

    /**
     * @var Illuminate\Http\Request
     */
    protected $request;


    /**
     * Inject the model
     *
     * @param  Lasallecms\Lasallecmsapi\Models\User $model
     * @param  Illuminate\Http\Request              $request
     */
    public function __construct(User $model, Request $request) {
        $this->model   = $model;
        $this->request = $request;
    }


    /**
     * Display all the tags in the admin listing
     *
     * @return collection
     */
    public function allUsersForDisplayOnAdminListing() {
        return $this->model->orderBy('title', 'ASC')->get();
    }


    /**
     * Find all the post records associated with a tag
     *
     * @param  int    $id     id
     * @return array
     */
    public function countAllPostsThatHaveUserId($id)  {
        // How many posts did this user create (created_by)?
        $results = DB::table('posts')->where('created_by', '=', $id)->get();

        if ( (count($results)) == 0 || (empty($results)) ) {
            $created_by = 0;
        } else {
            $created_by = count($results);
        }

        // How many posts did this user update (updated_by)?
        $results = DB::table('posts')->where('updated_by', '=', $id)->get();

        if ( (count($results)) == 0 || (empty($results)) ) {
            $updated_by = 0;
        } else {
            $updated_by = count($results);
        }

        return ['created_by' => $created_by, 'updated_by' => $updated_by];
    }

    /**
     * Is the "First Among Equals" user specified in the auth config (which is set by my user
     * management package) actually in the database?
     *
     * @return bool
     */
    public function isFirstAmongEqualsUserInDatabase() {
        $config = config('lasallecmsusermanagement.administrator_first_among_equals_email');

        $results = count(
            $this->model
            ->where('email', '=', $config)
            ->get()
        );

        if ( $results == 0 ) {
            return false;
        }

        return true;
    }

    /**
     * Find the user's ID from the user's email address
     *
     * @param  string  $email   Email address
     * @return int
     */
    public function findUserIdByEmail($email) {
        return $this->model
            ->where('email', $email)
            ->value('id')
        ;
    }

    /**
     * Quick find the user name using the user's email address.
     *
     * The select must succeed because validateUserEmail() has already happened
     *
     * @param  string  $email   Email address
     * @return string
     */
    public function findUserNameByEmail($email) {
        return $this->model
            ->where('email', $email)
            ->value('name');
        ;
    }



    ///////////////////////////////////////////////////////////
    //                    SANITIZE                           //
    ///////////////////////////////////////////////////////////

    /**
     * Sanitize the sms phone number
     *
     * The user creation/modification flow does *not* go through the
     * admin form automation process. It is a bespoke process,
     * so I have to "wash" it here.
     *
     * @param  text   $phoneNumber
     * @return string
     */
    public function washPhoneNumber($phoneNumber) {

        // Remove all non digits
        return preg_replace('/[^0-9]/', '', $phoneNumber);
    }



    ///////////////////////////////////////////////////////////
    //                    VALIDATION                         //
    ///////////////////////////////////////////////////////////

    /**
     * Get validation array for UPDATE WITH PASSWORD from the user model
     *
     * @return array
     */
    public function getValidationRulesForUpdateWithPassword() {
        return $this->model->validationRulesForUpdateWithPassword;
    }

    /**
     * Get validation array for UPDATE WITH *NO* PASSWORD from the user model
     *
     * @return array
     */
    public function getValidationRulesForUpdateNoPassword() {
        return $this->model->validationRulesForUpdateNoPassword;
    }


    /**
     * Validate phone number
     *
     * @param  string   $phoneNumber
     * @return bool
     */
    public function validatePhoneNumber($phoneNumber) {
        // must be 10 chars (digits)
        if (strlen($phoneNumber) == 10) {
            return true;
        }

        return false;
    }

    /**
     * Password should not use the word "password"
     *
     * I am countering a pet peeve!
     *
     * @param  text  $password
     * @return bool
     */
    public function validatePasswordNotUseWordPassword($password) {
        $washedPassword = trim($password);
        $washedPassword = strtolower($password);

        // if the word "password" resides within the password, then no good
        if (strpos($washedPassword, "password") !== false) {
            return false;
        }

        return true;
    }

    /**
     * Password should not be the username
     *
     * In my experience, some bots use the username as the password
     *
     * @param  text  $username
     * @param  text  $password
     * @return bool
     */
    public function validatePasswordNotUseUsername($username, $password) {
        $washedPassword = trim($password);
        $washedPassword = strtolower($password);

        $washedUsername = trim($username);
        $washedUsername = strtolower($username);

        if ($washedPassword == $washedUsername) {
            return false;
        }

        // remove hyphens in the username, and compare again
        // maybe username "Bob Bloom" has password "bob-bloom"
        $washedUsernameHyphen = str_replace(" ", "-", $washedUsername);
        if ($washedPassword == $washedUsernameHyphen) {
            return false;
        }

        // remove underscores in the username, and compare again
        // maybe username "Bob Bloom" has password "bob_bloom"
        $washedUsernameUnderscore = str_replace(" ", "_", $washedUsername);
        if ($washedPassword == $washedUsernameUnderscore) {
            return false;
        }

        // remove spaces in the username, and compare again
        // maybe username "Bob Bloom" has password "bobbloom"
        $washedUsername = str_replace(" ", "", $washedUsername);
        if ($washedPassword == $washedUsername) {
            return false;
        }

        return true;
    }



    ///////////////////////////////////////////////////////////
    //                      PERSIST                          //
    ///////////////////////////////////////////////////////////

    /**
     * Create (INSERT)
     *
     * @param  array  $data
     * @return bool
     */
    public function createUser($data) {
        $user = new User;

        $user->name       = $data['name'];
        $user->email      = $data['email'];
        $user->password   = bcrypt($data['password']);

        if ($data['activated'] != "1") {
            $user->activated = 0;
        }

        if ($data['enabled'] != "1") {
            $user->enabled = 0;
        }


        // two factor authorization
        // the front-end registrationdoes not set a value for this field,
        // but the admin does
        if ($data['two_factor_auth_enabled'] != "1") {
            $user->two_factor_auth_enabled = 0;
        } else {
            $user->two_factor_auth_enabled = 1;
        }
        $user->phone_country_code      = $data['phone_country_code'];
        $user->phone_number            = $data['phone_number'];



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
            $user->created_by  = config('lasallecmsusermanagement.auth_user_id_for_created_by_for_frontend_user_registration');
            $user->updated_by  = config('lasallecmsusermanagement.auth_user_id_for_created_by_for_frontend_user_registration');
        }

        // INSERT!
        $saveWentOk = $user->save();

        // If the save to the database table went ok, then let's INSERT related IDs into the pivot tables,
        if ($saveWentOk) {
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

    /**
     * UPDATE
     *
     * @param  array  $data
     * @return bool
     */
    public function updateUser($data) {
        $user = $this->getFind($data['id']);

        $user->name       = $data['name'];
        $user->email      = $data['email'];

        // The password need not be changed
        if ($data['password'] != "") {
            $user->password   = bcrypt($data['password']);
        }

        if ($data['activated'] != "1") {
            $user->activated = 0;
        }

        if ($data['enabled'] != "1") {
            $user->enabled = 0;
        }

        // two factor authorization
        if ($data['two_factor_auth_enabled'] != "1") {
            $user->two_factor_auth_enabled = 0;
        } else {
            $user->two_factor_auth_enabled = 1;
        }
        $user->phone_country_code      = $data['phone_country_code'];
        $user->phone_number            = $data['phone_number'];


        $user->created_by  = Auth::user()->id;
        $user->updated_by  = Auth::user()->id;

        $saveWentOk = $user->save();

        // If the save to the database table went ok, then let's UPDATE/INSERT related IDs into the pivot tables,
        if ($saveWentOk) {
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


    /**
     * UPDATE the user record for fields "last_login" and "last_login_ip"
     *
     * This method is duplicated in Lasallecms\Usermanagement\Helpers\TwoFactorAuthorization\TwoFactorAuthHelper
     * Sorry ;-(
     *
     * @param  int    $userId          User ID
     * @return void
     */
    public function updateUserRecordWithLastlogin($userId) {

        $now = Carbon::now();
        $ip  = $this->request->getClientIp();

        DB::table('users')
            ->where('id', $userId)
            ->update(['last_login' => $now, 'last_login_ip' => $ip] )
        ;
    }


    ///////////////////////////////////////////////////////////
    //            The LaSalleCRM PEOPLES table               //
    ///////////////////////////////////////////////////////////

    /**
     * Get the ID from the PEOPLES table
     *
     * @param  int     $id      Users table ID
     * @return mixed
     */
    public function getPeopleIdForIndexListing($id) {
        // Does the PEOPLES table exist?
        if (Schema::hasTable('peoples')) {
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