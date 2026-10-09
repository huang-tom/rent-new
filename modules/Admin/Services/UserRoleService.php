<?php

namespace Modules\Admin\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Admin\Repositories\Contracts\UserAdminRepository;
use Modules\Admin\Repositories\Contracts\UserRoleRepository;
use App\Exceptions\ErrorException;

/**
 * Class UserRoleService.
 *
 * @package Modules\Admin\Services
 */
class UserRoleService extends BaseService
{

    private $userAdminRepository;

    public function __construct(UserRoleRepository $userRoleRepository, UserAdminRepository $userAdminRepository)
    {
        $this->repository = $userRoleRepository;
        $this->userAdminRepository = $userAdminRepository;
    }


    /**
     * 删除
     * @param $user_role_id
     * @return bool
     * @throws ErrorException
     */
    public function removeRole($user_role_id)
    {
        $user_role_row = $this->repository->getOne($user_role_id);
        if ($user_role_row['user_role_buildin']) {
            throw new ErrorException(__('系统内置，不可删除'));
        }

        $used_rows = $this->userAdminRepository->find(['user_role_id' => $user_role_id]);
        if (!empty($used_rows)) {
            throw new ErrorException(sprintf(__("有 %d 条管理员使用，不可删除"), count($used_rows)));
        }

        $result = $this->repository->remove($user_role_id);

        if ($result) {
            return true;
        } else {
            throw new ErrorException(__('删除失败'));
        }
    }

}
