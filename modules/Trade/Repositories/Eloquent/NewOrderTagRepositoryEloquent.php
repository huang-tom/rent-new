<?php

namespace Modules\Trade\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Trade\Repositories\Contracts\NewOrderTagRepository;
use Modules\Trade\Repositories\Models\NewOrderTag;

class NewOrderTagRepositoryEloquent extends BaseRepository implements NewOrderTagRepository
{
    public function model()
    {
        return NewOrderTag::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
