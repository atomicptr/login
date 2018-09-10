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
     * @var \Atomicptr\Login\Authentication\AuthenticationService
     * @inject
     */
    protected $authentication;

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
        if ($this->request->hasArgument("error")) {
            $this->view->assign("error", $this->request->getArgument("error"));
        }

        $this->view->assign("user", $this->authentication->getUser());
    }

    /**
     * Checks and validates login credentials
     * @return void
     */
    public function submitLoginAction() {
        $userData = $this->request->getArgument("user");

        $parameters = [];

        $loginSuccess = $this->authentication->login(
            $userData["username"],
            $userData["password"],
            $this->settings["usernameField"] ?? "username"
        );

        if ($loginSuccess) {
            $this->signalSlotDispatcher->dispatch(__CLASS__, "afterLoginSuccessful", ["login", $this,]);

            $redirectPid = $this->settings["redirectAfterLogin"];

            if ($redirectPid) {
                $this->redirectToPage($redirectPid);
            }
        } else {
            $this->signalSlotDispatcher->dispatch(__CLASS__, "afterLoginFailed", ["login", $this]);

            $parameters = ["error" => "LoginFailed"];
        }

        $this->redirect("form", null, null, $parameters);
    }

    /**
     * Log out the user
     * @return void
     */
    public function submitLogoutAction() {
        $this->signalSlotDispatcher->dispatch(__CLASS__, "beforeLogout", ["login", $this]);
        $this->authentication->logout();
        $this->signalSlotDispatcher->dispatch(__CLASS__, "afterLogout", ["login", $this]);

        $redirectPid = $this->settings["redirectAfterLogout"];

        if ($redirectPid) {
            $this->redirectToPage($redirectPid);
        }

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
