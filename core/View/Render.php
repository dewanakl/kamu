<?php

namespace Core\View;

use InvalidArgumentException;

/**
 * Tampilkan html dan juga injek variabel
 *
 * @class Render
 * @package Core\View
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
     * 
     * @throws InvalidArgumentException
     */
    function __construct(string $path)
    {
        $this->path = __DIR__ . '/../../views/' . $path . '.php';
        if (!file_exists($this->path)) {
            throw new InvalidArgumentException(sprintf("Could not show. The file %s could not be found", $this->path));
        }
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
     */
    public function show(): void
    {
        extract($this->variables, EXTR_SKIP);

        ob_start();

        require_once $this->path;
        $this->content = ob_get_contents();

        ob_end_clean();
    }
}
