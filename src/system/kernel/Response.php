<?php

namespace ksoftm\system\kernel;

use ksoftm\system\utils\Cookie;
use ksoftm\system\utils\EndeCorder;

class Response
{
    /** @var string static $cookieKey cookie encrypting key. */
    protected static ?string $cookieKey = null;

    protected ?Cookie $data = null;

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

    /**
     * get and set cookie key
     *
     * @param string|null $key
     *
     * @return string|false
     */
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
        int $minutes = 10,
        string|bool $encryptKey = false
    ): Response {

        $this->data = Cookie::make($name, $value, $minutes);

        if ($encryptKey != false) {
            self::CookieKey($encryptKey);
            $this->data->encrypted(self::CookieKey());
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
    { //<<----------->> download system <<----------->>//

        if (file_exists($filePath)) {
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header("Cache-Control: no-cash, must-revalidate");

            // header("Expires: " . date("D, d M Y H:i:s", time() + 5));
            header("Expires: 0");

            header("Content-Disposition: attachment; filename=" . pathinfo($filePath, PATHINFO_BASENAME));
            header("Content-Type: application/" . pathinfo($filePath, PATHINFO_EXTENSION));
            // header("Content-Type: application/octet-stream");
            header("Content-Length: " . filesize($filePath));

            header("Content-Transfer-Encoding: binary");
            ob_clean();

            $ctx = stream_context_create();

            if (false !== ($fRes = fopen($filePath, 'r', false, $ctx))) {

                while (false !== ($d = stream_get_contents($fRes, 512 * 1024, 512 * 1024))) {
                    echo $d;
                }
            }

            exit;
        } else {
            // return view('errors._fileNotFound');
        }

        //<<-----X----->> download system <<-----X----->>//

    }
}
