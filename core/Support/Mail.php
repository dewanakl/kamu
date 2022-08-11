<?php

namespace Core\Support;

use Core\View\Render;

/**
 * Kirim email dengan SMTP
 *
 * @class Mail
 * @package Core\Support
 * @see https://github.com/snipworks/php-smtp
 */
class Mail
{
    /**
     * @var string CRLF
     */
    const CRLF = "\r\n";

    /** 
     * @var string $server
     */
    protected $server;

    /**
     * @var string $hostname
     */
    protected $hostname;

    /**
     * @var int $port 
     */
    protected $port;

    /**
     * @var resource $socket
     */
    protected $socket;

    /**
     * @var string $username 
     */
    protected $username;

    /**
     * @var string $password
     */
    protected $password;

    /**
     * @var string $subject
     */
    protected $subject;

    /**
     * @var array $to
     */
    protected $to = array();

    /**
     * @var array $from
     */
    protected $from = array();

    /**
     * @var string|null $protocol
     */
    protected $protocol = null;

    /**
     * @var string|null $htmlMessage
     */
    protected $htmlMessage = null;

    /**
     * @var bool $isTLS
     */
    protected $isTLS = false;

    /**
     * @var array $headers
     */
    protected $headers = array();

    /**
     * Init semua config dari env
     * 
     * @return void
     */
    function __construct()
    {
        $this->server = env('MAIL_HOST');
        $this->port = (int) env('MAIL_PORT');
        $this->hostname = gethostname();
        $this->username = env('MAIL_USERNAME');
        $this->password = env('MAIL_PASSWORD');
        $this->from = array(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        $protocol = env('MAIL_ENCRYPTION');

        if ($protocol == 'tls') {
            $this->isTLS = true;
        }
        $this->protocol = $protocol;
    }

    /**
     * Tambahkan header
     * 
     * @param string $key
     * @param mixed $value
     * @return Mail
     */
    private function setHeader(string $key, mixed $value = null): self
    {
        $this->headers[$key] = $value;

        return $this;
    }

    /**
     * Tambhakan alamat penerima
     *
     * @param string $address
     * @param mixed $name
     * @return Mail
     */
    public function addTo(string $address, mixed $name = null): self
    {
        $this->to[] = array($address, $name);

        return $this;
    }

    /**
     * Set subjek email
     *
     * @param string $subject
     * @return Mail
     */
    public function subjek(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Kirim html
     *
     * @param string|Render $message
     * @return Mail
     */
    public function pesan(string|Render $message): self
    {
        if ($message instanceof Render) {
            $message = (string) $message;
        }

        $this->htmlMessage = $message;

        return $this;
    }

    /**
     * Kirim email via mail server
     *
     * @return bool
     */
    public function send(): bool
    {
        $message = null;
        $this->socket = fsockopen(
            $this->getServer(),
            $this->port,
            $errno,
            $errstr,
            30
        );

        if (empty($this->socket)) {
            return false;
        }

        $this->getResponse();
        $this->sendCommand('EHLO ' . $this->hostname);

        if ($this->isTLS) {
            $this->sendCommand('STARTTLS');
            stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $this->sendCommand('EHLO ' . $this->hostname);
        }

        $this->sendCommand('AUTH LOGIN');
        $this->sendCommand(base64_encode($this->username));
        $this->sendCommand(base64_encode($this->password));
        $this->sendCommand('MAIL FROM: <' . $this->from[0] . '>');

        foreach ($this->to as $address) {
            $this->sendCommand('RCPT TO: <' . $address[0] . '>');
        }

        $boundary = md5(uniqid(microtime(true), true));

        $this->setHeader('MIME-Version', '1.0');
        $this->setHeader('Message-ID', "<$boundary@$this->hostname>");
        $this->setHeader('X-Mailer', 'PHP/' . phpversion());
        $this->setHeader('Date', date('r'));
        $this->setHeader('Subject', $this->subject);
        $this->setHeader('From', $this->formatAddress($this->from));
        $this->setHeader('Return-Path', $this->formatAddress($this->from));
        $this->setHeader('To', $this->formatAddressList($this->to));

        $this->setHeader('Content-Type', 'multipart/alternative; boundary="alt-' . $boundary . '"');

        $message .= '--alt-' . $boundary . self::CRLF;
        $message .= 'Content-Type: text/html; charset=utf-8' . self::CRLF;
        $message .= 'Content-Transfer-Encoding: base64' . self::CRLF . self::CRLF;
        $message .= chunk_split(base64_encode($this->htmlMessage)) . self::CRLF;

        $message .= '--alt-' . $boundary . self::CRLF;
        $message .= 'Content-Type: text/plain; charset=utf-8' . self::CRLF;
        $message .= 'Content-Transfer-Encoding: base64' . self::CRLF . self::CRLF;
        $message .= chunk_split(base64_encode($this->htmlMessage)) . self::CRLF;

        $message .= '--alt-' . $boundary . '--' . self::CRLF . self::CRLF;

        $headers = '';
        foreach ($this->headers as $k => $v) {
            $headers .= $k . ': ' . $v . self::CRLF;
        }

        $this->sendCommand('DATA');
        $result = $this->sendCommand($headers . self::CRLF . $message . self::CRLF . '.');
        $this->sendCommand('QUIT');
        fclose($this->socket);

        return substr($result, 0, 3) == 250;
    }

    /**
     * Server url
     *
     * @return string
     */
    protected function getServer(): string
    {
        return ($this->protocol) ? $this->protocol . '://' . $this->server : $this->server;
    }

    /**
     * Dapatkan balasannya
     * 
     * @return string
     */
    protected function getResponse(): string
    {
        $response = '';
        stream_set_timeout($this->socket, 8);
        while (($line = fgets($this->socket, 515)) !== false) {
            $response .= trim($line) . "\n";
            if (substr($line, 3, 1) == ' ') {
                break;
            }
        }

        return trim($response);
    }

    /**
     * Kirim perintah ke server email
     *
     * @param string $command
     * @return string
     */
    protected function sendCommand(string $command): string
    {
        fputs($this->socket, $command . self::CRLF);

        return $this->getResponse();
    }

    /**
     * Format alamat email dengan nama
     *
     * @param array $address
     * @return string
     */
    protected function formatAddress(array $address): string
    {
        return (empty($address[1])) ? $address[0] : '"' . addslashes($address[1]) . '" <' . $address[0] . '>';
    }

    /**
     * Format alamat email
     *
     * @param array $addresses
     * @return string
     */
    protected function formatAddressList(array $addresses): string
    {
        $data = array();
        foreach ($addresses as $address) {
            $data[] = $this->formatAddress($address);
        }

        return implode(', ', $data);
    }
}
