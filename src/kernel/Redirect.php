<?php

namespace ksoftm\system\kernel;

use ksoftm\system\utils\Session;

class Redirect
{
    public const REDIRECT_KEY = 'redirect_back';
    /**
     * Class constructor.
     */
    protected function __construct(string $routName)
    {
        $this->routName = $routName;
    }

    public static function init(): void
    {
        Session::flash(self::REDIRECT_KEY, filter_input(INPUT_SERVER, 'REQUEST_URI'));
    }

    public static function next(string $routeName, int $responseCode = 303): void
    {
        $path = Route::realPath($routeName);
        if (!empty($path) && $path != false) {
            Response::make()
                ->setStateCode($responseCode)
                ->header("Location", $path);
            exit;
        }
    }

    public static function back(string $default = '/', int $responseCode = 303): void
    {
        $path = Session::getOnce(self::REDIRECT_KEY, $default);

        if (!empty($path)) {
            Response::make()
                ->setStateCode($responseCode)
                ->header("Location", $path);
            exit;
        }
    }
}
