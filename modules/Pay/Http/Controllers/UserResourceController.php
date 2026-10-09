<?php

namespace Modules\Pay\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Support\Respond;
Use Illuminate\Http\Request;
use Modules\Pay\Services\UserResourceService;
use Modules\Pay\Repositories\Validators\UserResourceValidator;

class UserResourceController extends BaseController
{
    private $service;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserResourceService $service)
    {
        $this->service = $service;
    }


    /**
    * 列表
    */
    public function list(Request $request)
    {
        $data = $this->service->list($request);

        return Respond::success($data);
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->validator->with($request->all())->passesOrFail('create');
        $data = $this->service->add($request);

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $primary_id = $request['primary_id'];
        $this->validator->setId($primary_id);
        $this->validator->with($request->all())->passesOrFail('update');
        $data = $this->service->edit($request,$primary_id);

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $data = $this->service->remove($request);

        return Respond::success($data);
    }
}
