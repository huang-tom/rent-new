<?php

namespace Modules\Account\Http\Controllers\Front;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Criteria\UserDeliveryAddressCriteria;
use Modules\Account\Repositories\Validators\UserDeliveryAddressValidator;
use Modules\Account\Services\UserDeliveryAddressService;

class DeliveryAddressController extends BaseController
{

    private $userId;
    private $userDeliveryAddressService;
    private $userDeliveryAddressValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserDeliveryAddressService $userDeliveryAddressService, UserDeliveryAddressValidator $userDeliveryAddressValidator)
    {
        $this->userId = checkLoginUserId();

        $this->userDeliveryAddressService = $userDeliveryAddressService;
        $this->userDeliveryAddressValidator = $userDeliveryAddressValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $request['user_id'] = $this->userId;
        $data = $this->userDeliveryAddressService->list($request, new UserDeliveryAddressCriteria($request));

        return Respond::success($data);
    }


    /**
     * get
     */
    public function get(Request $request)
    {
        $ud_id = $request->input('ud_id', 0);
        $data = $this->userDeliveryAddressService->get($ud_id);

        return Respond::success($data);
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->userDeliveryAddressValidator->with($request->all())->passesOrFail('create');

        $request['user_id'] = $this->userId;
        $data = $this->userDeliveryAddressService->saveAddress($request);

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function save(Request $request)
    {
        $ud_id = $request['ud_id'];
        $row = $this->userDeliveryAddressService->get($ud_id);
        checkDataRights($this->userId, $row);

        $this->userDeliveryAddressValidator->with($request->all())->passesOrFail('update');

        $request['user_id'] = $this->userId;
        $data = $this->userDeliveryAddressService->saveAddress($request, $ud_id);

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $row = $this->userDeliveryAddressService->get($request['ud_id']);
        checkDataRights($this->userId, $row);

        $data = $this->userDeliveryAddressService->remove($request['ud_id']);

        return Respond::success($data);
    }


}
