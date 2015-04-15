<?php namespace Lasallecms\Lasallecmsapi\Postupdates;

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

// Form Processing Interface
use Lasallecms\Lasallecmsapi\Contracts\FormProcessing;

// Form Processing Base Concrete Class
use Lasallecms\Lasallecmsapi\FormProcessing\BaseFormProcessing;

// Tag Repository Interface
use Lasallecms\Lasallecmsapi\Contracts\PostupdateRepository;


/*
 * Process an update.
 * Go through the standard process (interface).
 */
class UpdatePostupdateFormProcessing extends BaseFormProcessing implements FormProcessing {


    /*
     * Instance of repository
     *
     * @var Lasallecms\Lasallecmsapi\Contracts\PostupdateRepository
     */
    protected $repository;

    /*
     * Inject the model
     *
     * @param  Lasallecms\Lasallecmsapi\Contracts\PostupdateRepository
     */
    public function __construct(PostupdateRepository $repository) {
        $this->repository = $repository;
    }

    /*
     * The processing steps.
     *
     * @param  The command bus object   $updateTagCommand
     * @return The custom response array
     */
    public function quarterback($updateTagCommand) {

        // Get inputs into array
        $data = (array) $updateTagCommand;

        // Foreign Key check --> not applicable
        //$this->isForeignKeyOk($command);

        // Sanitize

        // **** YO THERE!!!!!  ==> NEED SANITIZE AND VALIDATE RULES IN DA MODEL!!!!!!     *********


        $data = $this->sanitize($data, "update");

        // Validate
        if ($this->validate($data, "update") != "passed")
        {
            // Unlock the record
            $this->unlock($data['id']);

            // Prepare the response array, and then return to the edit form with error messages
            return $this->prepareResponseArray('validation_failed', 500, $data, $this->validate($data, "update"));
        }


        // Update
        if (!$this->persist($data))
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
     * Any constraints to check due to foreign keys
     *
     * @param  array  $data
     * @return bool
     */
    public function isForeignKeyOk($data){}

    /*
     * Persist --> save/update to the database
     *
     * @param  array  $data
     * @return bool
     */
    public function persist($data){

        // Extra step: prepare data for persist
        $data = $this->repository->preparePostupdateForPersist($data);

        return $this->repository->updatePostupdate($data);
    }


}