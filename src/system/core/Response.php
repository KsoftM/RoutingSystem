<?php

namespace ksoftm\system\core;


class Response
{
    
    public static function make(string $contents, int $responseCode, array $headers): Response
    {
        # code...

        return new Response();
    }


    public function header(string $content, string $type): Response
    {
        # code...

        return $this;
    }

    public function withHeader(array $headers): Response
    {
        # code...

        return $this;
    }

    public function cookie(string $name, string $value, int $minutes): Response
    {
        # code...

        return $this;
    }

    public function view(string $content, string $value): Response
    {
        # code...

        return $this;
    }
}
