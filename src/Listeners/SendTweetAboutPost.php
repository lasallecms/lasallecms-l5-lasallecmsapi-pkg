<?php

namespace Lasallecms\Lasallecmsapi\Listeners;

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

// LaSalle Software
use Lasallecms\Lasallecmsapi\Events\PublishThePost;
use Thujohn\Twitter\Facades\Twitter;

// Laravel classes
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTweetAboutPost implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct() {
        // blank on purpose
    }

    /**
     * Handle the event.
     *
     * @param  PublishThePost $event
     * @return void
     */
    public function handle(PublishThePost $event) {

        $status = "Article: ";
        $status .= $event->data['id']['title'];
        $status .= " ";
        $status .= $event->data['id']['canonical_url'];
        $status .= " #Laravel";

        $twitter_status = Twitter::postTweet(['status' => $status, 'format' => 'json']);
        \Log::info('Lasallecms\Lasallecmsapi\Listeners\SendTweetAboutPost listener completed');
    }
}