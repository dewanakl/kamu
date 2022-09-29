<?php

namespace Core\Routing;

use Core\Http\Request;
use Core\Http\Respond;
use Core\Valid\Validator;
use Core\View\View;

/**
 * Base controller untuk mempermudah memanggil fungsi
 *
 * @class Controller
 * @package Core\Routing
 */
abstract class Controller
{
    /**
     * View template html
     *
     * @param string $path
     * @param array $data
     * @return View
     */
    protected function view(string $path, array $data = []): View
    {
        $view = app(View::class);
        $view->variables($data);
        $view->show($path);

        return $view;
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
