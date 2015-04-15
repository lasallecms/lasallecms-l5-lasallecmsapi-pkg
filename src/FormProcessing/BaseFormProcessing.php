<?php namespace Lasallecms\Lasallecmsapi\FormProcessing;

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

// Laravel's Validator facade
use Validator;

class BaseFormProcessing {


    /*
     * Sanitize and transform the data
     *
     * @param  array  $data
     * @param  text   $type   Either "create" or "update"
     * @return array
     */
    public function sanitize($data, $type){

        if (strtolower($type) == "create") $rules = $this->repository->getSanitationRulesForCreate();

        if (strtolower($type) == "update") $rules = $this->repository->getSanitationRulesForUpdate();

        $sanitizedData = $this->repository->getSanitize($data, $rules);

        return $sanitizedData;
    }

    /*
     * Validate
     *
     * @param  array  $data
     * @param  text   $type   Are we validating a create or update?
     * @return bool
     */
    public function validate($data, $type){

        if (strtolower($type) == "create") $rules = $this->repository->getValidationRulesForCreate();

        if (strtolower($type) == "update") $rules = $this->repository->getValidationRulesForUpdate();

        $validator = Validator::make($data,$rules);

        if ($validator->fails()) return $validator->messages();

        return "passed";
    }

    /*
     * Unlock the record.
     * "Locked" is defined as the 'locked_by' field being populated; that is,> 0
     *
     * @param  int   $id
     * @return bool
     */
    public function unlock($id){
        $this->repository->unpopulateLockFields($id);
    }

    /*
     * Prepare the response array.
     * The response array is returned to the originating controller method, to let the
     * originating controller know what happened. The originating controller can then take proper action.
     *
     * @param  string  $status_text    Eg: "validation_failed"
     * @param  int     $status_code    200 = success
     *                                 400 = warning
     *                                 500 = error
     * @param  array   $data           Sanitized data
     * @param  object  $errorMessages  Expect this to be
     *                                 https://github.com/laravel/framework/blob/5.0/src/Illuminate/Support/MessageBag.php
     */
    public function prepareResponseArray($status_text, $status_code, $data = null, $errorMessages = null){
        $response = [
            'status_text'   => $status_text,
            'status_code'   => $status_code,
            'data'          => $data,
            'errorMessages' => $errorMessages
        ];

        return $response;
    }
}
