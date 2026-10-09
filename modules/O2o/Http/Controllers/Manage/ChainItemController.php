<?php

namespace Modules\O2o\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\O2o\Repositories\Criteria\ChainItemCriteria;
use Modules\O2o\Repositories\Validators\ChainItemValidator;
use Modules\O2o\Services\ChainItemService;

class ChainItemController extends BaseController
{
    private $chainItemService;
    private $chainItemValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ChainItemService $chainItemService, ChainItemValidator $chainItemValidator)
    {
        $this->chainItemService = $chainItemService;
        $this->chainItemValidator = $chainItemValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->chainItemService->list($request, new ChainItemCriteria($request));

        return Respond::success($data);
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->chainItemValidator->with($request->all())->passesOrFail('create');
        $data = $this->chainItemService->add($request);

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $chain_item_id = $request['chain_item_id'];

        $this->chainItemValidator->setId($chain_item_id);
        $this->chainItemValidator->with($request->all())->passesOrFail('update');
        $data = $this->chainItemService->edit($chain_item_id, $request);

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $chain_item_id = $request->input('chain_item_id', -1);
        $data = $this->chainItemService->remove($chain_item_id);

        return Respond::success($data);
    }


    /**
     * 修改状态
     */
    public function editState(Request $request)
    {
        $chain_item_id = $request['chain_item_id'];
        $data = $this->chainItemService->edit($chain_item_id, ['chain_item_enable' => $request->boolean('chain_item_enable')]);

        return Respond::success($data);
    }

}
