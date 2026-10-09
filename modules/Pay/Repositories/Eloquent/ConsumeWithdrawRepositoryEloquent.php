<?php

namespace Modules\Pay\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Pay\Repositories\Contracts\ConsumeWithdrawRepository;
use Modules\Pay\Repositories\Models\ConsumeWithdraw;

/**
 * Class ConsumeWithdrawRepositoryEloquent.
 *
 * @package Modules\Pay\Repositories\Eloquent
 */
class ConsumeWithdrawRepositoryEloquent extends BaseRepository implements ConsumeWithdrawRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ConsumeWithdraw::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
