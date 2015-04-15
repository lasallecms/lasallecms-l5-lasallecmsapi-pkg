<?php namespace Lasallecms\Lasallecmsapi\Posts;

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

// Post Repository Interface
use Lasallecms\Lasallecmsapi\Contracts\PostRepository;


/*
 * Process an update.
 * Go through the standard process (interface).
 */
class UpdatePostFormProcessing extends BaseFormProcessing implements FormProcessing {


    /*
     * Instance of repository
     *
     * @var Lasallecms\Lasallecmsapi\Contracts\PostRepository
     */
    protected $repository;

    /*
     * Inject the model
     *
     * @param  Lasallecms\Lasallecmsapi\Contracts\PostRepository
     */
    public function __construct(PostRepository $repository) {
        $this->repository = $repository;
    }

    /*
     * The processing steps.
     *
     * @param  The command bus object   $updatePostCommand
     * @return The custom response array
     */
    public function quarterback($updatePostCommand) {

        // Get inputs into array
        $data = (array) $updatePostCommand;

        // Foreign Key check --> not applicable
        //$this->isForeignKeyOk($command);

        // Sanitize
        // THIS IS A FIRST PASS AT SANITIZING, BECAUSE A LOT MORE ACTION OCCURS IN persist()
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
        $data = $this->repository->preparePostForPersist($data);

        return $this->repository->updatePost($data);
    }


}