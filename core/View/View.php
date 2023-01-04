<?php

namespace Core\View;

/**
 * Template view dengan parent
 *
 * @class View
 * @package \Core\View
 */
class View
{
    /**
     * Data dari setiap section
     * 
     * @var array $section
     */
    private $section;

    /**
     * Variabel yang di inject
     * 
     * @var array $variables
     */
    private $variables;

    /**
     * Nama parentnya
     * 
     * @var string|null $parent
     */
    private $parent;

    /**
     * Content final
     * 
     * @var Render|null $content
     */
    private $content;

    /**
     * Magic to string
     * 
     * @return string
     */
    function __toString()
    {
        $content = strval($this->content);
        clear_ob();

        $this->section = [];
        $this->variables = [];
        $this->parent = null;
        $this->content = null;

        return $content;
    }

    /**
     * Show html template
     * 
     * @param string $name
     * @return void
     */
    public function show(string $name): void
    {
        $this->parent = null;
        $this->content = $this->including($name);

        if (!is_null($this->parent)) {
            $this->content = null;
            $this->show($this->parent);
        }
    }

    /**
     * Insert variabel
     * 
     * @param array $variables
     * @return void
     */
    public function variables(array $variables = []): void
    {
        $this->variables = array_merge($this->variables ?? [], $variables);
    }

    /**
     * Set parent html
     * 
     * @param string $name
     * @param array $variables
     * @return void
     */
    public function parents(string $name, array $variables = []): void
    {
        $this->parent = $name;
        $this->variables($variables);
    }

    /**
     * Masukan html opsional
     * 
     * @param string $name
     * @return Render
     */
    public function including(string $name): Render
    {
        return render($name, $this->variables ?? []);
    }

    /**
     * Bagian awal dari html
     * 
     * @param string $name
     * @return void
     */
    public function section(string $name): void
    {
        $this->section[$name] = null;
        ob_start();
    }

    /**
     * Tampilkan bagian dari html
     * 
     * @param string $name
     * @return string|null
     */
    public function content(string $name): string|null
    {
        $content = @$this->section[$name] ?? null;
        $this->section[$name] = null;

        return $content;
    }

    /**
     * Bagian akhir dari html
     * 
     * @param string $name
     * @return void
     */
    public function endsection(string $name): void
    {
        $this->section[$name] = strval(ob_get_contents());
        ob_end_clean();
    }
}
