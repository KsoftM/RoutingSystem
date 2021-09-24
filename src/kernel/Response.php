<?php

namespace ksoftm\system\kernel;

use ksoftm\system\utils\Cookie;
use ksoftm\system\utils\EndeCorder;
use ksoftm\system\utils\SingletonFactory;

class Response extends SingletonFactory
{
    protected static ?self $instance = null;
    public static function getInstance(): self
    {
        if (empty(self::$instance)) {
            self::$instance = Response::make();
        }
        return self::$instance;
    }

    /** @var string static $cookieKey cookie encrypting key. */
    protected static ?string $cookieKey = null;

    protected ?Cookie $data = null;

    /**
     * Class constructor.
     */
    protected function __construct(
        string $contents = null,
        int $responseCode,
        array $headers
    ) {
        if (!empty($contents)) {
            if (ob_get_length() != false) {
                ob_clean();
            }
            echo $contents;
        }

        http_response_code($responseCode);

        if (!empty($headers)) {
            $this->withHeader($headers);
        }
    }

    /**
     * get and set cookie key
     *
     * @param string|null $key
     *
     * @return string|false
     */
    protected static function CookieKey(string $key = null): string|false
    {
        if (!empty($key)) {
            self::$cookieKey = $key;
        }

        return self::$cookieKey ?? false;
    }

    public static function make(
        string $contents = null,
        int $responseCode = 200,
        array $headers = []
    ): Response {
        return new Response($contents, $responseCode, $headers);
    }


    public function header(string $attribute, string $value = null): Response
    {
        if (!empty($value)) {
            header("$attribute: $value");
        } else {
            header($attribute);
        }

        return $this;
    }

    public function withHeader(array $headers): Response
    {
        foreach ($headers as $key => $value) {
            if (is_numeric($key)) {
                $this->header($value);
            } else {
                $this->header($key, $value);
            }
        }
        return $this;
    }

    public function cookie(
        string $name,
        string $value = null,
        int $timestamp = 3600,
        string|bool $encryptKey = false
    ): Response {

        $this->data = Cookie::make($name, $value, $timestamp);
        if ($encryptKey != false && $this->data instanceof Cookie) {
            self::CookieKey($encryptKey);
            $this->data->encrypt(self::CookieKey());
        }

        $this->data->start();

        return $this;
    }

    public function acceptHtml(string $charset = 'UTF-8'): Response
    {
        $this->header('Content-type', "text/html; charset=$charset");

        return $this;
    }

    public function acceptJson(string $charset = 'UTF-8'): Response
    {
        $this->header('Content-type', "application/json; charset=$charset");

        return $this;
    }


    public function download(string $filePath): void
    {
        if (file_exists($filePath)) {
            Response::make()->withHeader([
                "Cache-Control: public",
                "Content-Description: File Transfer",
                "Cache-Control: no-cash, must-revalidate",
                "Expires: 0",
                "Content-Disposition: attachment; filename=" . pathinfo($filePath, PATHINFO_BASENAME),
                // "Content-Type: application/octet-stream",
                "Content-Type: application/" . pathinfo($filePath, PATHINFO_EXTENSION) ?? 'docx',
                "Content-Length: " . filesize($filePath),
                "Content-Transfer-Encoding: binary"
            ]);

            if (ob_get_length() != false) {
                ob_end_clean();
            }

            $ctx = stream_context_create();

            if (false !== ($fRes = fopen($filePath, 'r', context: $ctx))) {

                while (false !== ($d = stream_get_contents($fRes, 512 * 1024))) {
                    echo $d;
                }
                exit;
            }
        }

        $this->centeredMessage('File Not Found..!', 404);

        exit;
    }

    public static function centeredMessage(string $message = 'File Not Found..!', int $responseCode = 404): Response
    {
        $message = $message ?? 'File Not Found..!';

        Response::make("
            <h1 style=\"
            
                font-size: xxx-large;
                height: 100%;
                display: grid;
                text-align: center;
                align-items: center;
                justify-content: center;
                align-content: center;
                justify-items: center;
                
                \">
                $message
            </h1>
            ", $responseCode);

        return Response::make();
    }
}
