<?php

namespace Modules\Sys\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Sys\Repositories\Contracts\MessageTemplateRepository;
use App\Exceptions\ErrorException;

/**
 * Class MessageTemplateService.
 *
 * @package Modules\Sys\Services
 */
class MessageTemplateService extends BaseService
{

    public function __construct(MessageTemplateRepository $messageTemplateRepository)
    {
        $this->repository = $messageTemplateRepository;
    }

    public function editState($request)
    {
        $message_id = $request->get('message_id');
        $state_data = [];

        if ($request->has('message_enable')) {
            $state_data['message_enable'] = $request->boolean('message_enable');
        }

        if ($request->has('message_sms_enable')) {
            $state_data['message_sms_enable'] = $request->boolean('message_sms_enable');
        }

        if ($request->has('message_email_enable')) {
            $state_data['message_email_enable'] = $request->boolean('message_email_enable');
        }

        if ($request->has('message_wechat_enable')) {
            $state_data['message_wechat_enable'] = $request->boolean('message_wechat_enable');
        }

        if ($request->has('message_xcx_enable')) {
            $state_data['message_xcx_enable'] = $request->boolean('message_xcx_enable');
        }

        if ($request->has('message_app_enable')) {
            $state_data['message_app_enable'] = $request->boolean('message_app_enable');
        }

        if ($request->has('message_sms_force')) {
            $state_data['message_sms_force'] = $request->boolean('message_sms_force');
        }

        if ($request->has('message_email_force')) {
            $state_data['message_email_force'] = $request->boolean('message_email_force');
        }

        if ($request->has('message_app_force')) {
            $state_data['message_app_force'] = $request->boolean('message_app_force');
        }

        if ($request->has('message_force')) {
            $state_data['message_force'] = $request->boolean('message_force');
        }

        // 更新状态
        if ($message_id && !empty($state_data)) {
            $result = $this->repository->edit($message_id, $state_data);
            if ($result) {
                return true;
            } else {
                throw new ErrorException(__('更新失败'));
            }
        }

        return true;
    }

}
