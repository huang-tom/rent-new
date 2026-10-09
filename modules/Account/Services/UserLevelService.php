<?php

namespace Modules\Account\Services;

use App\Exceptions\ErrorException;
use Kuteshop\Core\Service\BaseService;
use Modules\Account\Repositories\Contracts\UserInfoRepository;
use Modules\Account\Repositories\Contracts\UserLevelRepository;

/**
 * Class UserLevelService.
 *
 * @package Modules\Account\Services
 */
class UserLevelService extends BaseService
{
    private $userInfoRepository;

    public function __construct(UserLevelRepository $userLevelRepository, UserInfoRepository $userInfoRepository)
    {
        $this->repository = $userLevelRepository;
        $this->userInfoRepository = $userInfoRepository;
    }


    public function remove($user_level_id)
    {
        $row = $this->repository->getOne($user_level_id);
        if ($row['user_level_is_buildin'] == 1) {
            throw new ErrorException('系统内置，不可删除');
        }

        $count = $this->userInfoRepository->getNum([['user_level_id', '=', $user_level_id]]);
        if ($count > 0) {
            throw new ErrorException(sprintf(__("有 %d 条记录使用，不可删除"), $count));
        }

        $flag = $this->repository->remove($user_level_id);

        return $flag;
    }

}
