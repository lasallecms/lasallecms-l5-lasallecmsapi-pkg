<?php namespace Lasallecms\Lasallecmsapi\Tags;

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
use Lasallecms\Lasallecmsapi\Contracts\TagRepository;


/*
 * Process a new tag .
 * Go through the standard process (interface).
 */
class CreateTagFormProcessing extends BaseFormProcessing implements FormProcessing {

    /*
     * Instance of repository
     *
     * @var Lasallecms\Lasallecmsapi\Contracts\TagRepository
     */
    protected $repository;

    /*
     * Inject the model
     *
     * @param  Lasallecms\Lasallecmsapi\Contracts\TagRepository
     */
    public function __construct(TagRepository $repository) {
        $this->repository = $repository;
    }

    /*
     * The processing steps.
     *
     * @param  The command bus object   $createTagCommand
     * @return The custom response array
     */
    public function quarterback($createTagCommand) {

        // Get inputs into array
        $data = (array) $createTagCommand;

        // Foreign Key check --> not applicable
        //$this->isForeignKeyOk($command);

        // Sanitize
        $data = $this->sanitize($data, "create");

        // Validate
        if ($this->validate($data, "create") != "passed")
        {
            // Prepare the response array, and then return to the edit form with error messages
            return $this->prepareResponseArray('validation_failed', 500, $data, $this->validate($data, "create"));
        }


        // Create
        if (!$this->persist($data))
        {
            // Prepare the response array, and then return to the edit form with error messages
            // Laravel's https://github.com/laravel/framework/blob/5.0/src/Illuminate/Database/Eloquent/Model.php
            //  does not prepare a MessageBag object, so we'll whip up an error message in the
            //  originating controller
            return $this->prepareResponseArray('persist_failed', 500, $data);
        }

        // Unlock the record --> not applicable
        //$this->unlock($data['id']);

        // Prepare the response array, and then return to the command
        return $this->prepareResponseArray('create_successful', 200, $data);

    }


    /*
     * Any constraints to check due to foreign keys
     *
     * @param  array  $data
     * @return bool
     */
    public function isForeignKeyOk($data){}


    /*
     * Persist --> save/create to the database
     *
     * @param  array  $data
     * @return bool
     */
    public function persist($data){
        return $this->repository->createTag($data);
    }


}