# purge-qcloud-cdn

这个插件可以在用户更新其角色信息时，自动通过腾讯云 API 发出缓存刷新请求。

## 配置

本插件无配置页面。所有的配置在 `.env` 文件中完成。有以下 3 个配置项：

- `QCLOUD_CDN_BASE_URL` - 您的腾讯云 CDN 的基础 URL，**不能**以斜杠结尾。
- `QCLOUD_CDN_SECRET_ID` - 您的腾讯云账户的 Secret ID。
- `QCLOUD_CDN_SECRET_KEY` - 您的腾讯云账户的 Secret Key。

## 注意

在使用本插件前，请确保配置好队列驱动。
