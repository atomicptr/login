<?php

namespace Atomicptr\Login\Authentication;

use TYPO3\CMS\Core\Utility\GeneralUtility;

use TYPO3\CMS\Extbase\Annotation\Inject as inject;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

use Atomicptr\Login\Utility\ConfigurationUtility;

/**
 * Handles user login
 */
class AuthenticationService extends FrontendUserAuthentication {

    /**
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     * @inject
     */
    protected $signalSlotDispatcher;

    /**
     * @var \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository
     * @inject
     */
    protected $frontendUserRepository;

    /**
     * Try to log in as TYPO3 frontend user.
     * @param string $username      Users username.
     * @param string $password      Users password.
     * @param string $usernameField Username field "username" or "email".
     * @return boolean
     */
    public function login(string $username, string $password, string $usernameField = "username") : bool {
        $passwordHasherClass = $GLOBALS["TYPO3_CONF_VARS"]["FE"]["passwordHashing"]["className"];

        if (!$passwordHasherClass && strpos(TYPO3_version, "8.7") === 0) {
            if (!\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded("saltedpasswords")) {
                // crash when trying to login without salted passwords on TYPO3 v8
                throw new Exception("TYPO3 v".TYPO3_version.", extension \"saltedpasswords\" is not loaded.");
            }

            $passwordHasherClass = $GLOBALS["TYPO3_CONF_VARS"]["EXTENSIONS"]["saltedpasswords"]
                ["FE"]["saltedPWHashingMethod"];
        }

        $passwordProcessor = GeneralUtility::makeInstance($passwordHasherClass);

        $loginData = [
            "username" => $username,
            "uident_text" => $password,
            "status" => "login"
        ];

        $this->checkPid = false;

        $storagePid = ConfigurationUtility::getStoragePids();

        if ($storagePid !== null) {
            $this->checkPid = true;
            $this->checkPid_value = $storagePid;
        }

        $info = $this->getauthInfoArray();

        $info["db_user"]["username_column"] = $usernameField;

        $user = $this->fetchUserRecord($info["db_user"], $username);

        $this->signalSlotDispatcher->dispatch(__CLASS__, "beforeLoginAttempt", ["login", $this, $user]);

        if ($user && $passwordProcessor->checkPassword($password, $user["password"])) {
            $this->setSession($user);
            return true;
        }

        return false;
    }

    /**
     * Sets the user session
     * @param mixed $user User object.
     * @return void
     */
    protected function setSession($user) {
        $GLOBALS["TSFE"]->fe_user->forceSetCookie = true;

        $GLOBALS['TSFE']->fe_user->fetchGroupData();
        $GLOBALS["TSFE"]->fe_user->createUserSession($user);

        $userSession = $GLOBALS['TSFE']->fe_user->fetchUserSession();

        $GLOBALS["TSFE"]->fe_user->user = $userSession;
        setcookie("fe_typo_user", $userSession["ses_id"], time() + (86400 * 30), "/");

        $GLOBALS["TSFE"]->fe_user->storeSessionData();
    }

    /**
     * Checks if the user is logged in
     * @return boolean
     */
    public function isLoggedIn() : bool {
        return (bool)$GLOBALS["TSFE"]->loginUser;
    }

    /**
     * Returns the currently logged in user
     * @return mixed
     */
    public function getUser() {
        if ($GLOBALS["TSFE"]->fe_user->user) {
            return $this->frontendUserRepository->findByUid(
                $GLOBALS["TSFE"]->fe_user->user["uid"]
            ) ?? $GLOBALS["TSFE"]->fe_user->user;
        }

        return null;
    }

    /**
     * Logs out the current user
     * @return void
     */
    public function logout() {
        if (!$this->isLoggedIn()) {
            return;
        }

        $GLOBALS["TSFE"]->fe_user->user = null;
        setcookie("fe_typo_user", "", time() - 3600);
    }
}
