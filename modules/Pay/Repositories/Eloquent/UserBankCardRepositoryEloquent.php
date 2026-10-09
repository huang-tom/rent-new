<?php

namespace Modules\Pay\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Pay\Repositories\Contracts\UserBankCardRepository;
use Modules\Pay\Repositories\Models\UserBankCard;

/**
 * Class UserBankCardRepositoryEloquent.
 *
 * @package Modules\Pay\Repositories\Eloquent
 */
class UserBankCardRepositoryEloquent extends BaseRepository implements UserBankCardRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return UserBankCard::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
