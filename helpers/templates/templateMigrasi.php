<?php

/**
 * Template untuk membuat file migrasi dengan saya console
 * 
 * @return array
 */

$create = '<?php

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

$add = '<?php

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
        Schema::table(\'NAME\', function (Table $table) {
            $table->addColumn(function ($table) {
                
                //

            });
        });
    }

    /**
     * Kembalikan seperti semula
     *
     * @return void
     */
    public function down()
    {
        Schema::table(\'NAME\', function (Table $table) {
            // $table->dropColumn(\'Columns Name\');
        });
    }
};
';

return [$create, $add];
