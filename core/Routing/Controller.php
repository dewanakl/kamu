<?php

namespace Core\Routing;

use Core\Http\Request;
use Core\Http\Respond;
use Core\Valid\Validator;
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
     * @param string $path
     * @param array $data
     * @return Render
     */
    protected function view(string $path, array $data = []): Render
    {
        return extend($path, $data);
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
