<?php

namespace Modules\Account\Services;

use App\Exceptions\ErrorException;
use Kuteshop\Core\Service\BaseService;
use Modules\Account\Repositories\Contracts\UserTagBaseRepository;
use Modules\Account\Repositories\Contracts\UserTagGroupRepository;

/**
 * Class UserTagGroupService.
 *
 * @package Modules\Account\Services
 */
class UserTagGroupService extends BaseService
{

    private $userTagBaseRepository;

    public function __construct(UserTagGroupRepository $userTagGroupRepository, UserTagBaseRepository $userTagBaseRepository)
    {
        $this->repository = $userTagGroupRepository;
        $this->userTagBaseRepository = $userTagBaseRepository;
    }


    /**
     * @param $request
     * @return array
     */
    public function tree($request)
    {
        $tag_group_tree = [];
        $user_tag_group = $this->repository->find(['tag_group_enable' => 1]);
        if (!empty($user_tag_group)) {
            foreach ($user_tag_group as $tag_group) {
                $user_tag_rows = $this->userTagBaseRepository->find([
                    'tag_group_id' => $tag_group['tag_group_id'],
                    'tag_enable' => 1
                ]);

                if (!empty($user_tag_rows)) {
                    $tag_group_tree[] = [
                        'tag_title' => $tag_group['tag_group_name'],
                        'children' => array_values($user_tag_rows)
                    ];
                }
            }
        }

        return array_values($tag_group_tree);
    }


    /**
     * 删除
     * @param $tag_group_id
     * @return int|true
     * @throws ErrorException
     */
    public function remove($tag_group_id)
    {
        if ($this->userTagBaseRepository->find(['tag_group_id' => $tag_group_id])) {
            throw new ErrorException(__('分组下有标签不可删除'));
        }
        $flag = $this->repository->remove($tag_group_id);

        return $flag;
    }

}
