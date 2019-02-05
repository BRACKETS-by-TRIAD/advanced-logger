<?php

namespace Brackets\AdvancedLogger;

use Brackets\AdvancedLogger\Interpolations\RequestInterpolation;
use Brackets\AdvancedLogger\Interpolations\ResponseInterpolation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdvancedLogger
{
    /**
     *
     */
    protected const LOG_CONTEXT = 'RESPONSE';
    /**
     * @var array
     */
    protected $formats = [
        'full' => 'HTTP/{http-version} {status} | {remote-addr} | {user} | {method} {url} {query} | {response-time} ms | {user-agent} | {referer}',
        'combined' => '{remote-addr} - {remote-user} [{date}] "{method} {url} HTTP/{http-version}" {status} {content-length} "{referer}" "{user-agent}"',
        'common' => '{remote-addr} - {remote-user} [{date}] "{method} {url} HTTP/{http-version}" {status} {content-length}',
        'dev' => '{method} {url} {status} {response-time} ms - {content-length}',
        'short' => '{remote-addr} {remote-user} {method} {url} HTTP/{http-version} {status} {content-length} - {response-time} ms',
        'tiny' => '{method} {url} {status} {content-length} - {response-time} ms'
    ];
    /**
     * @var RequestInterpolation
     */
    protected $requestInterpolation;
    /**
     * @var ResponseInterpolation
     */
    protected $responseInterpolation;
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * AdvancedLogger constructor.
     *
     * @param Logger $logger
     * @param RequestInterpolation $requestInterpolation
     * @param ResponseInterpolation $responseInterpolation
     */
    public function __construct(
        Logger $logger,
        RequestInterpolation $requestInterpolation,
        ResponseInterpolation $responseInterpolation
    ) {
        $this->logger = $logger;
        $this->requestInterpolation = $requestInterpolation;
        $this->responseInterpolation = $responseInterpolation;
    }

    /**
     * @param Request $request
     * @param Response $response
     */
    public function log(Request $request, Response $response): void
    {
        $this->requestInterpolation->setRequest($request);

        $this->responseInterpolation->setResponse($response);

        if (config('advanced-logger.logger.enabled')) {
            $format = config('advanced-logger.logger.format', 'full');
            $format = array_get($this->formats, $format, $format);

            $message = $this->responseInterpolation->interpolate($format);
            $message = $this->requestInterpolation->interpolate($message);

            $this->logger->log(config('advanced-logger.logger.level', 'info'), $message, [
                static::LOG_CONTEXT
            ]);
        }
    }

}