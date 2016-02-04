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

// Laravel facades
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

// Third party classes
use Carbon\Carbon;

/**
 * Class Persist
 *
 * Trait made for specific use in Lasallecms\Lasallecmsapi\Repositories\BaseRepository.php
 *
 * @package Lasallecms\Lasallecmsapi\Repositories
 */
trait Persist
{
    ///////////////////////////////////////////////////////////////////
    ////////////         PERSIST: CREATE/INSERT           /////////////
    ///////////////////////////////////////////////////////////////////

    /**
     * Create (INSERT) Record
     *
     * @param  array  $data
     * @return bool
     */
    public function createRecord($data)
    {
        $modelInstance = new $this->model;

        $field_list = $data['field_list'];
        foreach ($field_list as $field)
        {
            // Ignore primary ID field for INSERT as created during said INSERT
            if ($field['name'] == "id") continue;

            // Special handling for password fields
            if ($field['type'] == "password")
            {
                $modelInstance->$field['name'] = bcrypt($data[$field['name']]);
                continue;
            }

            // Special handling for the composite_title field
            if ($field['name'] == "composite_title")
            {
                $modelInstance->title = $data[$field['name']];
                continue;
            }

            if (
                ($field['name'] == "featured_image_url") ||
                ($field['name'] == "featured_image_upload") ||
                ($field['name'] == "featured_image_server")

            ) {
                // Ignore these form fields, as there are no such database fields.
                // These fields are for featured image selection and processing only.
                continue;
            }

            // Related tables with pivot tables; that is, with one-to-many or many-to-many relationships
            // have their own save routine, since the relationships are stored in a separate database table
            // Note: empty 'related_pivot_table' in the field list produces exception error. Only 'related_table"
            //       type has this 'related_pivot_table' array element
            if ( !empty($field['related_pivot_table']))
            {
                if (($field['type'] == "related_table") && ($field['related_pivot_table'] == "true"))  continue;
            }

            $modelInstance->$field['name'] = $data[$field['name']];
        }

        // Assign data to the standard database fields
        $modelInstance->created_at       = $data['created_at'] = Carbon::now();
        $modelInstance->updated_at       = $data['updated_at'] = Carbon::now();

        // If the user is logged-in, the use the user's ID.
        // Otherwise, this INSERT/create is automatically generated so there's no person
        // to assign these fields to. So, use the user ID specified in the config.
        if (Auth::check()) {
            $modelInstance->created_by       = $data['created_by'] = Auth::user()->id;
            $modelInstance->updated_by       = $data['updated_by'] = Auth::user()->id;
        } else {
            $modelInstance->created_by       = config('lasallecmsusermanagement.auth_user_id_for_created_by_for_frontend_user_registration');
            $modelInstance->updated_by       = config('lasallecmsusermanagement.auth_user_id_for_created_by_for_frontend_user_registration');
        }



        // INSERT!
        $saveWentOk = $modelInstance->save();

        // If the save to the database table went ok, then let's INSERT related IDs into the pivot tables,
        if ($saveWentOk)
        {

            // Iterate through the field list to identify possible table relationships that use pivot database tables
            foreach ($field_list as $field)
            {
                if (($field['type'] == "related_table") && (!empty($field['related_pivot_table'])))
                {
                    // If the field is nullable, then having associated records is optional.
                    // Having trouble with auto-generated data sending null value, so added "empty" condition
                    if (
                        ( ($data == "")    ||
                          ($data == null)  ||
                          (!$data)         ||
                          (empty($data)) ) ||
                          ($data[$field['name']] == null)

                        &&
                        ($field['nullable'])
                    )
                    {
                        // blank on purpose... do not add records to the pivot table;

                    } else {
                        // INSERT into the pivot table
                        $this->associateRelatedRecordsToNewRecord(
                            $modelInstance,
                            $data[$field['name']],
                            $field['related_namespace'],
                            $field['related_model_class']
                        );
                    }
                }
            }
            return true;
        }

        return false;
    }


    /**
     * Associate each related record with the record just created
     *
     * @param  object    $modelInstance       object just created
     * @param  array     $associatedRecords   array of id's associated with the record just created
     * @param  string    $relatedNamespace    Namespace of the associated model
     * @param  string    $relatedModelClass   Class of the associated model
     * @return void
     */
    public function associateRelatedRecordsToNewRecord($modelInstance, $associatedRecords, $relatedNamespace, $relatedModelClass)
    {
        // Check if there are any records to insert into the pivot table
        if (count($associatedRecords) > 0)
        {
            // What is the namespace.class of the related table?
            $namespaceModelclass = $relatedNamespace . "\\". $relatedModelClass;

            // We need the repository of the related table (model?!). That repository is... well, it is
            // this repository class: the base repository class! We need a new instance with the related model.
            // So, create the new base repository instance...
            $relatedRepository = new \Lasallecms\Lasallecmsapi\Repositories\BaseRepository();

            /// ... and inject the related model class into it
            $relatedRepository->injectModelIntoRepository($namespaceModelclass);


            // For the purpose of saving to the pivot table, we need the method name of the related model as
            // it is in the model. As the method would be capitalized, let's un-capitalize it
            $relatedMethod = strtolower($relatedModelClass);

            // for each record that needs to be INSERTed into the pivot table
            foreach ($associatedRecords as $associatedRecordId)
            {
                // get the record in the related table, so we can use the info to save to the pivot table
                $associatedRecord = $relatedRepository->getFind($associatedRecordId);

                // save to the pivot table
                $modelInstance->$relatedMethod()->save($associatedRecord);
            }
        }
    }



    ///////////////////////////////////////////////////////////////////
    ////////////            PERSIST: UPDATE               /////////////
    ///////////////////////////////////////////////////////////////////

    /**
     * UPDATE
     *
     * @param  array  $data
     * @return bool
     */
    public function updateRecord($data)
    {
        $modelInstance = $this->getFind($data['id']);

        $field_list = $data['field_list'];
        foreach ($field_list as $field)
        {
            // Special handling for password fields
            if ($field['type'] == "password")
            {
                $modelInstance->$field['name'] = bcrypt($data[$field['name']]);
                continue;
            }

            // Special handling for the composite_title field
            if ($field['name'] == "composite_title")
            {
                $modelInstance->title = $data[$field['name']];
                continue;
            }

            if (
                ($field['name'] == "featured_image_url") ||
                ($field['name'] == "featured_image_upload") ||
                ($field['name'] == "featured_image_server")

            ) {
                // Ignore these form fields, as there are no such database fields.
                // These fields are for featured image selection and processing only.
                continue;
            }

            // Related tables with pivot tables; that is, with one-to-many or many-to-many relationships
            // have their own save routine, since the relationships are stored in a separate database table
            // Note: empty 'related_pivot_table' in the field list produces exception error. Only 'related_table"
            //       type has this 'related_pivot_table' array element
            if ( !empty($field['related_pivot_table']))
            {
                if (($field['type'] == "related_table") && ($field['related_pivot_table'] == "true"))  continue;
            }

            $modelInstance->$field['name'] = $data[$field['name']];
        }

        // Assign data to the standard database fields
        $modelInstance->updated_at       = $data['updated_at'] = Carbon::now();
        $modelInstance->updated_by       = $data['updated_by'] = Auth::user()->id;

        $saveWentOk = $modelInstance->save();

        // If the save to the database table went ok, then let's UPDATE/INSERT related IDs into the pivot tables,
        if ($saveWentOk)
        {
            // Iterate through the field list to identify possible table relationships that use pivot database tables
            foreach ($field_list as $field)
            {
                if (($field['type'] == "related_table") && ($field['related_pivot_table']))
                {
                    // If the field is nullable, then having associated records is optional.
                    if (
                        ( ($data == "")   ||
                            ($data == null) ||
                            (!$data)        ||
                            (empty($data)) )
                        &&
                        ($field['nullable'])
                    )
                    {
                        // blank on purpose... do not add records to the pivot table;
                    } else {
                        // INSERT into the pivot table
                        $this->associateRelatedRecordsToUpdatedRecord(
                            $modelInstance,
                            $data[$field['name']],
                            $field['related_model_class']
                        );
                    }
                }
            }
            return true;
        }

        return false;
    }


    /**
     * Associate each related record with the record just updated.
     *
     * @param  object    $modelInstance       object just updated
     * @param  array     $associatedRecords   array of id's associated with the record just updated
     * @param  string    $relatedNamespace    Namespace of the associated model
     * @param  string    $relatedModelClass   Class of the associated model
     * @return void
     */
    public function associateRelatedRecordsToUpdatedRecord($modelInstance, $associatedRecords, $relatedModelClass)
    {
        // Check if there are any records to sync with the pivot table
        if (count($associatedRecords) > 0)
        {
            // There's probably a function for this, but for now:
            //  (i)  create an array of the related IDs
            //  (ii) detach the existing tag IDs and attach the new tag IDs, by using SYNC

            //  (i)  create an array of the related IDs
            $newIds = array();
            foreach ($associatedRecords as $associatedId)
            {
                $newIds[] = $associatedId;
            }

            // For the purpose of saving to the pivot table, we need the method name of the related model as
            // it is in the model. As the method would be capitalized, let's un-capitalize it
            $relatedMethod = strtolower($relatedModelClass);

            // (ii) detach the existing tag IDs and attach the new tag IDs, by using SYNC
            $modelInstance->$relatedMethod()->sync($newIds);
        }
    }


    ///////////////////////////////////////////////////////////////////
    ////////////            PERSIST: DELETE               /////////////
    ///////////////////////////////////////////////////////////////////

    /**
     * DELETE
     *
     * @param  int  $id
     * @return bool
     */
    public function destroyRecord($id)
    {
        $deleteWentOk = $this->getDestroy($id);

        if ($deleteWentOk) {

            // Get the field list
            // The field list resides in the model. Exists either as a property, or returned from method -- but not both.
            if ($this->model->field_list) {
                // exists as a property
                $fieldList = $this->model->field_list;
            } else {
                // exists via method
                $fieldList = $this->model->getFieldList();
            }

            // What would the pivot table's fieldname be, for the pivot table's field referring to $id?
            $pivotTableFieldName = strtolower($this->model->model_class) . "_id";

            // Iterate through the field list, looking for pivot tables
            // Iterate through the field list to identify possible table relationships that use pivot database tables
            foreach ($fieldList as $field) {

                if ($field['type'] == "related_table")  {

                    if (($field['related_pivot_table'])) {
                        // Delete associated records
                        DB::table($field['related_pivot_table_name'])
                            ->where($pivotTableFieldName, '=', $id)
                            ->delete();
                    }

                }
            }

            return true;
        }

        return false;
    }
}