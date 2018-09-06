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
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     * @inject
     */
    protected $signalSlotDispatcher;

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
            $this->signalSlotDispatcher->dispatch(__CLASS__, "afterLoginSuccessful", ["login", $this,]);
            $this->redirectToPage(66);
        }

        // TODO: login failed...
        $this->redirect("form");
        //$this->signalSlotDispatcher->dispatch(__CLASS__, "afterLoginFailed", ["login", $this]);
    }

    /**
     * Log out the user
     * @return void
     */
    public function submitLogoutAction() {
        $this->signalSlotDispatcher->dispatch(__CLASS__, "beforeLogout", ["login", $this]);
        $this->userAuthentication->killSession();
        $this->signalSlotDispatcher->dispatch(__CLASS__, "afterLogout", ["login", $this]);
        $this->redirect("form");
    }

    /**
     * Redirect user to a specific page
     * @param integer $pageUid Target page UID.
     * @return void
     */
    public function redirectToPage(int $pageUid) {
        $url = $this->uriBuilder
            ->reset()
            ->setTargetPageUid($pageUid)
            ->setLinkAccessRestrictedPages(true)
            ->build();

        $this->signalSlotDispatcher->dispatch(__CLASS__, "beforeRedirect", ["login", $this, $url]);

        $this->redirectToUri($url);
    }
}
