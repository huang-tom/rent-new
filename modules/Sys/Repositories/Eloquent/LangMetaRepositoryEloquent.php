<?php

namespace Modules\Sys\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Sys\Repositories\Contracts\LangMetaRepository;
use Modules\Sys\Repositories\Models\LangMeta;

/**
 * Class LangMetaRepositoryEloquent.
 *
 * @package Modules\Sys\Repositories\Eloquent
 */
class LangMetaRepositoryEloquent extends BaseRepository implements LangMetaRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return LangMeta::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
