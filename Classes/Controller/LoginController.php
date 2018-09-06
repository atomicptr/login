<?php

namespace Atomicptr\Login\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Annotation\Inject as inject;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use Atomicptr\Login\Authentication\UserLogin;

/**
 * Manages everything related to login
 */
class LoginController extends ActionController {

    /**
     * User authentication stuff
     * @var \Atomicptr\Login\Authentication\UserLogin
     * @inject
     */
    protected $userAuthentication;

    /**
     * Renders the login form
     * @return void
     */
    public function formAction() {
    }

    /**
     * Checks and validates login credentials
     * @return void
     */
    public function submitLoginAction() {
        $userData = $this->request->getArgument("user");

        if ($this->userAuthentication->login($userData["username"], $userData["password"])) {
            // TODO: login success
        }

        // TODO: login failed...
        $this->redirect("form");
    }

    /**
     *
     */
    public function submitLogoutAction() {
        $this->userAuthentication->killSession();
        $this->redirect("form");
    }
}
