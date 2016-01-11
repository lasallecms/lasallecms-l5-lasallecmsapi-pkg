<?php
namespace Lasallecms\Lasallecmsapi\Users;

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
//// IT IS NOT PART OF LASALLE's FORM AUTOMATION. HOWEVER,     ////
//// THE FORM PROCESSING IS STILL BASED ON THE FORM PROCESSING ////
//// INTERFACE, WHICH IS GREAT JUST FOR READABILITY, AND,      ////
//// IT USES THE BASE PROCESSING METHODS UNLESS OVER-RIDDEN.   ////
///////////////////////////////////////////////////////////////////



// LaSalle Software
use Lasallecms\Lasallecmsapi\Contracts\FormProcessing;
use Lasallecms\Lasallecmsapi\FormProcessing\BaseFormProcessing;
use Lasallecms\Lasallecmsapi\Repositories\UserRepository;

// Laravel facades
use Illuminate\Support\Facades\Validator;

/*
 * Process an update.
 * Go through the standard process (interface).
 */
class UpdateUserFormProcessing extends BaseFormProcessing implements FormProcessing
{
    /*
     * Instance of repository
     *
     * @var Lasallecms\Lasallecmsapi\Contracts\UserRepository
     */
    protected $repository;

    /*
     * Inject the model
     *
     * @param  Lasallecms\Lasallecmsapi\Contracts\UserRepository
     */
    public function __construct(UserRepository $repository) {
        $this->repository = $repository;
    }

    /*
     * The processing steps.
     *
     * @param  The command bus object   $updateUserCommand
     * @return The custom response array
     */
    public function quarterback($updateUserCommand) {
        // Get inputs into array
        $data = (array) $updateUserCommand;


        // Sanitize
        $data = $this->sanitize($data, "update");


        // Validate (note the override validate method below)
        if ($this->validate($data, "update") != "passed")
        {
            // Unlock the record
            $this->unlock($data['id']);

            // Prepare the response array, and then return to the edit form with error messages
            return $this->prepareResponseArray('validation_failed', 500, $data, $this->validate($data, "update"));
        }


        // Update
        //if (!$this->persist($data))
        if ( !$this->repository->updateUser($data) )
        {
            // Unlock the record
            $this->unlock($data['id']);

            // Prepare the response array, and then return to the edit form with error messages
            // Laravel's https://github.com/laravel/framework/blob/5.0/src/Illuminate/Database/Eloquent/Model.php
            //  does not prepare a MessageBag object, so we'll whip up an error message in the
            //  originating controller
            return $this->prepareResponseArray('persist_failed', 500, $data);
        }


        // Unlock the record
        $this->unlock($data['id']);


        // Prepare the response array, and then return to the command
        return $this->prepareResponseArray('update_successful', 200, $data);
    }


    /*
     * Over-riding the base validate method because there is one set of validation rules for "there is a password",
     * (ie, user is changing their password); and, another set of validation rules for "there is NO password"
     * (ie, user not changing the password)
     *
     * @param  array  $data
     * @param  text   $type   Are we validating a create or update? ==> WHICH IS NOT USED IN THIS CUSTOM VALIDATE METHOD
     * @return bool
     */
    public function validate($data, $type) {
        // If there is a password, get the validation rules for "there is a password)
        if ($data['password'])
        {
            $rules = $this->repository->getValidationRulesForUpdateWithPassword();

        } else {
            $rules = $this->repository->getValidationRulesForUpdateNoPassword();
        }

        $validator = Validator::make($data,$rules);

        if ($validator->fails()) return $validator->messages();

        return "passed";
    }
}