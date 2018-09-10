<?php

defined("TYPO3_MODE") || die("Access denied.");

call_user_func(function ($extKey) {

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        "Atomicptr.Login",
        "LoginFormPlugin",
        "Login Form",
        "EXT:$extKey/ext_icon.svg"
    );

    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance("TYPO3\CMS\Core\Imaging\IconRegistry");
}, $_EXTKEY);
