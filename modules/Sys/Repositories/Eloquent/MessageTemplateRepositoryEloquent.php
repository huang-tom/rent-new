<?php

namespace Modules\Sys\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Sys\Repositories\Contracts\MessageTemplateRepository;
use Modules\Sys\Repositories\Models\MessageTemplate;

/**
 * Class MessageTemplateRepositoryEloquent.
 *
 * @package Modules\Sys\Repositories\Eloquent
 */
class MessageTemplateRepositoryEloquent extends BaseRepository implements MessageTemplateRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return MessageTemplate::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
