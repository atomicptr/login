<?php

namespace Atomicptr\Login\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Utility class to get TypoScript configuration
 */
class ConfigurationUtility {

    /**
     * Creates an Object Manager instance
     * @return \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected static function getObjectManager() : ObjectManager {
        return GeneralUtility::makeInstance(ObjectManager::class);
    }

    /**
     * Get Full TypoScript configuration
     * @return array
     */
    public static function getTypoScriptConfiguration() : array {
        return self::getObjectManager()->get(ConfigurationManagerInterface::class)->
            getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
    }

    /**
     * Returns the storage pids or null if unset
     * @return string|null
     */
    public static function getStoragePids() {
        $configuration = self::getTypoScriptConfiguration();

        $pid = $configuration["plugin."]["tx_login."]["persistence."]["storagePids"];

        if (!empty($pid)) {
            return $pid;
        }

        return null;
    }
}
