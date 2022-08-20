<?php

namespace Core\Http;

/**
 * Stream sebuah file
 *
 * @class Stream
 * @package Core\Http
 * @see https://gist.github.com/kosinix/4cf0d432638817888149
 */
class Stream
{
    /**
     * Open file
     * 
     * @var resource|false $file
     */
    private $file;

    /**
     * Basename file
     * 
     * @var string $name
     */
    private $name;

    /**
     * Hash file
     * 
     * @var string $boundary
     */
    private $boundary;

    /**
     * Size file
     * 
     * @var int|false $size
     */
    private $size;

    /**
     * Type file
     * 
     * @var string $type
     */
    private $type;

    /**
     * Download file
     * 
     * @var bool $download
     */
    private $download;

    /**
     * Request object
     * 
     * @var Request $request
     */
    private $request;

    /**
     * Init objek
     * 
     * @param Request $request
     * @return void
     */
    function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Init file
     * 
     * @param string $file
     * @return void
     */
    private function init(string $file): void
    {
        if (!is_file($file)) {
            notFound();
        }

        header_remove();
        header('Cache-Control: public');

        $timeFile = filemtime($file);
        $hashFile = md5($file);

        if (strtotime($this->request->server('HTTP_IF_MODIFIED_SINCE')) == $timeFile || trim($this->request->server('HTTP_IF_NONE_MATCH')) == $hashFile) {
            http_response_code(304);
            header('HTTP/1.1 304 Not Modified');
            exit;
        }

        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $timeFile) . ' GMT');
        header('Etag: ' . $hashFile);

        set_time_limit(0);
        $this->file = fopen($file, 'r');
        $this->name = basename($file);
        $this->boundary = $hashFile;
        $this->size = filesize($file);
        $this->type = $this->ftype($file);
        $this->download = false;
    }

    /**
     * Send single file
     * 
     * @param string $range
     * @return void
     */
    private function pushSingle(string $range): void
    {
        $start = $end = 0;
        $this->getRange($range, $start, $end);

        header('Content-Length: ' . ($end - $start + 1));
        header(sprintf('Content-Range: bytes %d-%d/%d', $start, $end, $this->size));

        fseek($this->file, $start);
        $this->readBuffer($end - $start + 1);
        $this->readFile();
    }

    /**
     * Send multi file
     * 
     * @param array $ranges
     * @return void
     */
    private function pushMulti(array $ranges): void
    {
        $length = $start = $end = 0;
        $tl = 'Content-Type: ' . $this->type . "\r\n";
        $formatRange = "Content-Range: bytes %d-%d/%d\r\n\r\n";

        foreach ($ranges as $range) {
            $this->getRange($range, $start, $end);
            $length += strlen("\r\n--" . $this->boundary . "\r\n");
            $length += strlen($tl);
            $length += strlen(sprintf($formatRange, $start, $end, $this->size));
            $length += $end - $start + 1;
        }

        $length += strlen("\r\n--" . $this->boundary . "--\r\n");

        header('Content-Type: multipart/byteranges; boundary=' . $this->boundary);
        header('Content-Length: ' . $length);

        foreach ($ranges as $range) {
            $this->getRange($range, $start, $end);
            echo "\r\n--" . $this->boundary . "\r\n";
            echo $tl;
            echo sprintf($formatRange, $start, $end, $this->size);
            fseek($this->file, $start);
            $this->readBuffer($end - $start + 1);
        }

        echo "\r\n--" . $this->boundary . "--\r\n";
    }

    /**
     * Get range file
     * 
     * @param string $range
     * @param int &$start
     * @param int &$end
     * @return void
     */
    private function getRange(string $range, int &$start, int &$end): void
    {
        list($start, $end) = explode('-', $range);
        $fileSize = $this->size;

        if ($start == '') {
            $tmp = $end;
            $end = $fileSize - 1;
            $start = $fileSize - $tmp;
            if ($start < 0) {
                $start = 0;
            }
        } else {
            if ($end == '' || $end > $fileSize - 1) {
                $end = $fileSize - 1;
            }
        }

        if ($start > $end) {
            header('Status: 416 Requested Range Not Satisfiable');
            header('Content-Range: */' . $fileSize);
            exit;
        }
    }

    /**
     * Read file
     * 
     * @return void
     */
    private function readFile(): void
    {
        while (!feof($this->file)) {
            echo fgets($this->file);
        }
    }

    /**
     * Read buffer file
     * 
     * @param int $bytes
     * @param int $size
     * @return void
     */
    private function readBuffer(int $bytes, int $size = 8192): void
    {
        $bytesLeft = $bytes;
        while ($bytesLeft > 0 && !feof($this->file)) {
            $bytesRead = ($bytesLeft > $size) ? $size : $bytesLeft;
            $bytesLeft -= $bytesRead;
            echo fread($this->file, $bytesRead);
        }
    }

    /**
     * Get type file
     * 
     * @param ?string $typeFile
     * @return string
     */
    private function ftype(?string $typeFile = null): string
    {
        if ($this->download) {
            return 'application/octet-stream';
        }

        $mimeTypes = [
            'txt' => 'text/plain',
            'text' => 'text/plain',
            'html' => 'text/plain',
            'php' => 'text/plain',
            'css' => 'text/css',
            'js' => 'text/javascript',
            'png' => 'image/png',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/ico',
            'svg' => 'image/svg+xml',
            'mp4' => 'video/mp4',
            'mkv' => 'video/mp4',
            'mp3' => 'audio/mpeg',
            'json' => 'application/json',
            'pdf' => 'application/pdf'
        ];

        $typeFile = strtolower(pathinfo($typeFile, PATHINFO_EXTENSION));

        if (empty($mimeTypes[$typeFile])) {
            return 'application/octet-stream';
        }

        return $mimeTypes[$typeFile];
    }

    /**
     * Process file
     * 
     * @return void
     */
    public function process(): void
    {
        $ranges = [];
        $t = 0;

        if ($this->request->method() == 'GET' && ($this->request->server('HTTP_RANGE') !== null)) {
            $range = substr(stristr(trim($this->request->server('HTTP_RANGE')), 'bytes='), 6);
            $ranges = explode(',', $range);
            $t = count($ranges);
        }

        header('Accept-Ranges: bytes');
        header('Content-Type: ' . $this->type);

        if ($this->type == 'application/octet-stream') {
            header(sprintf('Content-Disposition: attachment; filename="%s"', $this->name));
            header('Content-Transfer-Encoding: binary');
        }

        if ($t > 0) {
            header('HTTP/1.1 206 Partial Content');
            ($t === 1) ? $this->pushSingle($range) : $this->pushMulti($ranges);
        } else {
            header('Content-Length: ' . $this->size);
            $this->readFile();
        }

        fclose($this->file);
    }

    /**
     * Download file
     * 
     * @return self
     */
    public function download(): self
    {
        $this->download = true;
        $this->type = $this->ftype();
        return $this;
    }

    /**
     * Send file
     * 
     * @param string $filename
     * @return self
     */
    public function send(string $filename): self
    {
        $this->init($filename);
        return $this;
    }
}
