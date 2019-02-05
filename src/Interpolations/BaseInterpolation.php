<?php

namespace Brackets\AdvancedLogger\Interpolations;

use Brackets\AdvancedLogger\Contracts\InterpolationContract;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Class BaseInterpolation
 */
abstract class BaseInterpolation implements InterpolationContract
{
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var Response
     */
    protected $response;

    /**
     * @param Request $request
     */
    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    /**
     * @param Response $response
     */
    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }

    /**
     * @param string $text
     * @return string
     */
    protected function escape(string $text): string
    {
        return preg_replace('/\s/', "\\s", $text);
    }
}