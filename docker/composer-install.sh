#!/bin/sh
# =====================================================================
# 在 api 镜像里执行 composer 安装（由 docker compose 的 composer 服务调用）
#
# 为什么单独写成脚本而不是塞在 compose 的 command 里：
#   compose 的 command 是 YAML 折行标量，多行 + 内嵌引号会被解析器改得面目全非
#   （表现为 `sh: 4: --no-scripts: not found`，命令被当多行脚本逐行跑）。
#   写成文件挂进容器最稳。
# =====================================================================
set -e
cd /app

echo "=== 1. 关闭 Composer 2.10 的安全公告阻塞 ==="
# 背景：Composer 2.10 起，「依赖解析」阶段会因安全公告直接拒绝安装。
# 本项目是既有系统，yansongda/pay ^3.7 等被标记为受影响包 —— 这是上游依赖
# 现状，不是我们引入的。这里关掉阻塞（仍可用 composer audit 主动检查）。
# 优先用 composer config；若该键不被接受，则直接改写 composer.json。
if composer config policy.advisories.block false 2>/dev/null; then
    echo "  ✓ 已通过 composer config 关闭"
else
    echo "  composer config 不接受该键，改用直接改写 composer.json"
    php -r '
        $f = "/app/composer.json";
        $j = json_decode(file_get_contents($f), true);
        if (!is_array($j)) { fwrite(STDERR, "composer.json 解析失败\n"); exit(1); }
        $j["config"]["policy"]["advisories"]["block"] = false;
        file_put_contents($f, json_encode($j, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        echo "  ✓ 已写入 config.policy.advisories.block=false\n";
    '
fi

echo
echo "=== 2. 设置 Packagist 国内镜像 ==="
composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/ 2>/dev/null || true
echo "  ✓ 使用 mirrors.aliyun.com/composer"

echo
echo "=== 3. 确认本地 core 包已被识别 ==="
grep -A6 '"repositories"' composer.json | head -10

echo
echo "=== 4. 安装依赖（这一步最慢，3-8 分钟）==="
composer update \
    --no-interaction \
    --prefer-dist \
    --no-progress \
    --no-scripts \
    --no-audit \
    --ignore-platform-reqs \
    --optimize-autoloader

echo
echo "=== 5. 目录准备与权限 ==="
mkdir -p storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/views \
         storage/logs \
         storage/app/public \
         bootstrap/cache
chmod -R 777 storage bootstrap/cache 2>/dev/null || true

echo
echo "=== 6. 结果确认 ==="
echo "  vendor 包数: $(ls vendor | wc -l)"
if [ -d vendor/kuteshop/core ]; then
    echo "  ✓ kuteshop/core 已就位："
    ls vendor/kuteshop/core/src | sed 's/^/      /'
else
    echo "  ✗ kuteshop/core 缺失！"
    exit 1
fi
echo
echo "=== vendor 就绪 ==="
