# ClassManager

ClassManager 现在是一个 monorepo：Laravel 后端位于仓库根目录，React + Vite 前端位于 `frontend/`。

## 本地开发

需要 PHP 8.2+、Composer、Node.js、MySQL 和 Redis。

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run dev
```

`npm run dev` 会同时启动：

- 前端：http://127.0.0.1:5173
- 后端：http://127.0.0.1:8001

Vite 会把 `/api`、`/storage` 和 `/Docs` 转发到 Laravel。按一次 `Ctrl+C` 会同时关闭前后端；任意一方退出时，另一方也会自动停止。`composer dev` 等价于 `npm run dev`。

常用命令：

```bash
npm run build       # 构建前端
npm run lint        # 检查前端
composer test       # 运行后端测试
```

## Docker 部署

容器内由 Nginx 提供前端静态文件，并把 `/api` 转发给 PHP-FPM。只有一个应用容器、一个对外端口；MySQL 和 Redis 继续使用 `.env` 中配置的外部服务。

先创建应用配置：

```bash
cp .env.example .env
php artisan key:generate
```

按需修改 `.env`：

- `APP_PORT`：宿主机端口，默认 `8080`
- `DOCKER_DB_HOST`：容器访问的 MySQL 主机，默认 `host.docker.internal`
- `DOCKER_REDIS_HOST`：容器访问的 Redis 主机，默认 `host.docker.internal`
- `ACTIVITY_FILES_PATH`：活动文件的宿主机目录，默认 `./storage/files/activity`
- `DOCKER_APP_URL`：外部访问地址，默认 `http://localhost:8080`

启动：

```bash
docker compose up -d --build
```

应用默认访问地址是 http://localhost:8080，健康检查地址是 http://localhost:8080/api/ping。

活动发布文件、学生提交、临时文件和导出文件统一存储在容器的 `/var/www/html/storage/files/activity`，并映射到 `ACTIVITY_FILES_PATH`。重新构建或替换容器不会删除宿主机中的这些文件。数据库和 Redis 数据的持久化由对应的外部服务负责。

如需执行迁移：

```bash
docker compose exec app php artisan migrate --force
```
