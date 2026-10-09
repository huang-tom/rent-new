<?php

namespace Modules\Sys\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Sys\Repositories\Contracts\CurrencyBaseRepository;
use Modules\Sys\Repositories\Models\CurrencyBase;

/**
 * Class CurrencyBaseRepositoryEloquent.
 *
 * @package Modules\Sys\Repositories\Eloquent
 */
class CurrencyBaseRepositoryEloquent extends BaseRepository implements CurrencyBaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return CurrencyBase::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
