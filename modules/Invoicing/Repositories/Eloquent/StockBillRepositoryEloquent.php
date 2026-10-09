<?php

namespace Modules\Invoicing\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Invoicing\Repositories\Contracts\StockBillRepository;
use Modules\Invoicing\Repositories\Models\StockBill;

/**
 * Class StockBillRepositoryEloquent.
 *
 * @package Modules\Invoicing\Repositories\Eloquent
 */
class StockBillRepositoryEloquent extends BaseRepository implements StockBillRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return StockBill::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
