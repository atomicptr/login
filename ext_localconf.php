<?php

defined("TYPO3_MODE") || die();

call_user_func(function () {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        "Atomicptr.Login",
        "LoginFormPlugin",
        ["Login" => "form, submitLogin, submitLogout"],
        []
    );

    // add pagets config
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        "<INCLUDE_TYPOSCRIPT: source=\"FILE:EXT:login/Configuration/TSconfig/pagets.tsconfig\">"
    );
}, $_EXTKEY);
