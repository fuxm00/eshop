<?php

namespace App\FrontModule\Components\UserDetailsForm;

interface UserDetailsFormFactory {

    public function create():UserDetailsForm;

}