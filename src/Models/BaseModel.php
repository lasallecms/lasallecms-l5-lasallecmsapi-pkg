<?php
namespace Lasallecms\Lasallecmsapi\Models;

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

// Laravel classes
use Illuminate\Database\Eloquent\Model as Eloquent;

/*
 * Base Modelfor LaSalle Software, except LaSalleMart
 */
class BaseModel extends Eloquent
{
    /*
     * Laravel will execute this function automatically
     */
    public static function boot()
    {
        // empty for now
        // https://laracasts.com/series/digging-in/episodes/8
        // parent's boot function should occur first
        parent::boot();
    }


    ///////////////////////////////////////////////////////////////////
    ////////////////////         GETTERS          /////////////////////
    ///////////////////////////////////////////////////////////////////

    /*
     * Get the array of allowed user roles for the model
     */
    public function getAllowedUserGroups()
    {
        return $this->allowed_user_groups;
    }
}