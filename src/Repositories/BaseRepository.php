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
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

// Laravel classes
use Illuminate\Container\Container as Container;
use Illuminate\Support\Str;

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
     *
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


    /*
     * Display all the records ordered by publish_on, title, DESC
     *
     * @return collection
     */
    public function allRecordOrderbyPublishonTitleDesc()
    {
        return $this->model->orderBy('publish_on', 'title', 'DESC')->get();
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
        $allowedUserGroupsForAllActions = $this->model->allowed_user_groups;

        //http://laravel.com/docs/4.2/helpers#arrays
        return array_flatten( array_pluck($allowedUserGroupsForAllActions, $action) );
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
        return DB::table('groups')->where('id', $group_id)->value('title');
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
     * For Lookup Tables
     *
     * @param   int  $id  Lookup Table ID
     * @return  array
     */
    public function foreignKeyChecks($id)
    {
        return $this->model->foreignKeyCheck($id);
    }


    /*
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

    /*
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
            'description'      => 'min:4',
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
            'description'      => 'min:4',
            'enabled'          => 'boolean',
        ];
    }



    ///////////////////////////////////////////////////////////////////
    ////////////////    PREPARE FOR PERSIST     ///////////////////////
    ///////////////////////////////////////////////////////////////////

    /*
     * Prepare input data for save
     *
     * Basically ignoring the sanitizing that has already been applied, in the interests
     * of being thorough
     *
     * @param  array   $data  The sanitized input data array
     * @return array
     */
    public function washDataForPersist($data)
    {
        $fields = $data['field_list'];

        foreach ($fields as $field)
        {
            // If the "persist_wash" element is empty, then give it a value so the IF statements in this method work
            if ( empty($field['persist_wash']) ) $field['persist_wash'] = "empty";

            // Not all title fields are of type "varchar"
            if ( (($field['name'] == "title") && ($field['type'] == "varchar") ) || ($field['persist_wash'] == "title"))
            {
                $data[$field['name']] = $this->prepareTitleForPersist($data[$field['name']]);
            }

            if ( $field['name'] == "slug" )
            {
                $data['slug'] = $this->prepareSlugForPersist($data['title'], $data['slug']);
            }

            if ( $field['name'] == "canonical_url" )
            {
                $data['canonical_url'] = $this->prepareCanonicalURLForPersist($data['slug']);
            }

            if (( $field['name'] == "url" ) || ($field['persist_wash'] == "url"))
            {
                $data[$field['name']] = $this->prepareURLForPersist($data[$field['name']]);
            }

            if (( $field['name'] == "content") || ($field['persist_wash'] == "content"))
            {
                $data[$field['name']] = $this->prepareContentForPersist($data[$field['name']]);
            }

            if (($field['name'] == "description") || ($field['persist_wash'] == "description"))
            {
                $data[$field['name']] = $this->prepareDescriptionForPersist($data[$field['name']]);
            }

            if ( $field['name'] == "excerpt" )
            {
                $data['excerpt'] = $this->prepareExcerptForPersist($data['excerpt'], $data['content']);
            }

            if ($field['name'] == "meta_description")
            {
                $data['meta_description'] = $this->prepareMetaDescriptionForPersist($data['meta_description'], $data['excerpt']);
            }

            if ( $field['name'] == "featured_image" )
            {
                $data['featured_image'] = $this->prepareFeaturedImageForPersist($data['featured_image']);
            }

            if (($field['name'] == "enabled") || ($field['persist_wash'] == "enabled"))
            {
                $data[$field['name']] = $this->prepareEnabledForPersist($data[$field['name']]);
            }

            // publish_on is a not nullable field
            if (($field['name'] == "publish_on") || ($field['persist_wash'] == "publish_on"))
            {
                $data[$field['name']] = $this->preparePublishOnForPersist($data[$field['name']]);
            }

            // birthday is nullable!
            // publish_on is a not nullable field
            if (($field['name'] == "birthday") || ($field['persist_wash'] == "birthday"))
            {
                $data[$field['name']] = $this->prepareBirthdayForPersist($data[$field['name']]);
            }


            if (($field['name'] == "email") || ($field['persist_wash'] == "email") || ($field['type'] == "email") )
            {
                $data[$field['name']] = $this->prepareEmailForPersist($data[$field['name']]);
            }

            if (($field['name'] == "composite_title") || ($field['type'] == "composite_title"))
            {
                $data[$field['name']] = $this->prepareCompositeTitleForPersist($field['fields_to_concatenate'], $data);
            }

            if ($field['type'] == "related_table")
            {
                $data[$field['name']] = $this->prepareRelatedTableForPersist($field, $data[$field['name']]);
            }
        }

        if ($data['crud_action'] == "create")
        {
            $data['created_at'] = Carbon::now();
            $data['created_by'] = Auth::user()->id;

            $data['updated_at'] = Carbon::now();
            $data['updated_by'] = Auth::user()->id;

        } else {
            //blank
        }

        return $data;
    }

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
     * @param  string  $descriptions
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

            // wash the title
            $title = html_entity_decode($title);
            $title = strip_tags($title);
            $title = filter_var($title, FILTER_SANITIZE_STRING);
            // remove the encoded blank chars
            $title = str_replace("\xc2\xa0",'',$title);
            // remove encoded apostrophe
            $title = str_replace("&#39;",'',$title);
            $title = trim($title);

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

        // remove encoded apostrophe
        $slug = str_replace("&#39;",'',$slug);

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
     * Wash URL for persist.
     *
     * Does *not* test for a .com or .ca or other TLD
     *
     * @param  text  $url
     * @return text
     */
    public function prepareURLForPersist($url)
    {
        // NO, sometimes want to list the URL simply as a TITLE

        /*
        if (substr($url, 0, 7 ) == "http://") return $url;

        if (substr($url, 0, 8 ) == "http://") return $url;

        $washedUrl  = "http://";
        $washedUrl .= $url;
        */

        return $url;
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
     * This is *NOT* a NULLABLE field, so we canNOT return "null".
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
     * Transform birthday for persist.
     *
     * This is a NULLABLE field, so we can return "null".
     *
     * @param  datetime  $birthdate
     * @return datetime
     */
    public function prepareBirthdayForPersist($birthdate)
    {
        if
        (
            ($birthdate == "0000-00-00 00:00:00")
            || ($birthdate == "")
            || ($birthdate == "-0001-11-30 00:00:00")
            || (!$birthdate)
        )
        {
            // the date field is nullable, so return null
            return null;
        }

        return $birthdate;
    }

    /*
     * Transform emailn for persist.
     *
     * @param  string   $email
     * @return email
     */
    public function prepareEmailForPersist($email)
    {
        // leaving this code here for reference only

        // http://stackoverflow.com/questions/7290674/php-is-filter-sanitize-email-pointless

        /*
        $clean_email = filter_var($email,FILTER_SANITIZE_EMAIL);

        if ($email == $clean_email && filter_var($email,FILTER_VALIDATE_EMAIL))
        {
            // blank on purpose
        }
        */

        return $email;
    }

    /*
     * Concatenate fields for the composite Title field
     *
     * @param  array    $fieldsToConcatenate
     * @param  array    $data
     * @return string
     */
    public function prepareCompositeTitleForPersist($fieldsToConcatenate, $data)
    {
        $composite_title = "";

        // count to determine spacing between fields
        $count = count($fieldsToConcatenate);
        $i = 1;


        foreach ($fieldsToConcatenate as $fieldToConcatenate)
        {
            // If the field is blank, then skip the concatenation.
            // Eg: The field "street2" is blank
            if (($data[$fieldToConcatenate] == "") || (!$data[$fieldToConcatenate]) || (empty($data[$fieldToConcatenate])))
            {
                // blank on purpose --> yeah, I'm leaving it this way 'cause three months from now I'll actually
                //                      understand what I was thinking on the wrong side of midnight on May 27th, 2015!

            } else {

                $composite_title .= $data[$fieldToConcatenate];

                if ($i < $count) $composite_title .= " ";
            }
        }
        return $composite_title;
    }

    /*
     * Prepare foreign key field for persist.
     *
     * This is for "one" relationships only, where there is an actual field for the
     * related table's ID in the primary table.
     *
     * Basically, the purpose here is to set the data to "null" when there is no value, and
     * the field is nullable.
     *
     * @param  array    $fields
     * @param  array    $data
     * @return mixed
     */
    public function prepareRelatedTableForPersist($field, $data)
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
            $data = null;
        }

        return $data;
    }


    ///////////////////////////////////////////////////////////////////
    ////////////         PERSIST: CREATE/INSERT           /////////////
    ///////////////////////////////////////////////////////////////////

    /*
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
        $modelInstance->created_by       = $data['created_by'] = Auth::user()->id;

        $modelInstance->updated_at       = $data['updated_at'] = Carbon::now();
        $modelInstance->updated_by       = $data['updated_by'] = Auth::user()->id;

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


    /*
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
            $relatedRepository = new BaseRepository();

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

    /*
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


    /*
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

    /*
     * DELETE
     *
     * @param  int  $id
     * @return bool
     */
    public function destroyRecord($id)
    {
        return $this->getDestroy($id);
    }



    ///////////////////////////////////////////////////////////////////
    ////////////         HTML DROPDOWNS METHODS           /////////////
    ///////////////////////////////////////////////////////////////////

    /*
     * Determine which of these four SELECT forms to render:
     *  * single select from related table for create
     *  * single select from related table, with existing select, for update
     *
     *  * multiple selects from related table for create
     *  * multiple selects from related table, with existing selects, for update
     *
     * Called from blade template files.
     *
     * @param  array   $field                Primary table's Field array
     * @param  string  $action               Create or update
     * @param  int     $id                   Id of primary table
     *
     * @return void
     */
    public function determineSelectFormFieldToRenderFromRelatedTable($field, $action, $id=null )
    {
        if ($action == "create")
        {
            // related_pivot_table == false ==> one-to-one relationship, therefore "single"!
            if (empty($field['related_pivot_table']))
            {
                return $this->singleSelectFromRelatedTableCreate($field);

            } else {
                return $this->multipleSelectFromRelatedTableCreate($field);
            }
        }

        // action = "update"

        // related_pivot_table == false ==> one-to-one relationship, therefore "single"!
        if (!$field['related_pivot_table'])
        {
            return $this->singleSelectFromRelatedTableUpdate($field, $id);

        } else {
            return $this->multipleSelectFromRelatedTableUpdate($field['related_table_name'], $field['related_model_class'], $id);
        }
    }

    /*
     * Construct a dropdown with multiple selects from the related table.
     *
     * YES, THIS WOULD NORMALLY BE IN MY HELPERS PACKAGE, BUT THE "EDIT" VERSION BELOW
     * NEEDS THE REPOSITORY. I WANT TO KEEP THE "CREATE" AND "EDIT" METHODS TOGETHER
     *
     * @param  array   $field         Field array
     * @return string
     */
    public function singleSelectFromRelatedTableCreate($field)
    {
        // Get the records
        $records = $this->selectOptionWhereClauseEnabledField($field['related_table_name']);

    if (empty($records))
        {
            $html  = '<div class="alert alert-warning" role="alert">';

            if ($field['alternate_form_name'])
            {
                $modelName = $field['alternate_form_name'];
            } else {
                $modelName = $field['related_model_class'];
            }

            $html .= "<strong>There are no ".strtolower($modelName)." records to associate with. </strong>";
            $html .= '</div>';

            return $html;
        }


        // Initiatize the html select tag
        $html = "";
        $html .= '<select name="'.$field['name'].'" id="'.$field['name'].'" size="10" class="form-control">';

        // Construct the <option></option> tags for ALL tags in the tags table
        foreach ($records as $record)
        {
            $html .= '<option ';
            $html .= 'value="';
            $html .= $record->id;
            $html .= '">';

            if ($field['related_table_name'] == "users")
            {
                $html .= $record->name;
            } else {
                $html .= $record->title;
            }

            $html .= '</option>"';
        }
        $html .= '</select>';

        return $html;
    }

    /*
     * Construct a dropdown with multiple selects from the related table,
     * highlighting what is already selected.
     *
     * YES, THIS WOULD NORMALLY BE IN MY HELPERS PACKAGE, BUT THE "CREATE" VERSION ABOVE
     * NEEDS THE REPOSITORY. I WANT TO KEEP THE "CREATE" AND "EDIT" METHODS TOGETHER
     *
     * @param  array   $field         Field array
     * @param  int     $id            Id of primary table
     * @return string
     */
    public function singleSelectFromRelatedTableUpdate($field, $id)
    {
        // Get the related records
        $relatedTableRecords = $this->selectOptionWhereClauseEnabledField($field['related_table_name']);


        if (empty($relatedTableRecords))
        {
            $html  = '<div class="alert alert-warning" role="alert">';

            if ($field['alternate_form_name'])
            {
                $modelName = $field['alternate_form_name'];
            } else {
                $modelName = $field['related_model_class'];
            }

            $html .= "<strong>There are no ".strtolower($modelName)." records to associate with. </strong>";
            $html .= '</div>';

            return $html;
        }

        $selectedIdOFTheRelatedTable =  $this->getFind($id);

        // Initiatize the html select tag
        $html = "";
        $html .= '<select name="'.$field['name'].'" id="'.$field['name'].'" size="10" class="form-control">';

        // Construct the <option></option> tags for ALL tags in the tags table
        foreach ($relatedTableRecords as $relatedTableRecord)
        {
            // If this related record is attached to the primary record, then SELECTED it
            if ( $selectedIdOFTheRelatedTable->$field['name'] == $relatedTableRecord->id)
            {
                $selected = ' selected="selected" ';
            } else {
                $selected = "";
            }

            $html .= '<option ';
            $html .= $selected;
            $html .= 'value="';
            $html .= $relatedTableRecord->id;
            $html .= '">';

            if ($field['related_table_name'] == "users")
            {
                $html .= $relatedTableRecord->name;
            } else {
                $html .= $relatedTableRecord->title;
            }

            $html .= '</option>"';
        }
        $html .= '</select>';

        return $html;
    }



    /*
     * Construct a dropdown with multiple selects from the related table.
     *
     * YES, THIS WOULD NORMALLY BE IN MY HELPERS PACKAGE, BUT THE "EDIT" VERSION BELOW
     * NEEDS THE REPOSITORY. I WANT TO KEEP THE "CREATE" AND "EDIT" METHODS TOGETHER
     *
     * @param  array    $field         Field array
     * @return string
     */
    public function multipleSelectFromRelatedTableCreate($field)
    {
        // Get the records
        $records = $this->selectOptionWhereClauseEnabledField($field['related_table_name']);

        if (empty($records))
        {
            $html  = '<div class="alert alert-warning" role="alert">';
            $html .= "<strong>There are no ".strtolower($field['related_model_class'])." records to associate with. </strong>";
            $html .= '</div>';

            return $html;
        }

        // Initiatize the html select tag
        $html = "";
        $html .= '<select name="'.$field['related_table_name'].'[]" id="'.$field['related_table_name'].'" size="10" class="form-control" multiple>';

        // Construct the <option></option> tags for ALL tags in the tags table
        foreach ($records as $record)
        {
            $html .= '<option ';
            $html .= 'value="';
            $html .= $record->id;
            $html .= '">';
            $html .= $record->title;
            $html .= '</option>"';
        }
        $html .= '</select>';

        return $html;
    }

    /*
     * Construct a dropdown with multiple selects from the related table,
     * highlighting what is already selected.
     *
     * YES, THIS WOULD NORMALLY BE IN MY HELPERS PACKAGE, BUT THE "CREATE" VERSION ABOVE
     * NEEDS THE REPOSITORY. I WANT TO KEEP THE "CREATE" AND "EDIT" METHODS TOGETHER
     *
     * @param  string  $relatedTableName     Name of the related table
     * @param  string  $relatedModelClass    Name of the related model class
     * @param  int     $id                   Id of primary table
     * @return string
     */
    public function multipleSelectFromRelatedTableUpdate($relatedTableName, $relatedModelClass, $id)
    {
        // Get the related records
        $relatedTableRecords = $this->selectOptionWhereClauseEnabledField($relatedTableName);

        if (empty($relatedTableRecords))
        {
            $html  = '<div class="alert alert-warning" role="alert">';
            $html .= "<strong>There are no ".strtolower($relatedModelClass)." records to associate with. </strong>";
            $html .= '</div>';

            return $html;
        }

        // Create an array of tag IDs that are currently attached to the post
        $relatedTableRecordsAssociatedWithThisParentId = [];

        // Find the related records for the parent
        $allRelatedTableRecordsByParentId = $this->getLookupTableRecordsAssociatedByParentId($relatedModelClass, $id, "title");

        foreach ($allRelatedTableRecordsByParentId as $relatedTableRecordByParentId)
        {
            $relatedTableRecordsAssociatedWithThisParentId[] = $relatedTableRecordByParentId->id;
        }

        // Initiatize the html select tag
        $html = "";
        $html .= '<select name="'.$relatedTableName.'[]" id="'.$relatedTableName.'" size="6" class="form-control" multiple>';

        // If this related record is attached to the primary record, then SELECTED it
        foreach ($relatedTableRecords as $relatedTableRecord)
        {
            // If this tag is attached to the post, then SELECTED it
            if ( in_array($relatedTableRecord->id, $relatedTableRecordsAssociatedWithThisParentId) )
            {
                $selected = ' selected="selected" ';
            } else {
                $selected = "";
            }

            $html .= '<option ';
            $html .= $selected;
            $html .= 'value="';
            $html .= $relatedTableRecord->id;
            $html .= '">';
            $html .= $relatedTableRecord->title;
            $html .= '</option>"';
        }
        $html .= '</select>';

        return $html;
    }



    ///////////////////////////////////////////////////////////////////
    ////////////            MISC METHODS              /////////////////
    ///////////////////////////////////////////////////////////////////

    /*
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


    /*
    * Return a new instance of the model.
    * For CREATE
    *
    * @return object
    */
    public function newModelInstance()
    {
        return new $this->model;
    }

    /*
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
}