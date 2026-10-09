<?php

namespace Modules\Sys\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Sys\Repositories\Contracts\ContractTypeRepository;
use Modules\Sys\Repositories\Models\ContractType;

/**
 * Class ContractTypeRepositoryEloquent.
 *
 * @package Modules\Sys\Repositories\Eloquent
 */
class ContractTypeRepositoryEloquent extends BaseRepository implements ContractTypeRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ContractType::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
