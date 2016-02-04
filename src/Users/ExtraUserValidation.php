<?php

namespace Lasallecms\Lasallecmsapi\Users;

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
use Lasallecms\Lasallecmsapi\Repositories\UserRepository;
use Lasallecms\Lasallecmsapi\FormProcessing\BaseFormProcessing;

// Laravel classes
use Illuminate\Support\MessageBag;

/**
 * Class ExtraUserValidation
 * @package Lasallecms\Lasallecmsapi\Users
 */
class ExtraUserValidation
{
    /**
     * @var Lasallecms\Lasallecmsapi\Repositories\UserRepository
     */
    protected $userRepository;

    /**
     * @var Lasallecms\Lasallecmsapi\FormProcessing\BaseFormProcessing
     */
    protected $baseFormProcessing;

    /**
     * The message bag instance.
     *
     * @var Illuminate\Support\MessageBag
     */
    protected $messages;

    /**
     * ExtraUserValidation constructor.
     * @param UserRepository     $userRepository
     * @param BaseFormProcessing $baseFormProcessing
     */
    public function __construct(
        UserRepository $userRepository,
        BaseFormProcessing $baseFormProcessing
    )
    {
        $this->userRepository     = $userRepository;
        $this->baseFormProcessing = $baseFormProcessing;
    }

    /**
     * Perform extra validation
     *
     * @param  array  $data
     * @param  bool    $performPhoneValidation
     * @return array
     */
    public function extraValidation($data, $performPhoneValidation=true) {

        $this->messages = new MessageBag;

        // is the cell number where text messages will be sent ok?
        if (
            (!$this->userRepository->validatePhoneNumber($data['phone_number']))
            && ($performPhoneValidation)
        )
        {
            // Prepare the response array, and then return to the edit form with error messages

            // first, add the error message to the message bag
            $this->messages->add('phone_number', 'There is a problem with your phone number. Please re-enter it.');

            return $this->baseFormProcessing->prepareResponseArray('validation_failed', 500, $data, $this->messages);
        }



        // does the password contain the word "password"?
        if (!$this->userRepository->validatePasswordNotUseWordPassword($data['password'])) {
            // Prepare the response array, and then return to the edit form with error messages

            // first, add the error message to the message bag
            $this->messages->add('password', 'Please do not use the word \'password\' in your password. This is *really* b-a-d for security.');

            return $this->baseFormProcessing->prepareResponseArray('validation_failed', 500, $data, $this->messages);
        }


        // does the password contain the user's name?
        if (!$this->userRepository->validatePasswordNotUseUsername($data['name'], $data['password'])) {
            // Prepare the response array, and then return to the edit form with error messages

            // first, add the error message to the message bag
            $this->messages->add('password', 'Please do not use your name in the password. This is *really* b-a-d for security.');

            return $this->baseFormProcessing->prepareResponseArray('validation_failed', 500, $data, $this->messages);
        }


        // Extra validation is ok

        // Prepare the response array, and then return to the command
        return $this->baseFormProcessing->prepareResponseArray('extra_validation_successful', 200, $data);
    }
}