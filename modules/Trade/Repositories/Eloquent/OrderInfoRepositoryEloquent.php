<?php

namespace Modules\Trade\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Trade\Repositories\Contracts\OrderInfoRepository;
use Modules\Trade\Repositories\Models\OrderInfo;

/**
 * Class OrderInfoRepositoryEloquent.
 *
 * @package Modules\Trade\Repositories\Eloquent
 */
class OrderInfoRepositoryEloquent extends BaseRepository implements OrderInfoRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return OrderInfo::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
