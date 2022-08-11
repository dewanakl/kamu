<?php

namespace Core\Database;

interface Migration
{
    /**
     * Jalankan migrasi
     *
     * @return void
     */
    public function up();

    /**
     * Kembalikan seperti semula
     *
     * @return void
     */
    public function down();
}
