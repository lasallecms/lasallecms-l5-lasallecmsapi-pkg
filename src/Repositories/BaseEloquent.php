<?php namespace Lasallecms\Lasallecmsapi\Repositories;

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

use Lasallecms\Lasallecmsapi\Contracts\BaseRepository;

Use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BaseEloquent implements BaseRepository {

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




    /*
     * Assign the slug method of the post object from the admin post form's input
     *
     * @param   object   $post   Post object
     * @param   array    $data   Sanitized input from admin post form
     * @return  object
     */
    public function assignSlug($post, $data)
    {
        // THIS IS NOT A THOROUGH METHOD FOR PRODUCING A SLUG
        // Assumption: user is not going to mess around with slugs
        // Assumption: new post, user leaves slug blank. Titles never the same!

        // https://github.com/cviebrock/eloquent-sluggable
        // https://github.com/martinbean/laravel-sluggable-trait

        // If excerpt is empty, then use the content to populate the excerpt field
        if ($data['slug'] == "")
        {
            // create the slug from Laravel's Str::slug() method
            $post->slug = Str::slug($data['title']);

        } else {
            $rowCount = DB::table('posts')
                ->where('slug', $data['slug'])
                ->count();

            // yup, we assume that the slug in the POSTS table is from the current post!
            if ($rowCount == 1)
            {
                $post->slug = $data['slug'];
            } else {
                // append a number and hope the new slug is not being used in the POSTS table
                ++$rowCount;
                $post->slug = $data['slug'].$rowCount;
            }

        }




        return $post;
    }



    /*
     * Assign the publish method of the post object from the admin post form's input
     *
     * @param   object   $post   Post object
     * @param   array    $data   Sanitized input from admin post form
     * @return  object
     */
    public function assignExcerpt($post, $data)
    {
        // If excerpt is empty, then use the content to populate the excerpt field
        if ($data['excerpt'] == "")
        {
            // grab the content field from the form's input
            $content = $post->content;

        } else {
            // grab the exerpt field from the form's input
            $content = $post->excerpt;
        }

        // remove the html tags
        $content = strip_tags($content);

        // remove leading and trailing blanks
        $content = trim($content);

        // remove any "&nbsp;" that I am known to sneak into my posts
        $content = str_replace("&nbsp;", "", $content);

        // use the first 100 chars for the excerpt
        $post->excerpt = substr($content, 0, 100);

        return $post;
    }


    /*
    * Assign the publish method of the post object from the admin post form's input
    *
    * The db field type = tinyint(1) and the formfield is checkmark
    *
    * @param   $post   Post object
    * @param   $data   Sanitized input from admin post form
    * @return object
    */
    public function assignPublish($post, $data)
    {
        // http://stackoverflow.com/questions/20168769/laravel-4-how-to-test-if-a-checkbox-is-checked
        if ($data['publish'] === 'yes')
        {
            // checked
            $post->publish = 1;
        } else {
            // unchecked
            $post->publish = 0;
        }

        return $post;
    }


    /*
     * Assign the created_at and updated_at methods of the post object from the admin post form's input
     *
     * @param   $post   Post object
     * @param   $data   Sanitized input from admin post form
     * @return object
     */
    public function assignTimestampsForCreate($post)
    {
        // If the created_at date is not today, then gotta make updated_at = created_at
        // Probably should put this in a sanitizer
        // http://forumsarchive.laravel.io/viewtopic.php?id=14462
        // if created_at is blank
        if
        (
            ($post->created_at == "0000-00-00 00:00:00")
            || ($post->created_at == "")
            || ($post->created_at == "-0001-11-30 00:00:00")
        )
        {
            // "use Carbon\Carbon"
            $post->created_at = Carbon::now();
        }
        // updated_at is populated by now() by default but really want it to be same as created_at
        $post->updated_at = $post->created_at;

        return $post;
    }



    /*
     * Assign the created_at and updated_at methods of the post object from the admin post form's input
     *
     * @param   $post   Post object
     * @param   $data   Sanitized input from admin post form
     * @return object
     */
    public function assignTimestampsForUpdate($post, $data)
    {
        // created_at field is untouched, so no processing here!
        // There's a hidden field in the form to send created_at through the command bus mill


        switch ($data['updated_at'])
        {
            case "0000-00-00 00:00:00":
                $post->updated_at = Carbon::now();
                break;

            case "":
                $post->updated_at = Carbon::now();
                break;

            case "-0001-11-30 00:00:00":
                $post->updated_at = Carbon::now();
                break;

            default:
                // user manually set an updated_at date in the form
                $post->updated_at = $data['updated_at'];
                break;
        }

        return $post;
    }



    ///////////////////////////////////////////////////////////////////
    ///////////////////////////  LOCK FIELDS  /////////////////////////
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




}