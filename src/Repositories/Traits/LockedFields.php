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

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Class LockedFields
 *
 * Trait made for specific use in Lasallecms\Lasallecmsapi\Repositories\BaseRepository.php
 *
 * @package Lasallecms\Lasallecmsapi\Repositories
 */
trait LockedFields
{
    /**
     * Unlock records belonging to the current user.
     *
     * @param  string  $tableName
     * @return bool
     */
    public function unlockMyRecords($tableName)
    {
        $results = $this->lockedRecordsByUser($tableName, Auth::user()->id);

        foreach($results as $result)
        {
            $this->unpopulateLockFields($result->id);
        }
    }


    /**
     * Collection of records that are locked by a specific user, for a specific table
     *
     * @param  string     $tableName
     * @param  int        $userId
     * @return collection
     */
    public function lockedRecordsByUser($tableName, $userId)
    {
        return DB::table($tableName)->where('locked_by', '=', $userId)->get();
    }


    /**
     * Is the record locked?
     * "Locked" is defined as the 'locked_by' field being populated; that is,> 0
     *
     * @param  int     $id
     * @return bool
     */
    public function isLocked($id)
    {
        $record = $this->model->findOrFail($id);

        if ($record->locked_by > 0) return true;

        return false;
    }


    /**
     * Populate the locked_at and locked_by fields.
     * By definition, this must be an UPDATE
     *
     * All that is needed is the ID
     *
     * @param  int     $id
     * @return bool
     */
    public function populateLockFields($id)
    {
        // $this->getSave($data);   --> creates new record ;-(
        // $this->getUpdate($data); --> integrity constraint violation: 1451 Cannot delete or
        //                              update a parent row: a foreign key constraint fails  ;-(
        // use the model, not the repository, to UPDATE
        $record = $this->model->findOrFail($id);

        $record->locked_by = Auth::user()->id;
        $record->locked_at = date('Y-m-d H:i:s');

        return $record->save();
    }


    /**
     * Un-populate the locked_at and locked_by fields.
     * By definition, this must be an UPDATE
     *
     * All that is needed is the ID
     *
     * @param  int     $id
     * @return mixed(?)
     */
    public function unpopulateLockFields($id)
    {
        // $this->getSave($data);   --> creates new record ;-(
        // $this->getUpdate($data); --> integrity constraint violation: 1451 Cannot delete or
        //                              update a parent row: a foreign key constraint fails  ;-(
        // use the model, not the repository, to UPDATE
        $record = $this->model->findOrFail($id);

        // Locked by field allowed to be null
        $record->locked_by = null;
        $record->locked_at = null;

        return $record->save();
    }
}