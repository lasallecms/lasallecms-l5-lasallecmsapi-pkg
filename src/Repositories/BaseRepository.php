<?php

namespace Lasallecms\Lasallecmsapi\Repositories;

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

/**
 * This is the common base repository for all LaSalle Software, except LaSalleMart
 */

// LaSalle Software
use Lasallecms\Helpers\Dates\DatesHelper;

// LaSalle Software Traits Specific to this BaseRepository class
use Lasallecms\Lasallecmsapi\Repositories\Traits\LockedFields;
use Lasallecms\Lasallecmsapi\Repositories\Traits\UserGroups;
use Lasallecms\Lasallecmsapi\Repositories\Traits\Sanitation;
use Lasallecms\Lasallecmsapi\Repositories\Traits\Validation;
use Lasallecms\Lasallecmsapi\Repositories\Traits\PrepareForPersist;
use Lasallecms\Lasallecmsapi\Repositories\Traits\Persist;
use Lasallecms\Lasallecmsapi\Repositories\Traits\RepositorySpecificHTMLHelpers;
use Lasallecms\Lasallecmsapi\Repositories\Traits\PostUpdates;

// Laravel facades
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Laravel classes
use Illuminate\Container\Container as Container;

class BaseRepository
{

    // LaSalle Software Traits Specific to this BaseRepository class
    use LockedFields;
    use UserGroups;
    use Sanitation;
    use Validation;
    use PrepareForPersist;
    use Persist;
    use RepositorySpecificHTMLHelpers;
    use PostUpdates;



    ///////////////////////////////////////////////////////////////////
    //////////////////////       PROPERTIES       /////////////////////
    ///////////////////////////////////////////////////////////////////

    /**
     * @var Illuminate\Container\Container
     */
    protected $app;

    /**
     * @var  namespace and class of relevant model
     */
    protected $model;


    ///////////////////////////////////////////////////////////////////
    /////////////////////       CONSTRUCTOR       /////////////////////
    ///////////////////////////////////////////////////////////////////

    /**
     * Inject a new instance of the container in order to inject the relevant model.
     */
    public function __construct()
    {
        $this->app   = new Container;
    }



    ///////////////////////////////////////////////////////////////////
    //////////////////////    MODEL INJECTION     /////////////////////
    ///////////////////////////////////////////////////////////////////

    /**
     *
     * Inject the container, then use the container to inject the model object
     * "Resolve something out of the container"
     * http://laravel.com/docs/5.0/container#basic-usage
     *
     * Called from controller
     *
     * @param  string   $modelNamespaceClass  The model's concatenated namespace and class name
     *
     * @return object
     */
    public function injectModelIntoRepository($modelNamespaceClass)
    {
        $this->model = $this->app->make($modelNamespaceClass);
    }


    ///////////////////////////////////////////////////////////////////
    ////       LARAVEL MODEL METHODS IN REPOSITORY FORM       /////////
    ///////////////////////////////////////////////////////////////////

    /**
     * Return entire collection
     *
     *  @return eloquent
     */
    public function getAll()
    {
        return $this->model->all();
    }

    /**
     * Return specific model
     *
     * @param id         Post ID
     * @return eloquent
     */
    public function getFind($id)
    {
        return $this->model->findOrfail($id);
    }


    /**
     * Create model
     *
     * @param  data     Input data
     * @return eloquent
     */
    public function getCreate($data)
    {
        return $this->model->create($data);
    }


    /**
     * Store model
     *
     * @param  data     Input data
     *
     * @return eloquent
     */
    public function getStore($data)
    {
        return $this->model->store($data);
    }


    /**
    * Save model
    *
    * @return eloquent
    */
    public function getSave()
    {
        return $this->model->save();
    }


    /**
    * Update model
    *
    * @param  data     array  Input data
    * @return eloquent
    */
    public function getUpdate($data)
    {
        return $this->model->update($data);
    }


    /**
     * Delete a model
     *
     * @param id         Post ID
     * @return eloquent
     */
    public function getDestroy($id)
    {
        return $this->model->destroy($id);
    }


    /**
     * Lists
     *
     * @param id         Post ID
     * @return eloquent
     */
    public function getLists($name, $id)
    {
        //return $this->model->lists($name, $id)->orderBy('title', 'ASC');
        return $this->model->lists($name, $id);
    }


    /**
     * Display all the records ordered by publish_on, title, DESC
     *
     * @return collection
     */
    public function allRecordOrderbyPublishonTitleDesc()
    {
        return $this->model->orderBy('publish_on', 'title', 'DESC')->get();
    }



    ///////////////////////////////////////////////////////////////////
    //////////////     Foreign Key Constraint       ///////////////////
    ///////////////////////////////////////////////////////////////////

    /**
     * Is a table record used in another table?
     *
     * For Lookup Tables
     *
     * @param   int  $id  Lookup Table ID
     * @return  array
     */
    public function foreignKeyChecks($id)
    {
        return $this->model->foreignKeyCheck($id);
    }


    /**
     * A related table can be optional. For example, the PEOPLES table: a person does not have to
     * be a LaSalle Software user that can login. In fact, it is preferable to *not* make every
     * person in the CRM database an actual user. MySQL allows for the option to relate, or not, relate
     * a record via "NULL" or "NOT NULL". Eloquent uses "nullable()":
     * "$table->integer('locked_by')->nullable()->unsigned();",
     *
     * When a related table is mandatory; an ID from the related table must exist in the primary table.
     * What if there are no records in the related table? Upon save/update, get an "integrity" error from
     * MySQL. It is better for LaSalle Software to catch the error first (before one spends time filling
     * out a create/edit form), and not display the create/edit form.
     *
     * This method checks if there are mandatory related tables with no records.
     *
     * This method relevant for regular tables (not Lookup Tables).
     *
     * Note: DISABLED records in the related table are ignored!
     *
     * @param   int  $fields    The field list (specified in the model)
     * @return  array
     */
    public function isNotNullableRelatedTablesWithNoRecords($fields)
    {
        $result = [];
        $result['mandatory_no_records'] = false;

        // iterate through the fields
        foreach ($fields as $field)
        {
            // is this field a related table?
            if ( $field['type'] != "related_table" ) continue;

            // is this related table field mandatory (ie, NOT NULL)?
            if ($field['nullable']) continue;

            // are there enabled records in the related table?
            $records = $this->selectOptionWhereClauseEnabledField($field['related_table_name']);

            if (empty($records))
            {
                $result['mandatory_no_records'] = true;
                $result['field']                = $field;
                return $result;
            }
        }

        return $result;
    }


    ///////////////////////////////////////////////////////////////////
    //////////////          DO NOT DELETE           ///////////////////
    ///////////////////////////////////////////////////////////////////

    /**
     * Is this record *NOT* supposed to be deleted?
     *
     * @param   int  $id  Lookup Table ID
     * @return  bool
     */
    public function doNotDelete($id)
    {
        if (empty($this->model->do_not_delete_these_core_records)) return false;

        $table             = $this->model->table;
        $titlesDoNotDelete = $this->model->do_not_delete_these_core_records;
        $titleToBeDeleted  = DB::table($table)->where('id', '=', $id)->value('title');

        if (in_array($titleToBeDeleted, $titlesDoNotDelete)) return true;
        return false;
    }



    ///////////////////////////////////////////////////////////////////
    ////////////            MISC METHODS              /////////////////
    ///////////////////////////////////////////////////////////////////

    /**
     * What (lookup) table records are associated with another record resident in another table?
     *
     * Index blade files (displaying listings of records) use this method. Usually, with Eloquent, we
     * inject the models, and then use a statement such as "$this->model->find($id)->category->sortBy($sortBy);"
     *
     * @param   string      $relatedModelName    Name of Model that is related to the table
     * @param   int         $id                  ID of the record associated wtih $relatedModelName
     * @param   sortBy      $sortBy              Sort by this column in ASC order
     * @return  collection
     */
    public function getLookupTableRecordsAssociatedByParentId($relatedModelName, $id, $sortBy = "title")
    {
        return $this->model->find($id)->$relatedModelName->sortBy($sortBy);
    }


    /**
    * Return a new instance of the model.
    *
    * @return object
    */
    public function newModelInstance()
    {
        //return new $this->model;
    }

    /**
     * Do a "SELECT * FOR table" with optional "WHERE enabled = 1"
     *
     * @param  string   $relatedTableName   The name of the related table
     * @return eloquent
     */
    public function selectOptionWhereClauseEnabledField($relatedTableName)
    {
        if ($relatedTableName == "users")
        {
            $orderBy = "name";
        } else {
            $orderBy = "title";
        }

        if (Schema::hasColumn($relatedTableName, 'enabled'))
        {
            return DB::table($relatedTableName)->where('enabled', '=', 1)->orderby($orderBy)->get();
        } else {
            return DB::table($relatedTableName)->orderby($orderBy)->get();
        }
    }


    ///////////////////////////////////////////////////////////////////
    //////////            FRONT END METHODS              //////////////
    ///////////////////////////////////////////////////////////////////

    /**
     * @param  string   $slug
     * @return eloquent
     */
    public function findEnabledPostBySlug($slug)
    {
        $todaysDate = DatesHelper::todaysDateSetToLocalTime();
        $post = $this->model
            ->where('slug', $slug)
            ->where('enabled', 1)
            ->where('publish_on', '<=', $todaysDate)
            ->first()
            ;

        return $post;
    }
}