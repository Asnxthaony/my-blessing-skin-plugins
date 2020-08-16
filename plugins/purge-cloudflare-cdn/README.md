# purge-cloudflare-cdn

这个插件可以在用户更新其角色信息时，自动通过 Cloudflare API 发出缓存刷新请求。

## 配置

本插件无配置页面。所有的配置在 `.env` 文件中完成。有以下 3 个配置项：

- `CLOUDFLARE_SITE_URL` - 您的站点地址，**不能**以斜杠结尾；

- `CLOUDFLARE_ZONE_IDENTIFIER` - 站点的 Zone ID (在 `Overview` 中 `API` 区域查看)；
- `CLOUDFLARE_API_TOKEN` - 您的 Cloudflare 账户的 API Token (需拥有 `common.purge` 权限)；

## 注意

在使用本插件前，请确保配置好队列驱动。

## License

MIT License (c) 2020-present Asnxthaony
