# purge-bdy-cdn

这个插件可以在用户更新其角色信息时，自动通过 BCE CDN API 发出缓存刷新请求。

## 配置

本插件无配置页面。所有的配置在 `.env` 文件中完成。有以下 3 个配置项：

- `BDY_SITE_URL` - 站点地址，**不能**以斜杠结尾；
- `BDY_ACCESS_KEY` - 百度智能云的 Access Key
- `BDY_SECRET_KEY` - 百度智能云的 Secret Key

## 注意

如需使用 `srorage/logs/purge-bdy-cdn.log` 中的日志，可自行使用 `logrotate` 等工具切割。
