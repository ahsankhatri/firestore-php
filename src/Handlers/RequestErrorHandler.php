<?php

namespace MrShan0\PHPFirestore\Handlers;

use MrShan0\PHPFirestore\Exceptions\Client\BadRequest;
use MrShan0\PHPFirestore\Exceptions\Client\Conflict;
use MrShan0\PHPFirestore\Exceptions\Client\Forbidden;
use MrShan0\PHPFirestore\Exceptions\Client\NotFound;
use MrShan0\PHPFirestore\Exceptions\Client\Unauthorized;
use MrShan0\PHPFirestore\Exceptions\Server\InternalServerError;
use MrShan0\PHPFirestore\Exceptions\UnhandledRequestError;

class RequestErrorHandler
{
    private $exception;
    private $body;

    public function __construct($exception)
    {
        $this->exception = $exception;
        $this->body = $exception->getResponse()->getBody();
    }

    public function handleError()
    {
        $errorCode = $this->exception->getResponse()->getStatusCode();

        if (method_exists($this, 'handle'.$errorCode)) {
            $this->{'handle'.$errorCode}();
        }

        $this->handleUnknown();
    }

    protected function handle400()
    {
        throw new BadRequest($this->body);
    }

    protected function handle401()
    {
        throw new Unauthorized($this->body);
    }

    protected function handle403()
    {
        throw new Forbidden($this->body);
    }

    protected function handle404()
    {
        throw new NotFound($this->body);
    }

    protected function handle409()
    {
        throw new Conflict($this->body);
    }

    protected function handle500()
    {
        throw new InternalServerError($this->body);
    }

    private function handleUnknown()
    {
        throw new UnhandledRequestError($this->exception->getResponse()->getStatusCode(), $this->body);
    }
}
