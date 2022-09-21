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
            throw new InvalidArgumentException(sprintf('File "%s" gk adaa', $path . '.php'));
        }
    }

    /**
     * Magic to string
     * 
     * @return string
     */
    function __toString()
    {
        $content = $this->content;

        $this->path = null;
        $this->content = null;
        $this->variables = [];

        return $content;
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

        include_once $this->path;
        $this->content = ob_get_contents();

        ob_end_clean();
    }
}
