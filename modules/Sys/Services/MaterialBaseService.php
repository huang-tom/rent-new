<?php

namespace Modules\Sys\Services;

use App\Exceptions\ErrorException;
use Kuteshop\Core\Service\BaseService;
use Modules\Sys\Repositories\Contracts\MaterialBaseRepository;
use Modules\Sys\Repositories\Models\MaterialBase;
use Modules\Sys\Repositories\Models\MaterialGallery;

/**
 * Class MaterialBaseService.
 *
 * @package Modules\Sys\Services
 */
class MaterialBaseService extends BaseService
{

    public function __construct(MaterialBaseRepository $materialBaseRepository)
    {
        $this->repository = $materialBaseRepository;
    }


    /**
     * @param $request
     * @return true
     * @throws ErrorException
     */
    public function addMaterial($request)
    {
        // +------------------------------------------------------------------
        // | [修正 2026-09-22] 原实现必然插入失败，两个原因（实测确认）：
        // |
        // | 1. sys_material_base.material_number 是 char(32) NOT NULL 且无默认值，
        // |    而这里从不写它。本库 sql_mode 含 STRICT_TRANS_TABLES
        // |    （STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION），
        // |    缺列插入不会静默补空串，而是直接抛 SQL 错误。
        // | 2. 全项目检索 material_number，除表定义外零引用 —— 原厂也没有生成逻辑，
        // |    说明这条「添加素材」链路在厂商原始构建里就是坏的。
        // |
        // | 这里补一个 32 位十六进制流水号（md5(uniqid)），长度正好等于列宽。
        // +------------------------------------------------------------------
        $material_type = (string)$request->input('material_type', 'image');
        if (!in_array($material_type, ['video', 'other', 'image', 'audio', 'document'], true)) {
            $material_type = 'image';
        }

        $gallery_id = (int)$request->input('gallery_id', 0);

        // 素材必须有地址，否则是一条不可用的空记录。
        // 界面上这条链路的入口是「素材管理 → 添加」（先上传再落库），
        // 上传插件 plugins/MsUpload 不会自己调本接口，material_url 由调用方带入。
        $material_url = trim((string)$request->input('material_url', ''));
        if ($material_url === '') {
            throw new ErrorException(__('请先上传素材文件'));
        }

        $material_name = trim((string)$request->input('material_name', ''));
        if ($material_name === '') {
            // 没填标题就用文件名兜底，避免列表里出现一整列空白
            $material_name = basename(parse_url($material_url, PHP_URL_PATH) ?: $material_url);
        }

        $add_row = [
            'material_number' => md5(uniqid((string)mt_rand(), true)),
            'user_id' => (int)$request->input('user_id', 0),
            'store_id' => (int)$request->input('store_id', 0),
            'gallery_id' => $gallery_id,
            'material_type' => $material_type,
            'material_name' => mb_substr($material_name, 0, 255),
            'material_desc' => (string)$request->input('material_desc', ''),
            'material_alt' => (string)$request->input('material_alt', ''),
            'material_url' => $material_url,
            'material_source' => (string)$request->input('material_source', ''),
            'material_path' => (string)$request->input('material_path', ''),
            'material_sort' => (int)$request->input('material_sort', 0),
            'material_size' => (int)$request->input('material_size', 0),
            'material_mime_type' => (string)$request->input('material_mime_type', 'image/png'),
            'material_duration' => (string)$request->input('material_duration', ''),
        ];

        $material = MaterialBase::create($add_row);

        if ($material) {
            // gallery_num 是「该分类下素材数」。全项目检索确认它既没有任何读取方、
            // 也没有任何维护方，长期停在 0。这里在新增时同步 +1，至少不再继续发散。
            if ($gallery_id > 0) {
                MaterialGallery::where('gallery_id', $gallery_id)->increment('gallery_num');
            }

            return $material->toArray();
        } else {
            throw new ErrorException(__('操作失败'));
        }
    }


    /**
     * 批量删除素材
     *
     * [新增 2026-09-22] 补齐 POST /manage/sys/material/removeBatch。
     * 前端「素材管理」的「批量删除」按钮是**活着**的
     * （admin/src/views/sys/gallery/components/MaterialBase.vue:8），
     * 提交 material_id 为逗号拼接串（同文件 271 行 selectRows.map(...).join()），
     * 但后端从未注册该路由 → 点一次 404 一次。
     *
     * 用显式 Model 删除：继承来的 remove() 会把入参交给被混淆的 BaseRepository，
     * 而本项目 4 个 core 基类被混淆、对逗号串的行为不可证。显式 whereIn 行为可控。
     *
     * @param $request
     * @return bool
     * @throws ErrorException
     */
    public function removeBatchMaterial($request)
    {
        $raw = $request->input('material_id');
        $ids = is_array($raw) ? $raw : explode(',', (string)$raw);
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if (empty($ids)) {
            throw new ErrorException(__('请选择要删除的数据'));
        }

        // 先按分类统计，删完再把 gallery_num 减回去，避免出现负数
        $gallery_counts = MaterialBase::whereIn('material_id', $ids)
            ->selectRaw('gallery_id, COUNT(*) AS num')
            ->groupBy('gallery_id')
            ->pluck('num', 'gallery_id')
            ->toArray();

        $deleted = MaterialBase::whereIn('material_id', $ids)->delete();
        if ($deleted <= 0) {
            throw new ErrorException(__('删除失败'));
        }

        foreach ($gallery_counts as $gallery_id => $num) {
            $gallery_id = (int)$gallery_id;
            if ($gallery_id <= 0) {
                continue;
            }
            MaterialGallery::where('gallery_id', $gallery_id)
                ->where('gallery_num', '>=', (int)$num)
                ->decrement('gallery_num', (int)$num);
        }

        return true;
    }


    /**
     * 获取 material_mime_type
     * @param $extension
     * @return string
     */
    public function getContentType($extension)
    {
        switch ($extension) {
            case ".bmp":
                return "image/bmp";
            case ".gif":
                return "image/gif";
            case ".jpeg":
            case ".jpg":
            case ".png":
                return "image/jpeg";
            case ".html":
                return "text/html";
            case ".txt":
                return "text/plain";
            case ".vsd":
                return "application/vnd.visio";
            case ".ppt":
            case ".pptx":
                return "application/vnd.ms-powerpoint";
            case ".doc":
            case ".docx":
                return "application/msword";
            case ".xml":
                return "text/xml";
            case ".mp4":
                return "video/mp4";
            case ".awf":
                return "application/vnd.adobe.workflow";
            case ".wav":
                return "audio/wav";
            case ".zip":
                return "application/zip";
            case ".pdf":
                return "application/pdf";
            case ".ogg":
                return "application/ogg";
            case ".js":
                return "application/javascript";
            default:
                return "multipart/form-data";
        }
    }

}
