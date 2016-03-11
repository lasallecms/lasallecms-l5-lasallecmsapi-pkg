<?php

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



return [

	/*
	|--------------------------------------------------------------------------
	| Excerpt Length
	|--------------------------------------------------------------------------
	|
	| When an excerpt field is left blank, then the "content" field is used to
	| construct the excerpt. How many characters of the base "content" field
	| do you want to use for the excerpt?
	|
	*/
	'how_many_initial_chars_of_content_field_for_excerpt' => '100',

    /*
	|--------------------------------------------------------------------------
	| Excerpt Ellipses
	|--------------------------------------------------------------------------
	|
	| Do you prefer adding ellipses to your excerpts? Use this config setting
    | to do so automatically, instead of remembering to add the ellipses manually
    | each time you add a post
	|
	*/
    //'excerpt_ellipses' => '',
    'append_excerpt_with_this_string' => '...',


	/****************************************************************************************/
	/*                  START: SEND POST TO LASALLECRM EMAIL LIST                            */
	/****************************************************************************************/

	/*
	|--------------------------------------------------------------------------
	| Use the built-in event to send a post to a LaSalleCRM email list
	|--------------------------------------------------------------------------
	|
	| There is a built-in event that sends your most recent post to a LaSalleCRM email list.
	| Instead of using something like MailChimp's RSS-to-email feature, we can just do it
	| in-house.
	|
	| Each email recipient must exist as a LaSalleCRM person (but *not* necessarily a LaSalleCMS
	| user who can login). And, each user must have their email address as a "primary" email address.
	|
	| This event is fired when you UPDATE a post using the "Send to List" workflow status
	| (lookup_workflow_status_id = 5).
	|
	| Do you want to use this built-in event to send a post to the LaSalleCRM email list?
	|
	| true or false
	|
	*/
	'lasallecrm_list_send_post_to_email_list' => true,

	/*
	|--------------------------------------------------------------------------
	| List ID of the LaSalleCRM email list you want to send the post to
	|--------------------------------------------------------------------------
	|
	| Which LaSalleCRM list do you want to send the post to?
	|
	| Specify the ID of the "lists" database table. Go to List Management | Lists, and
	| use the number you see in the ID column next to the name of the list you want to use.
	|
	*/
	'lasallecrm_list_the_id_of_the_list_you_want_to_use' => '1',

	/****************************************************************************************/
	/*                   END: SEND POST TO LASALLECRM EMAIL LIST                            */
	/****************************************************************************************/

];
