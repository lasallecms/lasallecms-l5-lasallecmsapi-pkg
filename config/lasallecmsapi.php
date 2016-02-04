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

];
