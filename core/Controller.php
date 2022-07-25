<?php

namespace Core;

/**
 * Base controller untuk mempermudah memanggil fungsi
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
        return app(Respond::class)->view($view, $param);
    }

    /**
     * Alihkan ke url
     *
     * @param string $prm
     * @return Respond
     */
    protected function redirect(string $prm): Respond
    {
        return app(Respond::class)->to($prm);
    }

    /**
     * Kembali seperti semula
     *
     * @return Respond
     */
    protected function back(): Respond
    {
        return app(Respond::class)->back();
    }

    /**
     * Ubah ke json
     *
     * @param mixed $data
     * @param int $statusCode
     * @return string|false
     */
    protected function json(mixed $data, int $statusCode = 200): string|false
    {
        return app(Respond::class)->json($data, $statusCode);
    }
}
