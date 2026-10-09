<?php

namespace Modules\Sys\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Sys\Repositories\Contracts\LangStandardRepository;
use Modules\Sys\Repositories\Models\LangStandard;

/**
 * Class LangStandardRepositoryEloquent.
 *
 * @package Modules\Sys\Repositories\Eloquent
 */
class LangStandardRepositoryEloquent extends BaseRepository implements LangStandardRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return LangStandard::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
