<?php

namespace Modules\Trade\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Trade\Repositories\Contracts\NewRentOrderTagRepository;
use Modules\Trade\Repositories\Models\NewRentOrderTag;

class NewRentOrderTagRepositoryEloquent extends BaseRepository implements NewRentOrderTagRepository
{
    public function model()
    {
        return NewRentOrderTag::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
