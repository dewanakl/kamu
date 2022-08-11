<?php

namespace Core\Routing;

use Core\Http\Request;
use Core\Http\Respond;
use Core\Support\Validator;
use Core\View\Render;

/**
 * Base controller untuk mempermudah memanggil fungsi
 *
 * @class Controller
 * @package Core\Routing
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

    /**
     * Buat validasinya
     * 
     * @param Request $request
     * @param array $rules
     * @return Validator
     */
    protected function validate(Request $request, array $rules): Validator
    {
        return Validator::make($request->all(), $rules);
    }
}
