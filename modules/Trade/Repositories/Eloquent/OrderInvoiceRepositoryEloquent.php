<?php

namespace Modules\Trade\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Trade\Repositories\Contracts\OrderInvoiceRepository;
use Modules\Trade\Repositories\Models\OrderInvoice;

/**
 * Class OrderInvoiceRepositoryEloquent.
 *
 * @package Modules\Trade\Repositories\Eloquent
 */
class OrderInvoiceRepositoryEloquent extends BaseRepository implements OrderInvoiceRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return OrderInvoice::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
