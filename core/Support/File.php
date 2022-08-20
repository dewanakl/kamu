<?php

namespace Core\Support;

use Core\Http\Request;

/**
 * File uploaded
 *
 * @class File
 * @package Core\Support
 */
class File
{
    /**
     * Request object
     * 
     * @var Request $request
     */
    private $request;

    /**
     * File object
     * 
     * @var object $file
     */
    private $file;

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
     * Dapatkan file dari request
     * 
     * @param string $name
     * @return void
     */
    public function getFromRequest(string $name): void
    {
        $this->file = (object) $this->request->get($name);
    }

    /**
     * Dapatkan nama aslinya
     * 
     * @return string
     */
    public function getClientOriginalName(): string
    {
        return pathinfo($this->file->name, PATHINFO_FILENAME);
    }

    /**
     * Dapatkan extensi aslinya
     * 
     * @return string
     */
    public function getClientOriginalExtension(): string
    {
        return pathinfo($this->file->name, PATHINFO_EXTENSION);
    }

    /**
     * Apakah ada file yang di upload ?
     * 
     * @return bool
     */
    public function exists(): bool
    {
        return $this->file->error == 0;
    }

    /**
     * Dapatkan extensi aslinya dengan mime
     * 
     * @return string
     */
    public function extension(): string
    {
        return $this->file->type;
    }

    /**
     * Bikin namanya unik
     * 
     * @return string
     */
    public function hashName(): string
    {
        return bin2hex(random_bytes(10));
    }

    /**
     * Simpan filenya
     * 
     * @param string $name
     * @param string $folder
     * @return bool
     */
    public function store(string $name, string $folder = 'shared'): bool
    {
        return move_uploaded_file(
            $this->file->tmp_name,
            __DIR__ . '/../../' . $folder . '/' . $name . '.' . $this->getClientOriginalExtension()
        );
    }

    /**
     * Simpan filenya secara bertahap
     * 
     * @param string $name
     * @param string $folder
     * @return bool
     */
    public function chunk(string $name, string $folder = 'shared'): bool
    {
        $filePath = __DIR__ . '/../../' . $folder . '/' . $name;

        $chunk = isset($this->request->chunk) ? intval($this->request->chunk) : 0;
        $chunks = isset($this->request->chunks) ? intval($this->request->chunks) : 0;

        $out = fopen("{$filePath}.part", $chunk == 0 ? 'wb' : 'ab');
        if ($out) {
            $in = fopen($this->file->tmp_name, 'rb');
            if ($in) {
                while ($buff = fread($in, 4096)) {
                    fwrite($out, $buff);
                }

                fclose($in);
            } else {
                fclose($out);
                return false;
            }
            fclose($out);
        } else {
            return false;
        }

        if (!$chunks || $chunk == $chunks - 1) {
            rename("{$filePath}.part", $filePath);
        }

        return unlink($this->file->tmp_name);
    }
}
