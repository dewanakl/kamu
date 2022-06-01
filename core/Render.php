<?php

namespace Core;

use InvalidArgumentException;

class Render
{
    private $path;
    private $content;
    private $variables;

    function __construct(string $path)
    {
        $this->path = $path;
    }

    function __toString()
    {
        return $this->content;
    }

    public function setData(array $variables = []): void
    {
        $this->variables = $variables;
    }

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
