<?php

namespace Lasallecms\Lasallecmsapi\FormProcessing;

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

// LaSalle Software classes
use Lasallecms\Helpers\HTML\HTMLHelper;
use Lasallecms\Helpers\Images\ImagesHelper;

// Laravel facades
use Illuminate\Support\Facades\Config;

/**
 * Process the featured image
 *
 * @author  Bob Bloom  <info@southlasalle.com>
 */
class FeaturedImageProcessing
{
    /**
     * @var Lasallecms\Helpers\HTML\HTMLHelper
     */
    protected $HTMLHelper;

    /**
     * @var Lasallecms\Helpers\Images\ImagesHelper
     */
    protected $ImagesHelper;

    public function __construct(HTMLHelper $HTMLHelper, ImagesHelper $imagesHelper)
    {
        $this->HTMLHelper   = $HTMLHelper;
        $this->ImagesHelper = $imagesHelper;
    }

    /**
     * Main featured image processing
     *
     * @param  array  $data   Form field data
     */
    public function process($data) {

        $featuredImageProcessing = [];


        // FEATURED_IMAGE_URL

        // is the featured image from an external URL?
        $useFeaturedImageUrl = $this->isFieldValueBlank($data['featured_image_url']);

        // if using the featured_image_url field, then validate it now
        if (
            ($useFeaturedImageUrl) &&
            ($this->validateFeaturedImageUrl($data['featured_image_url']) != "passed")
        )
        {
            // validation failed
            $featuredImageProcessing['validationMessage'] = $this->validateFeaturedImageUrl($data['featured_image_url']);
            return $featuredImageProcessing;
        }

        // if using the featured_image_url field, and it passes the validation, then make it the Featured Image
        if ($useFeaturedImageUrl) {
            $featuredImageProcessing['validationMessage'] = "passed";
            $featuredImageProcessing['featured_image']    = $data['featured_image_url'];
            return $featuredImageProcessing;
        }


        // FEATURED_IMAGE_UPLOAD

        // is the featured image from a local file upload?
        $useFeaturedImageUpload = $this->isFieldValueBlank($data['featured_image_upload']);

        // if using the featured_image_upload field, then validate it now
        if (
            ($useFeaturedImageUpload) &&
            ($this->validateFeaturedImageUpload($data['featured_image_upload']) != "passed")
        )
        {
            // validation failed
            $featuredImageProcessing['validationMessage'] = $this->validateFeaturedImageUpload($data['featured_image_upload']);
            return $featuredImageProcessing;

        }

        // if using the featured_image_upload field, and it passes the validation, then make it the Featured Image
        if ($useFeaturedImageUpload) {

            // Move the file from the tmp folder to the image folder
            $this->moveFile($data['featured_image_upload']);

            $featuredImageProcessing['validationMessage'] = "passed";
            $featuredImageProcessing['featured_image']    = $data['featured_image_upload'];
            return $featuredImageProcessing;
        }


        // FEATURED_IMAGE_SERVER

        // if using the featured_image_upload field, then validate it now
        if ($this->validateFeaturedImageServer($data['featured_image_server']) != "passed") {
            // validation failed
            $featuredImageProcessing['validationMessage'] = $this->validateFeaturedImageServer($data['featured_image_server']);
            return $featuredImageProcessing;
        }

        // if using the featured_image_upload field, and it passes the validation, then make it the Featured Image
        $featuredImageProcessing['validationMessage'] = "passed";
        $featuredImageProcessing['featured_image']    = $data['featured_image_server'];
        return $featuredImageProcessing;
    }



    ///////////////////////////////////////////////////////////////////
    /////////////          VALIDATION METHODS          ////////////////
    ///////////////////////////////////////////////////////////////////

    /**
     * Validate the featured_image_url field's data
     *
     * @param  string   $featuredImageUrl  The featured_image_url form field's value
     * @return string                      "passed", or an error message
     */
    public function validateFeaturedImageUrl($featuredImageUrl) {

        // must be a fully qualified URL
        if (!HTMLHelper::isHTTPorHTTPS($featuredImageUrl)) {
            return "Your external image URL is not fully qualified. Please ensure that your URL begins with http";
        }

        // must have acceptable file extension
        if (!$this->isImageFileExtensionKosher($featuredImageUrl)) {
            return "Your external image file is not an accepted image file type.";
        }

        return "passed";
    }

    /**
     * Validate the featured_image_upload field's data
     *
     * @param  string   $featuredImageUpload     The featured_image_upload form field's value
     * @return string                           "passed", or an error message
     */
    public function validateFeaturedImageUpload($featuredImageUpload) {

        // not acceptable file extension
        if (!$this->isImageFileExtensionKosher($featuredImageUpload)) {
            return "Your uploaded image file, ".$featuredImageUpload.",  is not an accepted image file type.";
        }

        // file already exists on the server
        if ($this->doesImageFileExistOnServer($featuredImageUpload)) {
            return "Your uploaded image file, ".$featuredImageUpload.", already exists on the server.";
        }

        // file upload failed
        if ( ! \Input::file('featured_image_upload')->isValid() ) {
            return "There were problems uploading your image file, ".$featuredImageUpload.".";
        }

        return "passed";
    }

    /**
     * Validate the featured_image_server field's data
     *
     * @param  string   $featuredImageServer     The featured_image_server form field's value
     * @return string                           "passed", or an error message
     */
    public function validateFeaturedImageServer($featuredImageServer) {

        // if there is no featured_image_server, then there is no featured image -- which is ok
        if ((!$featuredImageServer) || ($featuredImageServer == "") ) {
            return "passed";
        }

        // not acceptable file extension
        if (!$this->isImageFileExtensionKosher($featuredImageServer)) {
            return "The image file you selected on your server, ".$featuredImageServer.",  is not an accepted image file type.";
        }

        // file NOT already exists on the server
        if (!$this->doesImageFileExistOnServer($featuredImageServer)) {
            return "The image file you selected on your server, ".$featuredImageServer.", does NOT actually exist on the server.";
        }

        return "passed";
    }

    /**
     * Evaluate whether the field's data is blank
     *
     * @param  string  $field  The form field's value
     * @return bool
     */
    public function isFieldValueBlank($field) {
        if ($field) return true;
        return false;
    }

    /**
     * Is the image's extension allowed?
     *
     * @param  string   $filename         The image's filename
     * @return bool
     */
    public function isImageFileExtensionKosher($filename) {

        // must have acceptable file extension
        $imageFileExtension = $this->ImagesHelper->filenameWithExtensionOnly($filename);

        $haystack = Config::get('lasallecmsfrontend.acceptable_image_extensions_for_uploading');

        if (in_array(strtolower($imageFileExtension), $haystack)) {
            return true;
        }

        return false;
    }

    /**
     * Does the iamge file exist already on the server?
     *
     * @param  string   $filename         The image's filename
     * @return bool
     */
    public function doesImageFileExistOnServer($filename) {
        if (\File::exists(public_path() . "/" . Config::get('lasallecmsfrontend.images_folder_uploaded'). '/'. $filename)) {
            return true;
        }

        return false;
    }


    ///////////////////////////////////////////////////////////////////
    /////////////            MISC METHODS              ////////////////
    ///////////////////////////////////////////////////////////////////

    /**
     * Move the uploaded file from the tmp folder to its proper destination folder
     *
     * @param  string   $filename         The image's filename
     * @return null
     */
    public function moveFile($filename) {

        $destinationPath = public_path() . "/" . Config::get('lasallecmsfrontend.images_folder_uploaded');

        \Input::file('featured_image_upload')->move($destinationPath, $filename);
    }
}