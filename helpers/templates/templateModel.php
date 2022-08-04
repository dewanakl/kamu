<?php

/**
 * Template untuk membuat file model dengan saya console
 * 
 * @return string
 */

return '<?php

namespace Models;

use Core\Model;

final class NAME extends Model
{
    protected $table = \'NAMe\';

    protected $primaryKey = \'id\';

    protected $dates = [
        \'created_at\',
        \'updated_at\',
    ];
}
';
