<?php

/**
 * Template untuk membuat file migrasi dengan saya console
 * 
 * @return string
 */

return '<?php

use Core\Database\Migration;
use Core\Database\Schema;
use Core\Database\Table;

return new class implements Migration
{
    /**
     * Jalankan migrasi
     *
     * @return void
     */
    public function up()
    {
        Schema::create(\'NAME\', function (Table $table) {
            $table->id();

            //

            $table->timeStamp();
        });
    }

    /**
     * Kembalikan seperti semula
     *
     * @return void
     */
    public function down()
    {
        Schema::drop(\'NAME\');
    }
};
';
