<?php

namespace Core\Database;

interface Generator
{
    /**
     * Jalankan generator ke database
     *
     * @return void
     */
    public function run();
}
