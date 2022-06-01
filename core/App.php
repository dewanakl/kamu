<?php

namespace Core;

final class App
{
    private static Application $app;

    public static function new(Application $app): Application
    {
        self::$app = $app;
        return static::get();
    }

    public static function get(): Application
    {
        return self::$app;
    }
}
