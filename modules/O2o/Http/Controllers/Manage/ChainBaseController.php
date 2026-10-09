<?php

namespace Modules\O2o\Http\Controllers\Manage;

use App\Exceptions\ErrorException;
use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\O2o\Repositories\Criteria\ChainBaseCriteria;
use Modules\O2o\Services\ChainBaseService;
use Modules\O2o\Repositories\Validators\ChainBaseValidator;

class ChainBaseController extends BaseController
{
    private $chainBaseService;
    private $chainBaseValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ChainBaseService $chainBaseService, ChainBaseValidator $chainBaseValidator)
    {
        $this->chainBaseService = $chainBaseService;
        $this->chainBaseValidator = $chainBaseValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->chainBaseService->list($request, new ChainBaseCriteria($request));

        return Respond::success($data);
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->chainBaseValidator->with($request->all())->passesOrFail('create');
        $data = $this->chainBaseService->add($request);

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $chain_id = $request['chain_id'];

        $this->chainBaseValidator->setId($chain_id);
        $this->chainBaseValidator->with($request->all())->passesOrFail('update');
        $data = $this->chainBaseService->edit($chain_id, $request);

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $chain_id = $request->input('chain_id', -1);
        $data = $this->chainBaseService->remove($chain_id);

        return Respond::success($data);
    }


    /**
     * 修改状态
     */
    public function editState(Request $request)
    {
        $chain_id = $request['chain_id'];
        $data = $this->chainBaseService->edit($chain_id, ['chain_status' => $request->boolean('chain_status')]);

        return Respond::success($data);
    }


}
