<?php

namespace Modules\Sys\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Sys\Repositories\Contracts\PageBaseRepository;
use Modules\Sys\Repositories\Models\PageBase;

/**
 * Class PageBaseRepositoryEloquent.
 *
 * @package Modules\Sys\Repositories\Eloquent
 */
class PageBaseRepositoryEloquent extends BaseRepository implements PageBaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return PageBase::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
