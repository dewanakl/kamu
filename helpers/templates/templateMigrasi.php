<?php

/**
 * Template untuk membuat file migrasi dengan saya console
 * 
 * @return string
 */

return '<?php

use Core\Schema;
use Core\Table;

return new class
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
