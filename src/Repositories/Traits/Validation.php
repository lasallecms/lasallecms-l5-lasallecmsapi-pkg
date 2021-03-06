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
 * Class Validation
 *
 * Trait made for specific use in Lasallecms\Lasallecmsapi\Repositories\BaseRepository.php
 *
 * @package Lasallecms\Lasallecmsapi\Repositories
 */
trait Validation
{
    /**
     * Get validation array for INSERT from model
     *
     * @return array
     */
    public function getValidationRulesForCreate()
    {
        return $this->model->validationRulesForCreate;
    }


    /**
     * Get validation array for UPDATE from model
     *
     * @return array
     */
    public function getValidationRulesForUpdate()
    {
        return $this->model->validationRulesForUpdate;
    }


    /**
     * For Lookup Tables
     *
     * Get validation array for INSERT from model
     *
     * @return array
     */
    public function getLookupTablesValidationRulesForCreate()
    {
        return [
            'title'            => 'required|min:4|unique:'.$this->model->table,
            'description'      => 'min:4',
            'enabled'          => 'boolean',
        ];
    }


    /**
     * For Lookup Tables
     *
     * Get validation array for UPDATE from model
     *
     * @return array
     */
    public function getLookupTablesValidationRulesForUpdate()
    {
        return [
            'title'            => 'required|min:4',
            'description'      => 'min:4',
            'enabled'          => 'boolean',
        ];
    }
}