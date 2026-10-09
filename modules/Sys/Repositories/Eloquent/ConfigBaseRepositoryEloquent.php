<?php

namespace Modules\Sys\Repositories\Eloquent;

use App\Support\StateCode;
use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Sys\Repositories\Contracts\ConfigBaseRepository;
use Modules\Sys\Repositories\Models\ConfigBase;

/**
 * Class ConfigBaseRepositoryEloquent.
 *
 * @package Modules\Sys\Repositories\Eloquent
 */
class ConfigBaseRepositoryEloquent extends BaseRepository implements ConfigBaseRepository
{

    protected $stateIdRow = [];
    protected $returnStateIdRow = [];

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ConfigBase::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }


    /**
     * @param $config_type_ids
     * @return mixed
     */
    public function findByTypeIdAndSort($config_type_ids)
    {
        return $this->model->whereIn('config_type_id', $config_type_ids)->where('config_enable', '=', 1)
            ->orderBy('config_sort')
            ->get()
            ->toArray();
    }


    /**
     * @param $key
     * @param $default
     * @return array|mixed|string|string[]|null
     */
    public function getConfig($key, $default = null)
    {
        try {
            $config_row = $this->getOne($key);
            if ($config_row) {
                if ('json' == $config_row['config_datatype']) {
                    $config_row['config_value'] = decode_json($config_row['config_value']);
                } else if ('dot' == $config_row['config_datatype']) {
                    $config_row['config_value'] = explode(',', $config_row['config_value']);
                }

                $val = is_array($config_row['config_value']) ? $config_row['config_value'] : trim($config_row['config_value']);
            } else {
                $val = $default;
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
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
        $this->initOrderProcess();

        $index = array_search($order_state_id, $this->stateIdRow);
        if ($index === false) {
            return [0, new Error("订单当前状态配置数据有误！")];
        } else {
            // 最后一个
            if (count($this->stateIdRow) === $index + 1) {
                $next_order_state_id = StateCode::ORDER_STATE_FINISH;
            } else {
                $next_order_state_id = $this->stateIdRow[$index + 1];
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

        $this->stateIdRow = [];

        $sc_order_process = $this->getConfig('sc_order_process');
        $state_id_list = explode(',', $sc_order_process);

        // 从小到大排序
        sort($state_id_list);

        $this->stateIdRow = $state_id_list;

        return $this->stateIdRow;
    }


    /**
     * 获取退单下一个状态
     * @param $return_state_id
     * @return array|int|mixed|string
     */
    public function getNextReturnStateId($return_state_id)
    {
        $this->initOReturnProcess();

        $index = array_search($return_state_id, $this->returnStateIdRow);
        if ($index === false) {
            return [0, new Error("订单当前状态配置数据有误！")];
        } else {
            // 最后一个
            if (count($this->returnStateIdRow) === $index + 1) {
                $next_return_state_id = StateCode::ORDER_STATE_FINISH;
            } else {
                $next_return_state_id = $this->returnStateIdRow[$index + 1];
            }
        }

        return $next_return_state_id;
    }


    /**
     * 读取配置，获得初始化退单状态
     * @return string[]
     */
    public function initOReturnProcess()
    {
        $this->returnStateIdRow = [];

        $sc_return_process = $this->getConfig('sc_return_process');
        $state_id_list = explode(',', $sc_return_process);

        // 从小到大排序
        sort($state_id_list);

        $this->returnStateIdRow = $state_id_list;

        return $this->returnStateIdRow;
    }


    /**
     * 参照老版本
     * @param $str
     * @return int
     */
    public function bkdrHash($str)
    {
        $seed = 131;
        $hash = 0;
        for ($i = 0; $i != strlen($str); ++$i) {
            $hash = $seed * $hash + ord($str[$i]);
        }

        return ($hash & 0x7FFFFFFF);
    }


    /**
     * 参照老版本
     *
     * [本地化] 原默认值为厂商 IM 服务器 wss://im.suteshop.com:9501。
     * 改为从 sys_config_base.im_node_site_url 读取；未配置时为空串，
     * 由 UserMessageService::getImConfig 在 IM 关闭时直接不下发。
     * 即：需要自建 IM 时把地址配到 im_node_site_url，代码里不再硬编码厂商域名。
     *
     * @param $user_id
     * @param $service_user_id
     * @param $node_site_url
     * @return string
     */
    public function getPlantformChatUrl($user_id, $service_user_id, $node_site_url = '')
    {
        if ($node_site_url === '') {
            $node_site_url = $this->getConfig('im_node_site_url', '');
        }

        $service_app_key = $this->getConfig('service_app_key');
        $token = md5($service_user_id . '-' . $service_app_key . time());
        $puid = sprintf('%d-%d', $service_user_id, $user_id);

        return sprintf("%s?uid=%s&puid=%s&suid=%s&t=%d&token=%s", $node_site_url, $this->bkdrHash($puid), $puid, $service_user_id, time(), $token);
    }

    /**
     * 参照老版本
     * @param $user_id
     * @return mixed
     */
    public function getPlantformUid($user_id)
    {
        $service_user_id = $this->getConfig('service_user_id');
        $puid = sprintf('%d-%d', $service_user_id, $user_id);

        return $this->bkdrHash($puid);
    }

}
