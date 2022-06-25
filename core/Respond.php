<?php

namespace Core;

/**
 * Respond dari request yang masuk
 *
 * @class Respond
 * @package Core
 */
class Respond
{
    /**
     * Request obj
     * 
     * @var Request $request
     */
    private Request $request;

    /**
     * Session obj
     * 
     * @var Session $session
     */
    private Session $session;

    /**
     * Url redirect
     * 
     * @var string|null $redirect
     */
    private $redirect;

    /**
     * Init objek
     * 
     * @param Request $request
     * @param Session $session
     * @return void
     */
    function __construct(Request $request, Session $session)
    {
        $this->request = $request;
        $this->session = $session;
    }

    /**
     * Alihkan halaman
     * 
     * @param string $prm
     * @return self
     */
    public function to(string $prm): self
    {
        $this->redirect = $prm;
        return $this;
    }

    /**
     * Isi dengan pesan
     * 
     * @param string $key
     * @param string $value
     * @return self
     */
    public function with(string $key, string $value): self
    {
        $this->session->set($key, $value);
        return $this;
    }

    /**
     * Kembali ke halaman yang dulu
     * 
     * @return self
     */
    public function back(): self
    {
        return $this->to($this->session->get('oldRoute', '/'));
    }

    /**
     * Ubah ke json
     * 
     * @param array $data
     * @param int $statusCode 
     * @return string|false
     */
    public function json(array $data, int $statusCode = 200): string|false
    {
        $this->httpCode($statusCode);
        header('Content-Type: application/json');
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * Tampilkan html
     * 
     * @param string $view
     * @param array $param
     * @return Render
     */
    public function view(string $view, array $param = []): Render
    {
        $template = App::get()->make(Render::class, array($view));
        $template->setData($param);
        $template->show();
        return $template;
    }

    /**
     * Alihkan halaman sesuai url
     * 
     * @param string $uri
     * @return void
     */
    public function redirect(string $uri): void
    {
        $this->session->unset('token');
        header('Location: ' . BASEURL . $uri, true, 302);
        $this->terminate();
    }

    /**
     * Tampilkan responnya
     * 
     * @param mixed $obj
     * @return void
     */
    public function send(mixed $obj): void
    {
        if (is_string($obj) || $obj instanceof Render) {
            $this->session->set('oldRoute', $this->request->server('REQUEST_URI'));
            $this->session->unset('old');
            $this->session->unset('error');
            $this->terminate($obj);
        } else if ($obj instanceof Respond) {
            if (!is_null($this->redirect)) {
                $this->redirect($this->redirect);
            }
        }

        if (!is_null($obj)) {
            dd($obj);
        }
    }

    /**
     * Stop responnya
     * 
     * @param mixed $prm
     * @return void
     */
    public function terminate(mixed $prm = null): void
    {
        if ($prm) {
            echo $prm;
        }

        exit;
    }

    /**
     * Respon kode
     * 
     * @param int $code
     * @return void
     */
    public function httpCode(int $code): void
    {
        http_response_code($code);
    }
}
