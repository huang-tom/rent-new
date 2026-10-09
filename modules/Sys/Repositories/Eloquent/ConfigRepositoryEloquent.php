<?php

namespace Modules\Sys\Repositories\Eloquent;

use App\Support\StateCode;
use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Sys\Repositories\Contracts\ConfigRepository;
use Modules\Sys\Repositories\Models\Config;

/**
 * Class ConfigRepositoryEloquent.
 *
 * @package Modules\Sys\Repositories\Eloquent
 */
class ConfigRepositoryEloquent extends BaseRepository implements ConfigRepository
{

    // 定义全局变量 $state_id_row
    protected $state_id_row = [];

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Config::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }


    public function getConfig($key, $default = null)
    {
        $config_row = $this->find($key);
        if ($config_row) {
            if ('json' == $config_row['config_datatype']) {
                $config_row['config_value'] = decode_json($config_row['config_value']);
            } else if ('dot' == $config_row['config_datatype']) {

                $config_row['config_value'] = explode(',', $config_row['config_value']);
            }

            $val = is_array($config_row['config_value']) ? $config_row['config_value'] : trim($config_row['config_value']);

            //self::$registry[$key] = $config_row['config_value'];
        } else {
            $val = $default;
        }

        return $val;
    }


    /**
     * 获取订单下一个状态
     * @param $order_state_id
     * @return array|int|mixed|string
     */
    public function getNextOrderStateId($order_state_id)
    {
        if (count($this->state_id_row) > 0) {
        } else {
            $this->initOrderProcess();
        }

        $index = array_search($order_state_id, $this->state_id_row);
        if ($index === false) {
            return [0, new Error("订单当前状态配置数据有误！")];
        } else {
            // 最后一个
            if (count($this->state_id_row) === $index + 1) {
                $next_order_state_id = StateCode::ORDER_STATE_FINISH;
            } else {
                $next_order_state_id = $this->state_id_row[$index + 1];
            }
        }

        return $next_order_state_id;
    }


    /**
     * 读取配置，获得初始化订单状态
     * @return string[]
     */
    public function initOrderProcess()
    {

        $this->state_id_row = [];

        $sc_order_process = $this->getConfig('sc_order_process');
        $state_id_list = explode(',', $sc_order_process);

        // 从小到大排序
        sort($state_id_list);

        $this->state_id_row = $state_id_list;

        return $this->state_id_row;
    }


}
