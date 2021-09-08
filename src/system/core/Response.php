<?php

namespace ksoftm\system\core;

use ksoftm\utils\EndeCorder;
use ksoftm\utils\html\BuildMixer;
use ksoftm\utils\html\Mixer;

class Response
{
    /** @var string static $cookieKey cookie encrypting key. */
    protected static ?string $cookieKey = null;

    // return response($content)->header('Content-Type', $type)
    // ->cookie('name', 'value', $minutes);

    // return response()->download($pathToFile, $name, $headers);

    // return response()->json($data, 200, $headers);

    // return Response::make($contents, 200, $headers);

    // return response($content)->withHeaders([
    //     'Content-Type' => $type,
    //     'X-Header-One' => 'Header Value',
    // ]);

    // $request->acceptsJson()

    /**
     * Class constructor.
     */
    protected function __construct(
        string $contents = null,
        int $responseCode,
        array $headers
    ) {
        if (!empty($contents)) {
            echo $contents;
        }

        http_response_code($responseCode);

        if (!empty($headers)) {
            $this->withHeader($headers);
        }
    }

    public static function CookieKey(string $key = null): string|false
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


    public function header(string $attribute, string $value): Response
    {
        $value = strpos($value, ';') == false ? "$value;" : $value;

        header("$attribute: $value");
        return $this;
    }

    public function withHeader(array $headers): Response
    {
        foreach ($headers as $key => $value) {
            $this->header($key, $value);
        }
        return $this;
    }

    public function cookie(
        string $name,
        string $value = null,
        int $minutes = 10
    ): Response {
        $data = EndeCorder::SSLEncryption(
            $value,
            self::CookieKey()
        );

        setcookie(
            $name,
            $data,
            time() + (60 * $minutes),
            '\\',
            '',
            true,
            true
        );

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
}
