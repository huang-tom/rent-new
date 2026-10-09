<?php

namespace Modules\Sys\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Sys\Repositories\Contracts\NumberSeqRepository;
use Modules\Sys\Repositories\Models\NumberSeq;

/**
 * Class NumberSeqRepositoryEloquent.
 *
 * @package Modules\Sys\Repositories\Eloquent
 */
class NumberSeqRepositoryEloquent extends BaseRepository implements NumberSeqRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return NumberSeq::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
