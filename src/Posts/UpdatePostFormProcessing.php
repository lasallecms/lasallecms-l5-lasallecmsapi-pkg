<?php

namespace Lasallecms\Lasallecmsapi\Posts;

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


///////////////////////////////////////////////////////////////////
///            THIS IS A COMMAND HANDLER                        ///
///////////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////////////
///  NOTE: THE REPOSITORY IS THE BASE REPOSITORY, NOT A         ///
///  REPOSITORY SPECIFIC TO THE MODEL. THE REASON IS TO         ///
///  FACILITATE AUTOMATION OF ADMIN FORMS. YOU CAN ALWAYS       ///
///  DO A MODEL-SPECIFIC REPOSITORY IF NEED BE.                 ///
///////////////////////////////////////////////////////////////////


// LaSalle Software
use Lasallecms\Lasallecmsapi\Repositories\BaseRepository;
use Lasallecms\Lasallecmsapi\FormProcessing\BaseFormProcessing;
use Lasallecms\Lasallecmsapi\FormProcessing\FeaturedImageProcessing;
use Lasallecms\Lasallecmsapi\Events\SendPostToLaSalleCRMemailList;

// Laravel facades
use Illuminate\Support\Facades\Config;

/*
 * Process an existing record.
 *
 * FYI: BaseFormProcessing implements the FormProcessing interface.
 */
class UpdatePostFormProcessing extends BaseFormProcessing
{
    /*
     * Instance of repository
     *
     * @var Lasallecms\Lasallecmsapi\Repositories\BaseRepository
     */
    protected $repository;

    /**
     * @var Lasallecms\Lasallecmsapi\FormProcessing\FeaturedImageProcessing
     */
    protected $featuredImageProcessing;


    ///////////////////////////////////////////////////////////////////
    /// SPECIFY THE TYPE OF PERSIST THAT IS GOING ON HERE:          ///
    ///  * "create"  for INSERT                                     ///
    ///  * "update   for UPDATE                                     ///
    ///  * "destroy" for DELETE                                     ///
    ///////////////////////////////////////////////////////////////////
    /*
     * Type of persist
     *
     * @var string
     */
    protected $type = "update";

    ///////////////////////////////////////////////////////////////////
    /// SPECIFY THE FULL NAMESPACE AND CLASS NAME OF THE MODEL      ///
    ///////////////////////////////////////////////////////////////////
    /*
     * Namespace and class name of the model
     *
     * @var string
     */
    protected $namespaceClassnameModel = "Lasallecms\Lasallecmsapi\Models\Post";



    ///////////////////////////////////////////////////////////////////
    ///   USUALLY THERE IS NOTHING ELSE TO MODIFY FROM HERE ON IN   ///
    ///////////////////////////////////////////////////////////////////


    /*
     * Inject the model
     *
     * @param Lasallecms\Lasallecmsapi\Repositories\BaseRepository
     * @param Lasallecms\Lasallecmsapi\FormProcessing\FeaturedImageProcessing
     */
    public function __construct(BaseRepository $repository, FeaturedImageProcessing $featuredImageProcessing)
    {
        $this->repository = $repository;

        $this->repository->injectModelIntoRepository($this->namespaceClassnameModel);

        // inject featured image processing class
        $this->featuredImageProcessing = $featuredImageProcessing;
    }

    /*
     * The form processing steps.
     *
     * @param  object  $createCommand   The command bus object
     * @return array                    The custom response array
     */
    public function quarterback($updateCommand)
    {
        // Convert the command bus object into an array
        $data = (array) $updateCommand;


        // Sanitize
        $data = $this->sanitize($data, $this->type);


        // Process the featured image, including validating the featured image
        $featuredImageProcessing = $this->featuredImageProcessing->process($data);

        // Did the featured image validation fail?
        if ($featuredImageProcessing['validationMessage'] != "passed") {

            // Unlock the record
            $this->unlock($data['id']);

            // Prepare the response array, and then return to the edit form with error messages
            return $this->prepareResponseArray('validation_failed', 500, $data, $featuredImageProcessing['validationMessage']);
        }
        if ($featuredImageProcessing['validationMessage'] == "passed") {
            $data['featured_image'] = $featuredImageProcessing['featured_image'];
        }


        // Validate the rest of the form fields' data
        if ($this->validate($data, $this->type) != "passed")
        {
            // Unlock the record
            $this->unlock($data['id']);

            // Prepare the response array, and then return to the edit form with error messages
            return $this->prepareResponseArray('validation_failed', 500, $data, $this->validate($data, $this->type));
        }


        // Even though we already sanitized the data, we further "wash" the data
        $data = $this->wash($data);


        // UPDATE record
        if (!$this->persist($data, $this->type))
        {
            // Unlock the record
            $this->unlock($data['id']);

            // Prepare the response array, and then return to the edit form with error messages
            // Laravel's https://github.com/laravel/framework/blob/5.0/src/Illuminate/Database/Eloquent/Model.php
            //  does not prepare a MessageBag object, so we'll whip up an error message in the
            //  originating controller
            return $this->prepareResponseArray('persist_failed', 500, $data);
        }


        // Unlock the record
        $this->unlock($data['id']);


     /* ================================================================================================================
                                           ***  FIRE (CUSTOM) EVENTS ***
        ----------------------------------------------------------------------------------------------------------------
        It is at this point, after the update processing has concluded, when you want to fire custom events.

        By my reckoning, it is unlikely to fire custom events after the create processing. One: who gets it right the
        time, that no editing will be required (eg, an article). Two: it's so easy to change the workflow_status_id in
        the update, and we certainly do not want to duplicate code (in the create form processor), so just place the
        fire events code here in the update form processor.

        I want a confirmation before firing my send-post-to-a-LaSalleCRM-email-list event. I want this confirmation to
        mimick my record deletion confirmation. That is, I want a completely separate non-ajax request to display the
        confirmation. Some custom events will not require a confirmation, so the fun is to build a generic way to
        accommodate either "confirm" and "no-confirm" event firing.

        There is an UpdateResource-ModelName-FormProcessing.php for each table/model, and this is where I want the
        (custom) event firing to be. I'm going to set up a generic snippet in the in
        Lasallecms\Formhandling\AdminFormhandling\AdminFormBaseController::update to handle the confirmation, and I'm
        going to create a new generic confirmation view for event firing confirmation at
        lasallecrmlistmanagement::confirmations.confirm_send_to_list.

        ================================================================================================================ */

        // Send article to emails in a LaSalleCRM email list
        if ($data['lookup_workflow_status_id'] == 5) {

            // Config setting specifying whether to send the article (post) to a LaSalleCRM list
            if (Config::get('lasallecmsapi.lasallecrm_list_send_post_to_email_list')) {

                // The config setting that specifies the list ID
                $data['listID'] = Config::get('lasallecmsapi.lasallecrm_list_the_id_of_the_list_you_want_to_use');


                // **************************************************************
                // I want a confirmation of this event.
                // **************************************************************

                // only event confirmations need the following params.
                // Will also need a route for the event, and a controller method to fire the event

                // set up the params for the confirmation, because it is a generic event firing confirmation,
                // and not a confirmation that is already set up specifically for this particular event firing

                $data['modelName']          = "post";
                $data['nameOfEventToFire']  = "SendPostToLaSalleCRMemailList";
                $data['tableName']          = "posts";
                $data['resourceRouteName']  = "posts";
                $data['RouteToController']  = "admin.post";
                $data['packageName']        = "LaSalleCMS";
                $data['formActionRoute']    = "posts/sendPostToLaSalleCRMList";
                $data['eventDescription']   = "send this article to a LaSalleCRM email list";
                $data                       = $data;

                // I created a new "response array" (not a real response, just a var I use in my LaSalle Software)
                // for "update successful, now confirm the event firing
                return $this->prepareResponseArray('update_successful_now_confirm_before_firing_event', 200, $data);


                // **************************************************************
                // Here for reference: if you fire event without confirming first
                // **************************************************************

                // Fire the event
                // $data['eventDescription'] = "fired the event to send this article to a LaSalleCRM email list";
                // event(new SendPostToLaSalleCRMemailList($data));
                // return $this->prepareResponseArray('update_successful_with_event_fired', 200, $data);
            }
        }


        // Prepare the "response" array, and then return to the command
        return $this->prepareResponseArray('update_successful', 200, $data);


        ///////////////////////////////////////////////////////////////////
        ///     NO EVENTS ARE SPECIFIED IN THE BASE FORM PROCESSING     ///
        ///////////////////////////////////////////////////////////////////
    }
}