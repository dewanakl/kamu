<?php

namespace Core\Http;

use Core\View\Render;
use Core\View\View;

/**
 * Respond dari request yang masuk
 *
 * @class Respond
 * @package Core\Http
 */
class Respond
{
    /**
     * Session obj
     * 
     * @var Session $session
     */
    private $session;

    /**
     * Url redirect
     * 
     * @var string|null $redirect
     */
    private $redirect;

    /**
     * Init objek
     * 
     * @param Session $session
     * @return void
     */
    function __construct(Session $session)
    {
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
        return $this->to($this->session->get('_oldroute', '/'));
    }

    /**
     * Alihkan halaman sesuai url
     * 
     * @param string $uri
     * @return void
     */
    public function redirect(string $uri): void
    {
        $this->session->unset('_token');
        $this->session->send();

        $uri = str_contains($uri, BASEURL) ? $uri : BASEURL . $uri;

        http_response_code(302);
        header('HTTP/1.1 302 Found', true, 302);
        header('Location: ' . $uri, true, 302);
        $this->terminate();
    }

    /**
     * Tampilkan responnya
     * 
     * @param mixed $respond
     * @return void
     */
    public function send(mixed $respond): void
    {
        if (is_string($respond) || $respond instanceof Render || $respond instanceof View) {
            if ($respond instanceof Render || $respond instanceof View) {
                $this->session->set('_oldroute', app(Request::class)->server('REQUEST_URI'));
                $this->session->unset('old');
                $this->session->unset('error');
            }

            $this->session->send();
            $this->terminate($respond);
        }

        if ($respond instanceof Respond) {
            if (!is_null($this->redirect)) {
                $this->redirect($this->redirect);
            }
        }

        if ($respond instanceof Stream) {
            $respond->process();
            $this->terminate();
        }

        if (!is_null($respond)) {
            dd($respond);
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
}
