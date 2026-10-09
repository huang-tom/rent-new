<?php

namespace Modules\Pay\Http\Controllers\Front;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Account\Repositories\Models\User;
use Modules\Pay\Repositories\Criteria\ConsumeRecordCriteria;
use Modules\Pay\Services\ConsumeRecordService;

class ConsumeController extends BaseController
{
    private $consumeRecordService;
    private $userId;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ConsumeRecordService $consumeRecordService)
    {
        $this->consumeRecordService = $consumeRecordService;
        $this->userId = User::getUserId();
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $request['user_id'] = $this->userId;
        $data = $this->consumeRecordService->list($request, new ConsumeRecordCriteria($request));

        return Respond::success($data);
    }

}
