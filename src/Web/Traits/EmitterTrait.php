<?php
declare(strict_types=1);

namespace Mizam\Web\Traits;

use Exception;
use InvalidArgumentException;
use Mizam\Log;

trait EmitterTrait
{
    /**
     * @param string $body
     * @param array $option_headers
     * @param CookieTrait[] $cookie_list
     * @param int $status_code
     * @throws Exception
     */
    static public function send(string $body, array $option_headers = [], array $cookie_list = [], int $status_code = 200): void
    {
        static::sendHeader($option_headers, $cookie_list, $status_code);

        Log::dumpOutputBufferToLog();

        Log::debug("send content", ['body' => $body]);

        echo $body;
    }

    /**
     * @param resource $stream
     * @param array $option_headers
     * @param CookieTrait[] $cookie_list
     * @param int $status_code
     * @throws Exception
     */
    static public function sendStream($stream, array $option_headers = [], array $cookie_list = [], int $status_code = 200): void
    {
        static::sendHeader($option_headers, $cookie_list, $status_code);

        Log::dumpOutputBufferToLog();

        while (!feof($stream)) {
            echo fread($stream, 8192);
        }

        fclose($stream);
    }

    /**
     * @param array $option_headers
     * @param array $cookie_list
     * @param int $status_code
     * @throws Exception
     */
    static public function sendHeaderAndFinishOb(array $option_headers = [], array $cookie_list = [], int $status_code = 200): void
    {
        static::sendHeader($option_headers, $cookie_list, $status_code);

        Log::dumpOutputBufferToLog();

        ob_end_clean();
    }

    /**
     * @param null $url
     * @throws Exception
     */
    public static function sendCORSPrefetch($url = null)
    {
        static::sendHeaderAndFinishOb([
            "Access-Control-Allow-Origin" => $url,
            "Access-Control-Allow-Methods" => "GET, POST, PUT, DELETE",
            "Access-Control-Allow-Headers" => "Origin, Authorization, Accept, Content-Type",
            "Access-Control-Max-Age" => "3600"
        ], [], 204);
    }

    /**
     * @param array $option_headers
     * @param CookieTrait[] $cookie_list
     * @param int $status_code
     * @throws Exception
     */
    private static function sendHeader(array $option_headers = [], array $cookie_list = [], int $status_code = 200): void
    {
        Log::debug("send header", ['headers' => $option_headers, 'cookie' => json_decode(json_encode($cookie_list)), 'status_code' => $status_code]);

        ## ref: https://infosec.mozilla.org/guidelines/web_security
        $option_headers = array_merge([
            "X-Frame-Options" => "DENY",
            "X-Content-Type-Options" => "nosniff",
            "X-XSS-Protection" => "1; mode=block",
            "Content-Security-Policy" => implode(";", [
                ## ref: https://developers.google.com/web/fundamentals/security/csp/?hl=ja
                "default-src 'self'",
//                "script-src 'self' https://www.google-analytics.com", # sample for Analytics
                "script-src 'self'",
//                "style-src 'self' 'unsafe-inline'", # sample for inline css
                "style-src 'self'",
                "img-src *",
                "frame-ancestors 'none'",
                // "report-uri http://example.jp/csp-report.php"
            ]),
            // "Content-Security-Policy-Report-Only"
            // "Referrer-Policy" => "no-referrer, strict-origin-when-cross-origin",
            // "Access-Control-Allow-Origin" => "https://example.jp"
        ], $option_headers);

        if (count($option_headers) > 0) {
            foreach ($option_headers as $header => $val) {
                header("{$header}: {$val}");
            }
        }

        if (count($cookie_list) > 0) {
            foreach ($cookie_list as $cookie) {
                if (!($cookie instanceof CookieTrait)) {
                    throw new InvalidArgumentException("cookie_list must be Web\\Cookie[]");
                }
                $cookie->send();
            }
        }

        http_response_code($status_code);
    }

    /**
     * @param string $url
     * @throws Exception
     */
    static public function redirect(string $url): void
    {
        static::sendHeader(["Location" => $url], [], 302);
    }
}
