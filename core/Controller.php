<?php

namespace Core;

abstract class Controller
{
    protected function view(string $view, array $param = []): Render
    {
        $template = new Render($view);
        $template->setData($param);
        $template->show();

        return $template;
    }

    protected function flash(string $key, string $value): Controller
    {
        session()->set($key, $value);
        return $this;
    }

    protected function to(string $redirect): void
    {
        response($redirect);
    }

    protected function json(array $data, int $statusCode = 200): string|false
    {
        http_response_code($statusCode);
        return resJson($data);
    }

    protected function back(): void
    {
        $this->to(session()->get('oldRoute', '/'));
    }
}
