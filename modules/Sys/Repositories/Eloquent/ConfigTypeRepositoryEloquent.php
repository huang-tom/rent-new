<?php

namespace Modules\Sys\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Sys\Repositories\Contracts\ConfigTypeRepository;
use Modules\Sys\Repositories\Models\ConfigType;

/**
 * Class ConfigTypeRepositoryEloquent.
 *
 * @package Modules\Sys\Repositories\Eloquent
 */
class ConfigTypeRepositoryEloquent extends BaseRepository implements ConfigTypeRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ConfigType::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
