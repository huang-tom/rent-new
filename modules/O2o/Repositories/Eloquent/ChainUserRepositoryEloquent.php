<?php

namespace Modules\O2o\Repositories\Eloquent;

use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\O2o\Repositories\Contracts\ChainUserRepository;
use Modules\O2o\Repositories\Models\ChainUser;
use Kuteshop\Core\Repository\BaseRepository;

/**
 * Class ChainUserRepositoryEloquent.
 *
 * @package Modules\O2o\Repositories\Eloquent
 */
class ChainUserRepositoryEloquent extends BaseRepository implements ChainUserRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ChainUser::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
