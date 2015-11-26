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

/**
 * Class RepositorySpecificHTMLHelpers
 *
 * Trait made for specific use in Lasallecms\Lasallecmsapi\Repositories\BaseRepository.php
 *
 * @package Lasallecms\Lasallecmsapi\Repositories
 */
trait RepositorySpecificHTMLHelpers
{
    ///////////////////////////////////////////////////////////////////
    ////////////         HTML DROPDOWNS METHODS           /////////////
    ///////////////////////////////////////////////////////////////////

    /**
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


    /**
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

        $html .= $this->renderBootstratMultiselectPlugin( $field['name'], "single", count($records) );


        return $html;
    }


    /**
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

        $html .= $this->renderBootstratMultiselectPlugin( $field['name'], "single", count($relatedTableRecords) );

        return $html;
    }


    /**
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
        $html .= '<select name="'.$field['related_table_name'].'[]" id="'.$field['related_table_name'].'" size="10" class="form-control" multiple="multiple">';

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

        $html .= $this->renderBootstratMultiselectPlugin( $field['related_table_name'], "multiple", count($records) );

        return $html;
    }


    /**
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

        $html .= $this->renderBootstratMultiselectPlugin( $relatedTableName, "multiple", count($relatedTableRecords) );

        return $html;
    }


    /**
     * The HTML for the Bootstrap Multiselect jQuery plugin
     * https://github.com/davidstutz/bootstrap-multiselect
     * params on line 393, bootstrap-multiselect/dist/js/bootstrap-multiselect.js
     *
     * @param   $selectid          string    The id.  <select id="$selectid" ..>
     * @param   $singleOrMultiple  string    {single | multiple }
     *                                       Select only one option, or select one or more options
     * @param   $count             int       The number of options
     * @return  string
     */
    private function renderBootstratMultiselectPlugin($selectid, $singleOrMultiple = "single", $count)
    {
        $html = '<script type="text/javascript">';
        $html .= '$(document).ready(function() {';
        $html .= "$('#".$selectid."').multiselect(";
        $html .= "{";

        $html .= "nonSelectedText: 'Select...',";
        $html .= 'enableHTML: false,';
        $html .= "maxHeight: 200,";

        if ($count > 10) {
            $html .= "enableFiltering: true,";
            $html .= "enableCaseInsensitiveFiltering: false,";
            $html .= "enableFullValueFiltering: false,";
            $html .= "filterBehavior: 'text',";
            $html .= "filterPlaceholder: 'Search...',";
        }

        if ($singleOrMultiple != "single") {
            $html .= "includeSelectAllOption: true,";
            $html .= "includeSelectAllIfMoreThan: 3,";
            $html .= "delimiterText: ' | ',";
        }

        $html .= "buttonClass: 'btn btn-default'";

        $html .= "}";
        $html .= ");";
        $html .= '});';
        $html .= '</script>';

        return $html;
    }
}