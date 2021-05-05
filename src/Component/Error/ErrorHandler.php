<?php


namespace Copper\Component\Error;


use Copper\Handler\FileHandler;
use Copper\Kernel;
use Copper\Traits\ComponentHandlerTrait;
use ErrorException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ErrorHandler
 * @package Copper\Component\Error
 */
class ErrorHandler
{
    use ComponentHandlerTrait;

    const FLASH_MESSAGE_KEY = 'error_flash_parameters';

    /** @var ErrorConfigurator */
    public $config;

    /**
     * ErrorHandler constructor.
     *
     * @param string $configFilename
     */
    public function __construct(string $configFilename)
    {
        $this->config = $this->configure(ErrorConfigurator::class, $configFilename);

        $this->register();
    }

    /**
     * @param array $parameters
     */
    public function setFlashParameters(array $parameters)
    {
        Kernel::getFlashMessage()->set(self::FLASH_MESSAGE_KEY, json_encode($parameters));
    }

    /**
     * @return mixed
     */
    public function getFlashParameters()
    {
        return json_decode(Kernel::getFlashMessage()->get(ErrorHandler::FLASH_MESSAGE_KEY, '[]'), true);
    }

    /**
     * Save error to log file and output view OR redirect to view
     *
     * @param string $msg
     * @param int $status
     */
    public function throwError(string $msg, $status = Response::HTTP_INTERNAL_SERVER_ERROR)
    {
        $error = new ErrorEntity($msg, $this->config->app_error_type, $status);

        $this->throwErrorFromEntity($error, true);
    }

    /**
     * Save error only to log file
     *
     * @param string $msg
     * @param int $status
     */
    public function logError(string $msg, $status = Response::HTTP_INTERNAL_SERVER_ERROR)
    {
        $error = new ErrorEntity($msg, $this->config->app_error_type, $status);

        $this->throwErrorFromEntity($error, true, true);
    }

    /**
     * Save error to log file and redirect to view
     *
     * @param string $msg
     * @param int $status
     */
    public function throwErrorWithRedirect(string $msg, $status = Response::HTTP_INTERNAL_SERVER_ERROR)
    {
        $error = new ErrorEntity($msg, $this->config->app_error_type);

        $this->throwErrorFromEntity($error, true, false, true);
    }

    /**
     * Save error to log file and return Response
     *
     * @param string $msg
     * @param int $status
     * @param array $headers
     *
     * @return Response
     */
    public function throwErrorAsResponse(string $msg, $status = 500, $headers = [])
    {
        $error = new ErrorEntity($msg, $this->config->app_error_type, $status);

        return new Response(
            $this->throwErrorFromEntity($error, true, false, false, true),
            $status,
            $headers
        );
    }

    /**
     * @param ErrorEntity $error
     * @param bool $app Is application error ?
     * @param bool $logOnly Log only error without view
     * @param bool $redirect Force redirect to view
     * @param bool $return Return result or output it
     *
     * @return bool|string
     */
    private function throwErrorFromEntity(ErrorEntity $error, $app = false, $logOnly = false, $redirect = false, $return = false)
    {
        $log_format = ($app) ? $this->config->app_log_format : $this->config->e_log_format;

        $log_data = $error->asLogData($log_format);

        if ($this->config->log === true) {
            if (FileHandler::fileExists(Kernel::getProjectLogPath()) === false)
                FileHandler::createFolder(Kernel::getProjectLogPath());

            FileHandler::appendContent($this->config->log_filepath, $log_data . "\n", true);
        }

        if ($logOnly)
            return true;

        if (Kernel::getRequestContext() === null) {
            echo 'CORE ERROR';
            echo '<pre>';
            print_r($error->asParamList());
            echo '</pre>';
            return false;
        }

        $res = Kernel::redirectToRoute(ROUTE_index);

        if ($this->config->view === true) {

            $view_parameters = $error->asParamList();

            if ($this->config->view_route_redirect || $redirect) {
                $this->setFlashParameters($view_parameters);
                $res = Kernel::redirectToRoute($this->config->view_route_name);
            } else {
                $res = Kernel::renderView($this->config->view_default_template, $view_parameters);
            }
        }

        $res = ($res instanceof Response) ? $res->getContent() : $res;

        if ($return)
            return $res;

        echo $res;

        exit();
    }

    public function register()
    {
        /**
         * Uncaught exception handler.
         */
        $logException = function ($e) {
            $error = ErrorEntity::createFromException($e, $this->config->e_hide_project_path);
            $this->throwErrorFromEntity($error);
        };

        /**
         * Error handler, passes flow over the exception logger with new ErrorException.
         */
        $logError = function ($num, $str, $file, $line, $context = null) use ($logException) {
            $logException(new ErrorException($str, 0, $num, $file, $line));
        };

        /**
         * Checks for a fatal error, work around for set_error_handler not working on fatal errors.
         */
        $checkForFatal = function () use ($logError) {
            $error = error_get_last();

            if ($error !== NULL && $error["type"] == E_ERROR)
                $logError($error["type"], $error["message"], $error["file"], $error["line"]);
        };

        register_shutdown_function($checkForFatal);

        set_error_handler($logError);

        set_exception_handler($logException);

        ini_set("display_errors", "off");

        error_reporting(E_ALL);
    }

}