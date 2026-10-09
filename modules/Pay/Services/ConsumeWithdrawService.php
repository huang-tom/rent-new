<?php

namespace Modules\Pay\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Pay\Repositories\Contracts\ConsumeWithdrawRepository;
use Modules\Account\Repositories\Contracts\UserInfoRepository;

/**
 * Class ConsumeWithdrawService.
 *
 * @package Modules\Pay\Services
 */
class ConsumeWithdrawService extends BaseService
{
    private $userInfoRepository;


    public function __construct(ConsumeWithdrawRepository $consumeWithdrawRepository, UserInfoRepository $userInfoRepository)
    {
        $this->repository = $consumeWithdrawRepository;
        $this->userInfoRepository = $userInfoRepository;
    }


    /**
     * 获取列表
     * @return array
     */
    public function list($request, $criteria)
    {
        $limit = $request->get('size') ?? 10;
        $data = $this->repository->list($criteria, $limit);
        $data['data'] = $this->userInfoRepository->fixUserInfo($data['data']);

        return $data;
    }

}
