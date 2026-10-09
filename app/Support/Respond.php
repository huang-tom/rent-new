<?php

/*
 * (c) suishang
 * Uniformly return data
 */

declare(strict_types=1);

namespace App\Support;

use Symfony\Component\HttpFoundation\Response;

/**
 * 设置控制器返回数据格式
 */
class Respond
{
    // 默认200
    private static $httpCode = Response::HTTP_OK;  // http状态码


    /**
     * 设置返回码,连贯操作
     *
     * @param int $httpCode
     *
     * @return Respond
     */
    public static function setHttpCode(int $httpCode)
    {
        static::$httpCode = $httpCode;
        return static::class;
    }


    /**
     * 正确响应格式
     * @param $data
     * @param string $msg
     * @param int $code
     * @param $status
     * @return \Illuminate\Http\JsonResponse
     */
    public static function success($data, string $msg = '操作成功', int $code = Response::HTTP_OK, $status = 200)
    {
        $data = (is_object($data) ? $data->toArray() : $data);

        //转换分页数据
        if (isset($data['data'])) {
            $data = Respond::transformPageData($data);
        }

        return response()->json([
            'status' => $status,
            'code' => $code,
            'msg' => $msg,
            'data' => $data
        ]);
    }


    /**
     * 错误响应格式
     * @param $msg
     * @param int $code
     * @param $status
     * @return \Illuminate\Http\JsonResponse
     */
    public static function error($msg = 'ERROR', int $code = Response::HTTP_BAD_REQUEST, $status = 250)
    {
        return response()->json([
            'status' => $status,
            'code' => $code,
            'msg' => $msg
        ]);
    }


    /**
     * 不带数据，只返回成功状态码
     * @param string $msg
     * @param int $code
     * @param $status
     * @return \Illuminate\Http\JsonResponse
     */
    public static function ok(string $msg = 'OK', int $code = Response::HTTP_OK, $status = 200)
    {
        return response()->json([
            'status' => $status,
            'code' => $code,
            'msg' => $msg,
        ]);
    }


    /**
     * @param $data
     * @return array
     */
    public static function transformPageData($data)
    {
        $return_data = $data;
        $return_data['items'] = $data['data']; //返回数据
        $return_data['page'] = $data['current_page']; //当前页数
        $return_data['records'] = $data['total']; //总条数
        $return_data['size'] = $data['limit']; //显示行数
        $return_data['total'] = $data['last_page']; //总页数

        unset($return_data['data']);
        unset($return_data['current_page']);
        unset($return_data['limit']);
        unset($return_data['last_page']);
        if (isset($return_data['first_page_url'])) {
            unset($return_data['first_page_url']);
            unset($return_data['from']);
            unset($return_data['last_page_url']);
            unset($return_data['links']);
            unset($return_data['next_page_url']);
            unset($return_data['path']);
            unset($return_data['per_page']);
            unset($return_data['prev_page_url']);
            unset($return_data['to']);
        }

        return $return_data;
    }
}
