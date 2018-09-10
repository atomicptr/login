<?php

defined("TYPO3_MODE") || die();

use Atomicptr\Login\Utility\FlexformUtility;

call_user_func(function () {

    FlexformUtility::add(
        "login_loginformplugin",
        "EXT:login/Configuration/FlexForms/login_loginformplugin.xml"
    );
});
