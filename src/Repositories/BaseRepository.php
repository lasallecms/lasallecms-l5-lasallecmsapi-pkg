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
 * @version    1.0.0
 * @link       http://LaSalleCMS.com
 * @copyright  (c) 2015, The South LaSalle Trading Corporation
 * @license    http://www.gnu.org/licenses/gpl-3.0.html
 * @author     The South LaSalle Trading Corporation
 * @email      info@southlasalle.com
 *
 */

/*
 * This is the common base repository for all LaSalle Software, except LaSalleMart
 */

// Laravel facades
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

// Laravel classes
use Illuminate\Container\Container as Container;

// Third party classes
use Carbon\Carbon;

class BaseRepository
{
    ///////////////////////////////////////////////////////////////////
    //////////////////////       PROPERTIES       /////////////////////
    ///////////////////////////////////////////////////////////////////

    /*
     * @var Illuminate\Container\Container
     */
    protected $app;

    /*
     * @var  namespace and class of relevant model
     */
    protected $model;


    ///////////////////////////////////////////////////////////////////
    /////////////////////       CONSTRUCTOR       /////////////////////
    ///////////////////////////////////////////////////////////////////

    /*
     * Inject a new instance of the container in order to inject the relevant model.
     */
    public function __construct()
    {
        $this->app   = new Container;
    }


    ///////////////////////////////////////////////////////////////////
    //////////////////////    MODEL INJECTION     /////////////////////
    ///////////////////////////////////////////////////////////////////

    /*
     *
     * Inject the container, then use the container to inject the model object
     * "Resolve something out of the container"
     * http://laravel.com/docs/5.0/container#basic-usage
     *
     * Called from controller
     *
     * @param  string   $modelNamespaceClass  The model's concatenated namespace and class name
     */
    public function injectModelIntoRepository($modelNamespaceClass)
    {
        $this->model = $this->app->make($modelNamespaceClass);
    }


    ///////////////////////////////////////////////////////////////////
    ////       LARAVEL MODEL METHODS IN REPOSITORY FORM       /////////
    ///////////////////////////////////////////////////////////////////

    /*
     * Return entire collection
     *
     *  @return eloquent
     */
    public function getAll()
    {
        return $this->model->all();
    }

    /*
     * Return specific model
     *
     * @param id         Post ID
     * @return eloquent
     */
    public function getFind($id)
    {
        return $this->model->findOrfail($id);
    }


    /*
     * Create model
     *
     * @param  data     Input data
     * @return eloquent
     */
    public function getCreate($data)
    {
        return $this->model->create($data);
    }


    /*
     * Store model
     *
     * @param  data     Input data
     * @return eloquent
     */
    public function getStore($data)
    {
        return $this->model->store($data);
    }


    /*
    * Save model
    *
    * @return eloquent
    */
    public function getSave()
    {
        return $this->model->save();
    }


    /*
    * Update model
    *
    * @param  data     array  Input data
    * @return eloquent
    */
    public function getUpdate($data)
    {
        return $this->model->update($data);
    }


    /*
     * Delete a model
     *
     * @param id         Post ID
     * @return eloquent
     */
    public function getDestroy($id)
    {
        return $this->model->destroy($id);
    }


    /*
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


    ///////////////////////////////////////////////////////////////////
    ////////////////////      USER GROUPS         /////////////////////
    ///////////////////////////////////////////////////////////////////

    /*
     * Is the user allowed to do an action
     *
     * @param   string   $action   Generally: index, create, store, edit, insert, destroy
     * @return  bool
     */
    public function isUserAllowed($action)
    {
        $this->groupIdTitle(1);

        // Get the user's group.
        // Returns array of objects.
        $userGroups = $this->userBelongsToTheseGroups();

        // Array of allowed groups from the model
        $allowedGroups = $this->allowedGroupsForThisActionByModel($action);

        // Cycle through all the allowed groups, to see if the user belongs to one of these allowed groups.
        // One match is all it takes!
        foreach ($allowedGroups as $allowedGroup)
        {
            // Cycle through all the groups the user belongs to
            foreach ($userGroups as $userGroup)
            {
                //debug
                //echo "<br>".$this->groupIdTitle($userGroup->group_id)." and ".$allowedGroup;
                if (
                    (strtolower($this->groupIdTitle($userGroup->group_id)))
                    ==
                    (strtolower($allowedGroup))
                ) return true;
            }
        }
        return false;
    }

    /*
     * What groups does the model specify are allowed to do the controller's action.
     * Put another way, what group can do the index() for a specific controller?
     * This array resides in the model class.
     *
     * @param string   $action   A particular controller's action (method) -- just for that controller,
     *                                                                        *NOT* generically for all controllers!
     * @return array
     */
    public function allowedGroupsForThisActionByModel($action)
    {
        $allowedUserGroupsForAllActions = $this->model->getAllowedUserGroups();

        //http://laravel.com/docs/4.2/helpers#arrays
        return array_flatten( array_fetch($allowedUserGroupsForAllActions, $action) );
    }

    /*
     * What groups does the user belong?
     *
     * @return object
     */
    public function userBelongsToTheseGroups()
    {
        return DB::table('user_group')->where('user_id', '=', Auth::user()->id)->get();
    }

    /*
     * What is the title field for a given group_id, in the groups database table?
     *
     * @param  int   $group_id
     * @return string
     */
    public function groupIdTitle($group_id)
    {
        return DB::table('groups')->where('id', $group_id)->pluck('title');
    }



    ///////////////////////////////////////////////////////////////////
    ////////////////////      LOCK FIELDS         /////////////////////
    ///////////////////////////////////////////////////////////////////

    /*
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

    /*
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

    /*
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

    /*
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

    /*
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


    ///////////////////////////////////////////////////////////////////
    //////////////     Foreign Key Constraint       ///////////////////
    ///////////////////////////////////////////////////////////////////

    /*
     * Is a table record used in another table?
     *
     * @param   int  $id  Lookup Table ID
     * @return  int
     */
    public function foreignKeyChecks($id)
    {
        return $this->model->foreignKeyCheck($id);
    }


    ///////////////////////////////////////////////////////////////////
    ///////////////////////////  SANITATION   /////////////////////////
    ///////////////////////////////////////////////////////////////////
    /*
     * Get sanitation array for INSERT from model
     *
     * @return array
     */
    public function getSanitationRulesForCreate()
    {
        return $this->model->sanitationRulesForCreate;
    }

    /*
     * Get sanitation array for UPDATE from model
     *
     * @return array
     */
    public function getSanitationRulesForUpdate()
    {
        return $this->model->sanitationRulesForUpdate;
    }

    /*
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

    /*
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

    /*
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


    ///////////////////////////////////////////////////////////////////
    ///////////////////////////  VALIDATION   /////////////////////////
    ///////////////////////////////////////////////////////////////////
    /*
     * Get validation array for INSERT from model
     *
     * @return array
     */
    public function getValidationRulesForCreate()
    {
        return $this->model->validationRulesForCreate;
    }

    /*
     * Get validation array for UPDATE from model
     *
     * @return array
     */
    public function getValidationRulesForUpdate()
    {
        return $this->model->validationRulesForUpdate;
    }


    /*
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
            'description'      => 'min:11',
            'enabled'          => 'boolean',
        ];
    }

    /*
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
            'description'      => 'min:11',
            'enabled'          => 'boolean',
        ];
    }



    ///////////////////////////////////////////////////////////////////
    ////////////////    PREPARE FOR PERSIST     ///////////////////////
    ///////////////////////////////////////////////////////////////////

    /*
     * Transform title for persist.
     *
     * @param  text  $title
     * @return text
     */
    public function prepareTitleForPersist($title)
    {
        // Strip whitespace (or other characters) from the beginning and end of a string
        $transformedTitle = trim($title);

        // Strip HTML and PHP tags from a string
        $transformedTitle = strip_tags($transformedTitle);

        // Strip tags, optionally strip or encode special characters
        // http://php.net/manual/en/filter.filters.sanitize.php
        $transformedTitle = filter_var($transformedTitle, FILTER_SANITIZE_STRING);

        // Uppercase the first character of each word in a string
        $transformedTitle = ucwords($transformedTitle);

        return $transformedTitle;
    }

    /*
     * Transform description for persist.
     *
     * @param  text  $meta_description
     * @param  text  $excerpt
     * @return text
     */
    public function prepareDescriptionForPersist($description)
    {
        $description = html_entity_decode($description);
        $description = strip_tags($description);
        $description = filter_var($description, FILTER_SANITIZE_STRING);

        // remove the encoded blank chars
        $description = str_replace("\xc2\xa0",'',$description);

        $description = trim($description);
        return $description;
    }

    /*
     * Prepare slug for persist.
     *
     * @param  text  $title
     * @param  text  $slug
     * @return text
     */
    public function prepareSlugForPersist($title, $slug)
    {
        $separator = '-';

        if ($slug == "")
        {
            // Convert all dashes/underscores into separator
            $flip = $separator == '-' ? '_' : '-';

            $slug = preg_replace('!['.preg_quote($flip).']+!u', $separator, $title);

            // Remove all characters that are not the separator, letters, numbers, or whitespace.
            $slug = preg_replace('![^'.preg_quote($separator).'\pL\pN\s]+!u', '', mb_strtolower($slug));

            // Replace all separator characters and whitespace by a single separator
            $slug = preg_replace('!['.preg_quote($separator).'\s]+!u', $separator, $slug);

            $rowCount = $this->doesSlugAlreadyExist($slug);
            if ($rowCount > 0)
            {
                ++$rowCount;
                return $slug.$rowCount;
            }
            return $slug;
        }

        // remove the encoded blank chars
        $slug = str_replace("\xc2\xa0",'',$slug);

        $slug = trim($slug);
        $slug = strtolower($slug);
        $slug = strip_tags($slug);
        $slug = preg_replace('!['.preg_quote($separator).'\s]+!u', $separator, $slug);

        $rowCount = $this->doesSlugAlreadyExist($slug);

        if ($rowCount > 0)
        {
            ++$rowCount;
            return $slug.$rowCount;
        }
        return $slug;
    }

    /*
     * Does the slug already exist in the table?
     *
     * @param  text  $slug
     * @return int
     */
    public function doesSlugAlreadyExist($slug)
    {
        $rowCount = DB::table($this->model->table)
            ->where('slug',  $slug)
            ->count();

        if ($rowCount > 0) return $rowCount;
        return 0;
    }




    /*
     * Transform canonical_url for persist.
     *
     * @param  text  $slug
     * @return text
     */
    public function prepareCanonicalURLForPersist($slug)
    {
        $baseURL = rtrim(config('app.url'), "/");

        if ($this->model->table == "posts") $type = "blog";

        return $baseURL.'/'.$type.'/'.$slug;
    }

    /*
     * Transform content for persist.
     *
     * @param  text  $content
     * @return text
     */
    public function prepareContentForPersist($content)
    {
        $transformedContent = trim($content);
        return $transformedContent;
    }

    /*
     * Transform excerpt for persist.
     *
     * @param  text  $excerpt
     * @return text
     */
    public function prepareExcerptForPersist($excerpt="", $content)
    {
        $chars_to_excerpt = config('lasallecmsapi.how_many_initial_chars_of_content_field_for_excerpt');

        if ($excerpt == "")
        {
            $excerpt = $content;

            $excerpt = html_entity_decode($excerpt);
            $excerpt = strip_tags($excerpt);
            $excerpt = filter_var($excerpt, FILTER_SANITIZE_STRING);

            // remove the encoded blank chars
            $excerpt = str_replace("\xc2\xa0",'',$excerpt);

            $excerpt = trim($excerpt);
            $excerpt = mb_substr($excerpt, 0, $chars_to_excerpt).config('lasallecmsapi.append_excerpt_with_this_string');
            return $excerpt;
        }

        $excerpt = html_entity_decode($excerpt);
        $excerpt = strip_tags($excerpt);
        $excerpt = filter_var($excerpt, FILTER_SANITIZE_STRING);

        // remove the encoded blank chars
        $excerpt = str_replace("\xc2\xa0",'',$excerpt);

        $excerpt = trim($excerpt);
        $excerpt.config('lasallecmsapi.append_excerpt_with_this_string');

        return $excerpt;
    }

    /*
     * Transform meta_description for persist.
     *
     * @param  text  $meta_description
     * @param  text  $excerpt
     * @return text
     */
    public function prepareMetaDescriptionForPersist($meta_description="", $excerpt)
    {
        if ($meta_description == "") return $excerpt;

        $meta_description = html_entity_decode($meta_description);
        $meta_description = strip_tags($meta_description);
        $meta_description = filter_var($meta_description, FILTER_SANITIZE_STRING);

        // remove the encoded blank chars
        $excerpt = str_replace("\xc2\xa0",'',$excerpt);

        $excerpt = trim($excerpt);
        return $meta_description;
    }

    /*
     * Transform featured_image for persist.
     *
     * @param  text  $featured_image
     * @return text
     */
    public function prepareFeaturedImageForPersist($featured_image)
    {
        return $featured_image;
    }

    /*
     * Transform enabled for persist.
     *
     * @param  bool  $enabled
     * @return bool
     */
    public function prepareEnabledForPersist($enabled) {
        if (($enabled == "") || $enabled == 0) return 0;
        return 1;
    }

    /*
     * Transform publish_on for persist.
     *
     * @param  datetime  $publish_on
     * @return datetime
     */
    public function preparePublishOnForPersist($publish_on)
    {
        if
        (
            ($publish_on == "0000-00-00 00:00:00")
            || ($publish_on == "")
            || ($publish_on == "-0001-11-30 00:00:00")
        )
        {
            // "use Carbon\Carbon"
            return Carbon::now();
        }

        return $publish_on;
    }

    /*
     * Return a new instance of the model.
     * For CREATE
     */
    public function newModelInstance()
    {
        return new $this->model;
    }

}