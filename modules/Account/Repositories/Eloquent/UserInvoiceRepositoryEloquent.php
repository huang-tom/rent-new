<?php

namespace Modules\Account\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Account\Repositories\Contracts\UserInvoiceRepository;
use Modules\Account\Repositories\Models\UserInvoice;

/**
 * Class UserInvoiceRepositoryEloquent.
 *
 * @package Modules\Account\Repositories\Eloquent
 */
class UserInvoiceRepositoryEloquent extends BaseRepository implements UserInvoiceRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return UserInvoice::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
