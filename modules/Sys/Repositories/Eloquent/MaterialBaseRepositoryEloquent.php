<?php

namespace Modules\Sys\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Sys\Repositories\Contracts\MaterialBaseRepository;
use Modules\Sys\Repositories\Models\MaterialBase;

/**
 * Class MaterialBaseRepositoryEloquent.
 *
 * @package Modules\Sys\Repositories\Eloquent
 */
class MaterialBaseRepositoryEloquent extends BaseRepository implements MaterialBaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return MaterialBase::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
