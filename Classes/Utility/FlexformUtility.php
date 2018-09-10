<?php

namespace Atomicptr\Login\Utility;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Utility class to handle Flex form stuff
 */
class FlexformUtility {

    /**
     * Adds a flex form to a plugin
     * @param string $pluginName   The name of the plugin.
     * @param string $flexFormPath Path to the flexform.
     * @return void
     */
    public static function add(string $pluginName, string $flexFormPath) {
        // add flexform
        $GLOBALS["TCA"]["tt_content"]["types"]["list"]["subtypes_addlist"][$pluginName] = "pi_flexform";
        ExtensionManagementUtility::addPiFlexFormValue($pluginName, "FILE:$flexFormPath");

        // hide unused elements
        $GLOBALS["TCA"]["tt_content"]["types"]["list"]["subtypes_excludelist"][$pluginName] =
            "recursive,select_key,pages";
    }
}
