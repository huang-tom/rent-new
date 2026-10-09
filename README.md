# zxbshop 商城后台 — PHP 后端

商城系统的后端 API 服务，配套管理后台前端（Vue3 + Element Plus）使用。

## 技术栈

| 层 | 技术 |
|---|---|
| 框架 | Lumen 9（Laravel 组件） |
| 语言 | PHP 8.0+ |
| 数据库 | MySQL 8.0 |
| 缓存 / 队列 | Redis |
| 鉴权 | JWT（Bearer Token） |
| 架构 | 模块化（nwidart/laravel-modules），按业务域拆分为 `modules/*` |

## 目录结构

```
./
├── api/                  # 后端主体（本文档所在目录）
│   ├── app/              # 全局：中间件、异常、Console 命令、Support 工具类
│   ├── bootstrap/app.php # 应用引导与容器注册
│   ├── config/           # 框架与业务配置
│   ├── modules/          # 业务模块（每个模块自带 Routes / Http / Services / Repositories）
│   │   ├── Account/      # 账号、用户、消息
│   │   ├── Invoicing/    # 进销存（库存、单据）
│   │   ├── Pt/           # 商品
│   │   ├── Shop/         # 店铺
│   │   ├── Sns/          # 社交圈子（动态 / 分类 / 评论）
│   │   ├── Sys/          # 系统：菜单、权限、配置、页面装修
│   │   └── Trade/        # 交易：订单、退款、物流
│   ├── public/           # Web 入口（index.php）
│   ├── routes/           # 全局路由
│   ├── storage/          # 日志、缓存、框架运行时
│   └── docker/           # php.ini / nginx.conf / composer 安装脚本
├── packages/             # 私有 composer 包（与 api/ 平级，相对路径引入）
└── install/              # 数据库初始化脚本
```

## 运行环境

- PHP 8.0（需 `pdo_mysql`、`redis`、`gd`、`bcmath`、`zip`、`opcache` 扩展）
- MySQL 8.0（建议 `utf8mb4` / `utf8mb4_general_ci`）
- Redis 6+
- Composer 2.x

## 本地起服务

```bash
composer install                       # 安装依赖（私有包由相对路径引入）
cp .env.example .env                   # 按需修改数据库 / Redis 连接
php -S 0.0.0.0:9000 -t public          # 开发模式起服务
```

生产环境以 PHP-FPM + Nginx 方式运行，入口为 `public/index.php`。

## 数据库初始化

脚本位于同级 `install/` 目录：

```bash
mysql -uroot -p <DB_NAME> < install/01_zxbshop-001.sql    # 建表 + 基础数据
mysql -uroot -p <DB_NAME> < install/02_patch_missing.sql  # 补齐补丁
```

## 接口约定

- API 前缀：`/front/*`（用户端）、`/manage/*`（后台管理端）
- 鉴权：请求头 `Authorization: Bearer <token>`
- 分页返回：`{ records, items, page, size, total }`

## 注意事项

- `storage/` 与 `bootstrap/cache/` 需对 PHP-FPM 运行用户可写。
- 修改 `.env` 后需重启 PHP-FPM 进程（配置不热加载）。
- `Packages/` 与 `api/` 必须保持平级，`api/composer.json` 以相对路径 `../packages/...` 引用私有包，拆开会导致依赖安装失败。
- 新增后台页面时，除前端路由外还需在 `admin_menu_base` 补 `menu_type=0` 的权限点子行，否则权限列表不会包含该页面。
