<?php

namespace Modules\Sys\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class LangMeta.
 *
 * @package Modules\Sys\Repositories\Models
 */
class LangMeta extends Model
{

    protected $table = 'sys_lang_meta';
    protected $primaryKey = 'meta_id';
    protected $keyType = 'string';
    public $timestamps = false;

    protected $guarded = [];

}
