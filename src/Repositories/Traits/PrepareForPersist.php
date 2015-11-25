<?php

namespace Lasallecms\Lasallecmsapi\Repositories\Traits;

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

// Laravel facades
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Third party classes
use Carbon\Carbon;

/**
 * Class PrepareForPersist
 *
 * Trait made for specific use in Lasallecms\Lasallecmsapi\Repositories\BaseRepository.php
 *
 * @package Lasallecms\Lasallecmsapi\Repositories
 */
trait PrepareForPersist
{
    /**
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
                // we need to send over the record ID (basically, the posts ID) to properly determine if this slug exists
                if (empty($data['id'])) $data['id'] = false;

                $data['slug'] = $this->prepareSlugForPersist($data['title'], $data['slug'], $data['id']);
            }

            if ( $field['name'] == "canonical_url" )
            {
                $data['canonical_url'] = $this->prepareCanonicalURLForPersist($data['slug']);
            }


            // The way things have evolved towards version 1.0, the "URL" type does not get pre-washed for persist.
            // The reason is that, after all, want to just use the URL in a title field, so do *not* want "http://"
            //
            // However, whilst putting my Knowledge Base package together, I do need the URL wash. So... what
            // I am doing is creating a "persist_wash" called "link", which actually performs the needed "prepareURLForPersist".
            // The prepareURLForPersis method is on line 890 (ish!)
            if (( $field['name'] == "url" ) || ($field['persist_wash'] == "url"))
            {
                // Ok, not actually doing this pre-wash.
                //$data[$field['name']] = $this->prepareURLForPersist($data[$field['name']]);
            }
            if (( $field['name'] == "link" ) || ($field['persist_wash'] == "link"))
            {
                // yes, definitely doing this pre-wash!
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


    /**
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


    /**
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


    /**
     * Prepare slug for persist.
     *
     * @param  text  $title
     * @param  text  $slug
     * @param  int   $id        The id of the record (eg, of the posts table) that is currently being edited
     * @return text
     */
    public function prepareSlugForPersist($title, $slug, $id=false)
    {
        $separator = '-';

        if ($slug == "")
        {
            // No slug.. so this is a "create" form; or, the slug was deleted accidentally and so needs to be regenerated

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

            // well, is another record using this slug already? Let's return a record count, so we can use the count number...
            $rowCount = $this->doesSlugAlreadyExist($slug);

            if ($rowCount > 0)
            {
                // yes, this slug does exist already, so let's append this slug to make it different from what already exists.
                ++$rowCount;
                return $slug.$rowCount;
            }

            // no, this slug does not yet exist, so let's use it as-is...
            return $slug;
        }


        // Ah, so a slug was entered. So coming from an "edit" form...

        // remove the encoded blank chars
        $slug = str_replace("\xc2\xa0",'',$slug);

        // remove encoded apostrophe
        $slug = str_replace("&#39;",'',$slug);

        $slug = trim($slug);
        $slug = strtolower($slug);
        $slug = strip_tags($slug);
        $slug = preg_replace('!['.preg_quote($separator).'\s]+!u', $separator, $slug);


        // if this slug is a different slug manually entered into the edit form, then process it further
        if (!$this->isManuallyChangedSlugInEditForm($slug, $id)) {
            return $slug;
        }

        // so this slug does belong to the existing ID, but is different than the slug in the database...

        // well, is another record using this slug already? Let's return a record count, so we can use the count number...
        $rowCount = $this->doesSlugAlreadyExist($slug);

        if ($rowCount > 0)
        {
            // yes, this slug does exist already, so let's append this slug to make it different from what already exists.
            ++$rowCount;
            return $slug.$rowCount;
        }

        // no, this slug does not yet exist, so let's use it as-is...
        return $slug;
    }


    /**
     * Was the slug changed in the edit form?
     *
     * @param  text  $slug
     * @param  int   $id        The id of the record (eg, of the posts table) that is currently being edited
     * @return bool
     */
    public function isManuallyChangedSlugInEditForm($slug, $id=false)
    {
        // If there is no $id, then there's nothing to figure out!
        if (!$id) return false;

        $record = DB::table($this->model->table)
            ->where('id', $id)
            ->first()
        ;

        if ($record->slug == $slug) {

            // The slug entered into the form is the same slug that is already in the database, so no change...
            return false;

        } else {

            // The slug entered into the form is different than the slug in the database, so yes it has changed...
            return true;
        }
    }


    /**
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


    /**
     * Transform canonical_url for persist.
     *
     * @param  text  $slug
     * @return text
     */
    public function prepareCanonicalURLForPersist($slug)
    {
        $baseURL = rtrim(config('app.url'), "/");

        if ($this->model->table == "posts") $type = "blog";


        // July 15, 2015: do *NOT* want type!

        //return $baseURL.'/'.$type.'/'.$slug;
        return $baseURL.'/'.$slug;
    }


    /**
     * Wash URL for persist.
     *
     * Does *not* test for a .com or .ca or other TLD
     *
     * @param  text  $url
     * @return text
     */
    public function prepareURLForPersist($url)
    {
        if (substr($url, 0, 7 ) == "http://") return $url;

        if (substr($url, 0, 8 ) == "https://") return $url;

        $washedUrl  = "http://";
        $washedUrl .= $url;

        return $url;
    }


    /**
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


    /**
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


    /**
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


    /**
     * Transform featured_image for persist.
     *
     * @param  text  $featured_image
     * @return text
     */
    public function prepareFeaturedImageForPersist($featured_image)
    {
        return $featured_image;
    }


    /**
     * Transform enabled for persist.
     *
     * @param  bool  $enabled
     * @return bool
     */
    public function prepareEnabledForPersist($enabled) {
        if (($enabled == "") || $enabled == 0) return 0;
        return 1;
    }


    /**
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


    /**
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


    /**
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


    /**
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


    /**
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
}