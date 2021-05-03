<?php


namespace Copper\Component\Error;


use Copper\Handler\ArrayHandler;
use Copper\Handler\DateHandler;
use Copper\Handler\FileHandler;
use Copper\Handler\StringHandler;
use Copper\Kernel;
use Symfony\Component\HttpFoundation\Response;

class ErrorEntity
{
    /** @var string */
    public $date;
    /** @var string */
    public $method;
    /** @var string */
    public $url;
    /** @var string */
    public $protocol_ver;
    /** @var string */
    public $status;
    /** @var string */
    public $msg;
    /** @var string */
    public $type;
    /** @var string */
    public $file;
    /** @var string */
    public $line;
    /** @var string */
    public $code;
    /** @var string */
    public $func;
    /** @var string */
    public $args;
    /** @var string */
    public $ips;
    /** @var int */
    public $user_id;
    /** @var string */
    public $referer;

    /**
     * ErrorEntity constructor.
     * @param $msg
     * @param $type
     * @param int $status
     */
    public function __construct($msg, $type, $status = Response::HTTP_INTERNAL_SERVER_ERROR)
    {
        $this->date = DateHandler::dateTime();
        $this->method = Kernel::getRequest()->getMethod();
        $this->url = Kernel::getRequest()->getRequestUri();
        $this->protocol_ver = Kernel::getRequest()->getProtocolVersion();
        $this->status = $status;

        $this->type = $type;
        $this->msg = $msg;
        // ---- exception ---
        // file
        // line
        // code
        // func
        // args
        // ------------------
        $this->ips = ArrayHandler::join(Kernel::getRequest()->getClientIps());
        $this->user_id = Kernel::getAuth()->check() ? Kernel::getAuth()->user()->id : 0;
        $this->referer = Kernel::getRequest()->headers->get('referer');
    }

    /**
     * @param \Exception $e
     * @param bool $hideProjectPath
     *
     * @return ErrorEntity
     */
    public static function createFromException($e, bool $hideProjectPath)
    {
        $trace = $e->getTrace();

        $msg = $e->getMessage();
        $type = get_class($e);

        $error = new ErrorEntity($msg, $type);

        $error->file = $e->getFile();

        $error->line = $e->getLine();
        $error->func = (count($trace) > 0 && array_key_exists('function', $trace[0])) ? $e->getTrace()[0]['function'] : '';
        $error->args = (count($trace) > 0 && array_key_exists('args', $trace[0])) ? $e->getTrace()[0]['args'] : '';

        if (StringHandler::has($error->func, '{closure}'))
            $error->args = [];

        $error->code = FileHandler::readLine($error->file, $error->line - 1);

        $error->args = StringHandler::dump($error->args, true);

        if ($hideProjectPath)
            $error->file = StringHandler::replace($error->file, Kernel::getProjectPath(), '');

        return $error;
    }

    public function asLogData($format)
    {
        $list = [
            $this->date,
            $this->method,
            $this->url,
            $this->protocol_ver,
            $this->status,
            $this->type,
            $this->msg,
            $this->file,
            $this->line,
            $this->ips,
            $this->user_id,
            $this->referer
        ];

        return StringHandler::sprintf($format, $list);
    }

    public function asParamList()
    {
        return [
            '$date' => $this->date,
            '$method' => $this->method,
            '$url' => $this->url,
            '$type' => $this->type,
            '$msg' => $this->msg,
            '$file' => $this->file,
            '$line' => $this->line,
            '$code' => $this->code,
            '$func' => $this->func,
            '$args' => $this->args,
            '$ips' => $this->ips,
            '$user_id' => $this->user_id,
            '$referer' => $this->referer,
            '$protocol_ver' => $this->protocol_ver,
            '$status' => $this->status,
        ];
    }
}