<?php

namespace Modules\Pt\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Pt\Repositories\Criteria\NewProductPromoCateCriteria;
use Modules\Pt\Repositories\Validators\NewProductPromoCateValidator;
use Modules\Pt\Services\NewProductPromoCateService;

class NewProductPromoCateController extends BaseController
{
    private $newProductPromoCateService;
    private $newProductPromoCateValidator;

    public function __construct(
        NewProductPromoCateService   $newProductPromoCateService,
        NewProductPromoCateValidator $newProductPromoCateValidator
    )
    {
        $this->newProductPromoCateService = $newProductPromoCateService;
        $this->newProductPromoCateValidator = $newProductPromoCateValidator;
    }

    /**
     * 推广分类列表
     */
    public function list(Request $request)
    {
        $data = $this->newProductPromoCateService->list($request, new NewProductPromoCateCriteria($request));

        return Respond::success($data);
    }

    public function formatRequest(Request $request)
    {
        return [
            'cate_name' => $request->input('cate_name', ''),
            'cate_desc' => $request->input('cate_desc', ''),
            'cate_type' => (int)$request->input('cate_type', NewProductPromoCateService::CATE_TYPE_CATEGORY),
            'category_id' => (int)$request->input('category_id', 0),
            'cate_enable' => (int)$request->input('cate_enable', 1),
            'cate_sort' => (int)$request->input('cate_sort', 50),
        ];
    }

    public function add(Request $request)
    {
        $this->newProductPromoCateValidator->with($request->all())->passesOrFail('create');
        $data = $this->newProductPromoCateService->addCate($this->formatRequest($request));

        return Respond::success($data);
    }

    public function edit(Request $request)
    {
        $cate_id = $request->get('cate_id', -1);
        $this->newProductPromoCateValidator->setId($cate_id);
        $this->newProductPromoCateValidator->with($request->all())->passesOrFail('update');
        $data = $this->newProductPromoCateService->editCate($cate_id, $this->formatRequest($request));

        return Respond::success($data);
    }

    public function remove(Request $request)
    {
        $cate_id = $request->get('cate_id', -1);
        $data = $this->newProductPromoCateService->removeCate($cate_id);

        return Respond::success($data);
    }

    public function editState(Request $request)
    {
        $cate_id = $request->get('cate_id', -1);
        $cate_enable = (int)$request->input('cate_enable', 1);
        $data = $this->newProductPromoCateService->editState($cate_id, $cate_enable);

        return Respond::success($data);
    }
}
