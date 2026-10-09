<?php

namespace Modules\Account\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Account\Repositories\Contracts\UserMessageRepository;
use Modules\Account\Repositories\Models\UserMessage;

/**
 * Class UserMessageRepositoryEloquent.
 *
 * @package Modules\Account\Repositories\Eloquent
 */
class UserMessageRepositoryEloquent extends BaseRepository implements UserMessageRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return UserMessage::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
