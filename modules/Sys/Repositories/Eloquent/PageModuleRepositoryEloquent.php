<?php

namespace Modules\Sys\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Sys\Repositories\Contracts\PageModuleRepository;
use Modules\Sys\Repositories\Models\PageModule;

/**
 * Class PageModuleRepositoryEloquent.
 *
 * @package Modules\Sys\Repositories\Eloquent
 */
class PageModuleRepositoryEloquent extends BaseRepository implements PageModuleRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return PageModule::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
