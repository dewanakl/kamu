<?php

namespace Core\Support;

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
     * Carriage Return Line Feed
     * 
     * @var string CRLF
     */
    private const CRLF = "\r\n";

    /** 
     * Nama servernya
     * 
     * @var string $server
     */
    private $server;

    /**
     * Hostnamenya
     * 
     * @var string $hostname
     */
    private $hostname;

    /**
     * Email port
     * 
     * @var int $port 
     */
    private $port;

    /**
     * Email socket
     * 
     * @var resource $socket
     */
    private $socket;

    /**
     * Email username
     * 
     * @var string $username 
     */
    private $username;

    /**
     * Email password
     * 
     * @var string $password
     */
    private $password;

    /**
     * Email subject
     * 
     * @var string $subject
     */
    private $subject;

    /**
     * Kepada siapa
     * 
     * @var array $to
     */
    private $to;

    /**
     * Dari siapa
     * 
     * @var array $from
     */
    private $from;

    /**
     * Protokolnya
     * 
     * @var string|null $protocol
     */
    private $protocol;

    /**
     * Pesannya
     * 
     * @var string|null $htmlMessage
     */
    private $htmlMessage;

    /**
     * Apakah tls ?
     * 
     * @var bool $isTLS
     */
    private $isTLS;

    /**
     * Email headers
     * 
     * @var array $headers
     */
    private $headers;

    /**
     * Init semua config dari env
     * 
     * @return void
     */
    function __construct()
    {
        $this->server = env('MAIL_HOST');
        $this->port = intval(env('MAIL_PORT'));
        $this->hostname = parse_url(BASEURL, PHP_URL_HOST);
        $this->username = env('MAIL_USERNAME');
        $this->password = env('MAIL_PASSWORD');
        $this->from = array(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        $this->protocol = env('MAIL_ENCRYPTION');

        if ($this->protocol == 'tcp') {
            $this->isTLS = true;
        }
    }

    /**
     * Tambahkan header
     * 
     * @param string $key
     * @param mixed $value
     * @return void
     */
    private function setHeader(string $key, mixed $value): void
    {
        $this->headers[$key] = $value;
    }

    /**
     * Server url
     *
     * @return string
     */
    private function getServer(): string
    {
        return ($this->protocol) ? $this->protocol . '://' . $this->server : $this->server;
    }

    /**
     * Dapatkan balasannya
     * 
     * @return string
     */
    private function getResponse(): string
    {
        $response = '';
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
    private function sendCommand(string $command): string
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
    private function formatAddress(array $address): string
    {
        return empty($address[1]) ? $address[0] : '"' . addslashes($address[1]) . '" <' . $address[0] . '>';
    }

    /**
     * Format alamat email
     *
     * @param array $addresses
     * @return string
     */
    private function formatAddressList(array $addresses): string
    {
        $data = [];
        foreach ($addresses as $address) {
            $data[] = $this->formatAddress($address);
        }

        return implode(', ', $data);
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
     * @param mixed $message
     * @return Mail
     */
    public function pesan(mixed $message): self
    {
        $this->htmlMessage = strval($message);

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
        $this->socket = fsockopen($this->getServer(), $this->port);

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
        $this->setHeader('Message-ID', "<{$boundary}@{$this->hostname}>");
        $this->setHeader('Date', date('r') . ' GMT');
        $this->setHeader('Subject', $this->subject);
        $this->setHeader('From', $this->formatAddress($this->from));
        $this->setHeader('To', $this->formatAddressList($this->to));
        $this->setHeader('Return-Path', $this->from[0]);
        $this->setHeader('Reply-To', $this->from[0]);
        $this->setHeader('List-Unsubscribe', '<mailto:' . $this->from[0] . '?subject=unsubscribe>');

        $this->setHeader('Content-Type', 'multipart/alternative; boundary="alt-' . $boundary . '"');

        $message .= '--alt-' . $boundary . self::CRLF;
        $message .= 'Content-Type: text/html; charset=utf-8' . self::CRLF;
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
}
