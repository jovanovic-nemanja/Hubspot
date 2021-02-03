<?php

namespace App\Http\Middleware;

use Closure;
use Cache;

class ApiDataLogger
{
    private $startTime;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->startTime = microtime(true);
        return $next($request);
    }

    public function terminate($request, $response)
    {
        if (env('APP_ENV') && env('APP_DEBUG')) {
            $endTime = microtime(true);

            $dataToLog  = 'Time: '   . gmdate("F j, Y, g:i a") . "\n";
            $dataToLog .= 'Duration: ' . number_format($endTime - $this->startTime, 3) . "\n";
            $dataToLog .= 'IP Address: ' . $request->ip() . "\n";
            $dataToLog .= 'URL: '    . $request->fullUrl() . "\n";
            $dataToLog .= 'Method: ' . $request->method() . "\n";
            $dataToLog .= 'Input: '  . $request->getContent() . "\n";
            $dataToLog .= 'Output: ' . json_encode(json_decode($response->getContent()), JSON_PRETTY_PRINT) . "\n";

            if (env('APP_ENV') == 'local') {
                $this->debugger($request, json_decode($response->getContent(), true));
            }

            // \Log::info($dataToLog . "\n" . str_repeat("=", 20) . "\n\n");
        }
    }

    protected function debugger($request, $response)
    {
        $stacks = [];

        if (Cache::has('debug')) {
            $stacks = Cache::get('debug');

            $_stack = [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'input' => json_decode($request->getContent(), true),
                'response' => $response
            ];

            if (count($stacks) >= 10) {
                array_shift($stacks);
            }

            array_push($stacks, $_stack);

            Cache::forever('debug', $stacks);
        } else {
            $_stack = [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'input' => json_decode($request->getContent(), true),
                'response' => $response
            ];

            array_push($stacks, $_stack);

            Cache::forever('debug', $stacks);
        }
    }
}
