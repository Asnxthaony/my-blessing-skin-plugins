# 玩家列表

当您想要在用户中心的仪表盘处显示玩家列表时，可以使用本插件。

## 使用方法

本插件没有配置页面，所有配置需要通过修改源代码来进行。

1. 修改 `assets/js/player-list.js` 中的接口地址

2. 修改 `app/Http/Controllers/TextureController.php` 中的 `avatarByPlayer` 方法

```php
    public function avatarByPlayer($size, $name)
    {
        if ($player = Player::where('name', $name)->first()) {
            $hash = $player->getTexture('skin');
            if (Storage::disk('textures')->has($hash)) {
                $png = Minecraft::generateAvatarFromSkin(
                    Storage::disk('textures')->read($hash),
                    $size
                );

                return $this->outputImage(png($png));
            }
        }
   
        return response()->file(storage_path('static_textures/avatar.png'));
    }
```
