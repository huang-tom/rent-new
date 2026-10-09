<?php

namespace Modules\Sys\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Routing\Controller as BaseController;
use function view;

class CreateController extends BaseController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }


    public function tables()
    {
        $tables = DB::select('show tables');
        $tables = array_column($tables, 'Tables_in_homestead');

        /*foreach ($tables as $name){
            echo '<a href="/manage/sys/create/info?name='.$name.'">'.$name.'</a><br>';
        }*/

        $www = view('dataDictionary', ['tables' => $tables]);
        echo $www;
    }

    public function info(Request $request)
    {
        $table_name = $request->get('name');

        //获取数据库所有的表字段和注释
        # 查看 db-name 库的 table-name 表中 column-name 字段的字段类型
        $sql = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = 'homestead'
        AND TABLE_NAME = '".$table_name."'";
        $table = DB::select($sql);


        $arr = explode('_',$table_name);
        $module_name = $arr[0];
        unset($arr[0]);

        $file_name = '';
        foreach ($arr as $v)
        {
            $file_name .= ucfirst($v);
        }

        echo view('info', ['table' => $table,'name'=>$table_name,'module_name'=>$module_name,'file_name'=>$file_name]);
    }

    public function code(Request $request)
    {
        Artisan::call('module:make-repositories UserLevel account');
        Artisan::call('module:make-controller UserLevel account');
        Artisan::call('module:make-service UserLevel account');
        die(1);
    }

}
