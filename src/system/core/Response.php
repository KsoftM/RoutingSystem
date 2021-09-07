<?php

namespace ksoftm\system\core;


class Response
{
    // return response($content)->header('Content-Type', $type)
    // ->cookie('name', 'value', $minutes);

    // return response()->download($pathToFile, $name, $headers);

    // return response()->json($data, 200, $headers);

    // return Response::make($contents, 200, $headers);

    // return response($content)->withHeaders([
    //     'Content-Type' => $type,
    //     'X-Header-One' => 'Header Value',
    // ]);

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
