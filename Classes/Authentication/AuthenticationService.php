<?php

namespace Atomicptr\Login\Authentication;

use TYPO3\CMS\Core\Crypto\PasswordHashing\Argon2iPasswordHash;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
     * Try to log in as TYPO3 frontend user.
     * @param string $username Users username.
     * @param string $password Users password.
     * @return boolean
     */
    public function login(string $username, string $password) : bool {
        // TODO: allow different processors
        $passwordProcessor = GeneralUtility::makeInstance(Argon2iPasswordHash::class);

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

        // TODO: add option to use email for this instead
        //$info["db_user"]["username_column"] = "username";

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