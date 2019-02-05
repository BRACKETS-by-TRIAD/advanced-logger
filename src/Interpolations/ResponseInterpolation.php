<?php

namespace Brackets\AdvancedLogger\Interpolations;

use Brackets\AdvancedLogger\Services\Benchmark;

/**
 * Class RequestInterpolation
 */
class ResponseInterpolation extends BaseInterpolation
{
    /**
     * @param string $text
     * @return string
     */
    public function interpolate(string $text): string
    {
        $variables = explode(' ', $text);
        foreach ($variables as $variable) {
            $matches = [];
            preg_match("/{\s*(.+?)\s*}(\r?\n)?/", $variable, $matches);
            if (isset($matches[1])) {
                $value = $this->escape($this->resolveVariable($matches[0], $matches[1]));
                $text = str_replace($matches[0], $value, $text);
            }
        }
        return $text;
    }

    /**
     * @param string $raw
     * @param string $variable
     * @return string
     */
    public function resolveVariable(string $raw, string $variable): string
    {
        $method = str_replace([
            'content',
            'httpVersion',
            'status',
            'statusCode'
        ], [
            'getContent',
            'getProtocolVersion',
            'getStatusCode',
            'getStatusCode'
        ], camel_case($variable));

        if (method_exists($this->response, $method)) {
            return $this->response->$method();
        }

        if (method_exists($this, $method)) {
            return $this->$method();
        }

        $matches = [];
        preg_match("/([-\w]{2,})(?:\[([^\]]+)\])?/", $variable, $matches);
        if (count($matches) === 3) {
            [$line, $var, $option] = $matches;
            switch (strtolower($var)) {
                case 'res':
                    return $this->response->headers->get($option);
                default;
                    return $raw;
            }
        }
        return $raw;
    }

    /**
     * Get length of response
     *
     * @return string
     */
    public function getContentLength(): string
    {
        $path = storage_path('framework' . DIRECTORY_SEPARATOR . 'temp');
        if (!file_exists($path) && !mkdir($path, 0777, true) && !is_dir($path)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
        }
        $content = $this->response->getContent();
        $file = $path . DIRECTORY_SEPARATOR . 'response-' . time();
        file_put_contents($file, $content);
        $contentLength = (string)filesize($file);
        unlink($file);
        return $contentLength;
    }

    /**
     * Get response time
     *
     * @return string|null
     */
    public function responseTime(): ?string
    {
        try {
            return (string)Benchmark::duration('application');
        } catch (\Exception $e) {
            return null;
        }
    }
}