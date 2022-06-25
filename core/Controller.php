<?php

namespace Core;

/**
 * Base controller untuk mempermudah 
 *
 * @class Controller
 * @package Core
 */
abstract class Controller
{
    /**
     * Render template html
     *
     * @param string $view
     * @param array $param
     * @return Render
     */
    protected function view(string $view, array $param = []): Render
    {
        return App::get()->singleton(Respond::class)->view($view, $param);
    }

    /**
     * Alihkan ke url
     *
     * @param string $prm
     * @return Respond
     */
    protected function redirect(string $prm): Respond
    {
        return App::get()->singleton(Respond::class)->to($prm);
    }

    /**
     * Kembali seperti semula
     *
     * @return Respond
     */
    protected function back(): Respond
    {
        return App::get()->singleton(Respond::class)->back();
    }

    /**
     * Ubah ke json
     *
     * @param array $data
     * @param int $statusCode
     * @return Respond
     */
    protected function json(array $data, int $statusCode = 200): string|false
    {
        return App::get()->singleton(Respond::class)->json($data, $statusCode);
    }
}
