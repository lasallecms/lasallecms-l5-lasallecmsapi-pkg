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

/**
 * Class Sanitation
 *
 * Trait made for specific use in Lasallecms\Lasallecmsapi\Repositories\BaseRepository.php
 *
 * @package Lasallecms\Lasallecmsapi\Repositories
 */
trait Sanitation
{
    /**
     * Get sanitation array for INSERT from model
     *
     * @return array
     */
    public function getSanitationRulesForCreate()
    {
        return $this->model->sanitationRulesForCreate;
    }


    /**
     * Get sanitation array for UPDATE from model
     *
     * @return array
     */
    public function getSanitationRulesForUpdate()
    {
        return $this->model->sanitationRulesForUpdate;
    }


    /**
     * For Lookup Tables
     *
     * Get sanitation array for INSERT from model
     *
     * @return array
     */
    public function getLookupTablesSanitationRulesForCreate()
    {
        return [
            'title'            => 'trim|strip_tags',
            'description'      => 'trim',
        ];
    }


    /**
     * For Lookup Tables
     *
     * Get sanitation array for UPDATE from model
     *
     * @return array
     */
    public function getLookupTablesSanitationRulesForUpdate()
    {
        return [
            'title'            => 'trim|strip_tags',
            'description'      => 'trim',
        ];
    }


    /**
     * Sanitize
     *
     * @param  array  $data
     * @param  array  $rules
     * @return array
     */
    public function getSanitize($data, $rules)
    {
        // iterate through each field
        foreach ($rules as $field => $rule)
        {
            // turn the listing of rules with a "|" separator into an array
            // yeah, $rule can contain multiple rules (ie, multiple php functions)
            $phpFunctions = explode('|', $rule);

            // iterate through each rule
            foreach($phpFunctions as $phpFunction)
            {
                $data[$field] = call_user_func_array($phpFunction, [$data[$field] ]);

                // debug
                //echo "<br>The field ".$field." is now = ".$data[$field]." (".$singleFunction.")";
            }
        }

        return $data;
    }
}