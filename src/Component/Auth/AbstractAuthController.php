<?php


namespace Copper\Component\Auth;

use Copper\Component\FlashMessage\FlashMessage;
use Copper\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AbstractAuthController extends AbstractController
{
    /** @var string */
    const LOGIN_TEMPLATE = 'login';

    /** @var string */
    const DEFAULT_RETURN_ROUTE = 'index';
    /** @var string */
    const DEFAULT_ERROR_MSG = 'Wrong login or password';

    /** @var string */
    const LOGIN_FIELD = 'login';
    /** @var string */
    const PASSWORD_FIELD = 'password';

    /**
     * @param AbstractUser $user
     *
     * @return RedirectResponse
     */
    protected function generateSuccessLoginResponse(AbstractUser $user)
    {
        $return_route = $this->request->query->get($this->auth->config->returnToRouteParam, null);

        if ($return_route !== null)
            return $this->redirectToRoute($return_route);
        else
            return $this->redirectToRoute(static::DEFAULT_RETURN_ROUTE);
    }

    public function getLogin()
    {
        return $this->render(static::LOGIN_TEMPLATE);
    }

    public function postLogin()
    {
        $login = trim($this->request->request->get(static::LOGIN_FIELD));
        $password = trim($this->request->request->get(static::PASSWORD_FIELD));

        $user = $this->auth->validate($login, $password);

        if ($user !== null) {
            $this->auth->authorize($user->id);
            return $this->generateSuccessLoginResponse($user);
        }

        $this->flashMessage->set(FlashMessage::ERROR, static::DEFAULT_ERROR_MSG);
        $this->flashMessage->set('form_login', $login);

        return $this->redirectToRoute($this->auth->config->loginRoute);
    }

    public function getLogout()
    {
        $this->auth->logout();

        return $this->redirectToRoute(static::DEFAULT_RETURN_ROUTE);
    }

}