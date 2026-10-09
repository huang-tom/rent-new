<?php

namespace Modules\Pay\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Pay\Repositories\Contracts\ConsumeDepositRepository;
use Modules\Pay\Repositories\Models\ConsumeDeposit;

/**
 * Class ConsumeDepositRepositoryEloquent.
 *
 * @package Modules\Pay\Repositories\Eloquent
 */
class ConsumeDepositRepositoryEloquent extends BaseRepository implements ConsumeDepositRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ConsumeDeposit::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
