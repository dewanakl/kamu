<?php

namespace Core;

use InvalidArgumentException;

/**
 * Tampilkan html dan juga injek variabel
 *
 * @class Render
 * @package Core
 */
class Render
{
    /**
     * Path file html
     * 
     * @var string $path
     */
    private $path;

    /**
     * Isi file html
     * 
     * @var string $path
     */
    private $content;

    /**
     * Injek variabel
     * 
     * @var array $variables
     */
    private $variables;

    /**
     * Init objek
     * 
     * @param string $path
     * @return void
     */
    function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * Magic to string
     * 
     * @return string
     */
    function __toString()
    {
        return $this->content;
    }

    /**
     * Set variabel ke template html
     * 
     * @param array $variables
     * @return void
     */
    public function setData(array $variables = []): void
    {
        $this->variables = $variables;
    }

    /**
     * Eksekusi template html
     * 
     * @return void
     * 
     * @throws InvalidArgumentException
     */
    public function show(): void
    {
        $path = __DIR__ . '/../views/' . $this->path . '.php';

        if (!file_exists($path)) {
            throw new InvalidArgumentException(sprintf("Could not show. The file %s could not be found", $path));
        }

        extract($this->variables, EXTR_SKIP);

        ob_start();

        require_once $path;
        $this->content = ob_get_contents();

        ob_end_clean();
    }
}
