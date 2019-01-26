<?php

namespace Copper\Controller;

class RedirectController extends AbstractController
{
    /**
     * Removes all slashes from URL except last one
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeTrailingSlashAction()
    {
        $pathInfo = $this->request->getPathInfo();
        $requestUri = $this->request->getRequestUri();

        $url = str_replace($pathInfo, rtrim($pathInfo, ' /'), $requestUri);

        // 308 (Permanent Redirect) is similar to 301 (Moved Permanently) except
        // that it does not allow changing the request method (e.g. from POST to GET)
        return $this->redirect($url, 308);
    }
}