<?php

namespace Modules\Sys\Services;

use App\Exceptions\ErrorException;
use Illuminate\Support\Facades\DB;
use Kuteshop\Core\Service\BaseService;
use Modules\Sys\Repositories\Contracts\CurrencyBaseRepository;

/**
 * Class CurrencyBaseService.
 *
 * @package Modules\Sys\Services
 */
class CurrencyBaseService extends BaseService
{

    public function __construct(CurrencyBaseRepository $currencyBaseRepository)
    {
        $this->repository = $currencyBaseRepository;
    }


    /**
     * 添加货币语言
     * @param $request
     * @return true
     * @throws ErrorException
     */
    public function addCurrencyBase($request)
    {
        DB::beginTransaction();

        try {
            if (isset($request['currency_is_default']) && $request['currency_is_default']) {
                $this->repository->editWhere(['currency_is_default' => true], ['currency_is_default' => false]);
            }
            if (isset($request['currency_default_lang']) && $request['currency_default_lang']) {
                $this->repository->editWhere(['currency_default_lang' => true], ['currency_default_lang' => false]);
            }

            $this->repository->add($request);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ErrorException(__('操作失败: ') . $e->getMessage());
        }

        return true;
    }


    /**
     * 修改货币语言
     * @param $currency_id
     * @param $request
     * @return true
     * @throws ErrorException
     */
    public function editCurrencyBase($currency_id, $request)
    {
        DB::beginTransaction();

        try {

            $row = $this->repository->getOne($currency_id);
            if (isset($request['currency_is_default']) && $request['currency_is_default'] && $request['currency_is_default'] != $row['currency_is_default']) {
                $this->repository->editWhere(['currency_is_default' => true], ['currency_is_default' => false]);
            }
            if (isset($request['currency_default_lang']) && $request['currency_default_lang'] && $request['currency_default_lang'] != $row['currency_default_lang']) {
                $this->repository->editWhere(['currency_default_lang' => true], ['currency_default_lang' => false]);
            }

            $this->repository->edit($currency_id, $request);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ErrorException(__('操作失败: ') . $e->getMessage());
        }

        return true;
    }


    /**
     * 修改状态
     * @param $request
     * @param $currency_id
     * @return bool
     * @throws ErrorException
     */
    public function editState($currency_id, $request)
    {
        DB::beginTransaction();

        try {
            $state_fields = [
                'currency_status',
                'currency_is_default',
                'currency_default_lang',
                'currency_is_standard',
                'currency_decimal_place',
            ];

            $state_data = [];
            $currency_row = $this->repository->getOne($currency_id);

            foreach ($state_fields as $field) {
                if ($request->has($field)) {
                    $new_value = $request->boolean($field, false);
                    $state_data[$field] = $new_value;

                    if ($new_value != $currency_row[$field] && in_array($field, ['currency_is_default', 'currency_default_lang'])) {
                        $this->repository->editWhere([$field => true], [$field => false]);
                    }
                }
            }

            if (!empty($state_data)) {
                $this->repository->edit($currency_id, $state_data);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ErrorException(__('操作失败: ') . $e->getMessage());
        }

        return true;
    }

}
