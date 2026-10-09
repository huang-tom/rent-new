<?php

namespace Modules\Sys\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class LangStandard.
 *
 * @package Modules\Sys\Repositories\Models
 */
class LangStandard extends Model
{

    protected $table      = 'sys_lang_standard';
    protected $primaryKey = 'zh_CN';
    protected $keyType    = 'string';
    public $timestamps    = false;

    protected $guarded = [];
    protected $casts = [
        'is_imp'  => 'boolean',
        'is_used' => 'boolean',
        'frontend' => 'boolean',
        'backend' => 'boolean',
        'java' => 'boolean'
    ];

}
