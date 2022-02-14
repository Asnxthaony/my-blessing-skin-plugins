<?php

namespace Asnxthaony\PurgeCloudflareCdn;

use App\Models\Player;
use App\Services\PluginManager;
use Composer\CaBundle\CaBundle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PurgeCDN implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $tries = 3;

    /**
     * @var Player
     */
    protected $player;

    public function __construct(Player $player)
    {
        $this->player = $player;
    }

    public function handle(PluginManager $plugins)
    {
        // 检查插件是否启用
        $usm = $plugins->get('usm-api');
        $legacy = $plugins->get('legacy-api');
        $yggdrasil = $plugins->get('yggdrasil-api');

        // 列出需要刷新的 URL
        $name = urlencode($this->player->name);
        $urls = ['/'.$name.'.json', '/csl/'.$name.'.json'];
        if (isset($usm) && $usm->isEnabled()) {
            $urls[] = '/usm/'.$name.'.json';
        }
        if (isset($legacy) && $legacy->isEnabled()) {
            array_push(
                $urls,
                '/skin/'.$name.'.png',
                '/cape/'.$name.'.png'
            );
        }
        if (isset($yggdrasil) && $yggdrasil->isEnabled()) {
            $uuid = DB::table('uuid')->where('name', $name)->value('uuid');
            if ($uuid) {
                array_push(
                    $urls,
                    '/api/yggdrasil/sessionserver/session/minecraft/profile/'.$uuid,
                    '/api/yggdrasil/sessionserver/session/minecraft/profile/'.$uuid.'?unsigned=false',
                    '/api/yggdrasil/sessionserver/session/minecraft/profile/'.$uuid.'?unsigned=true'
                );
            }
        }

        $siteUrl = env('CLOUDFLARE_SITE_URL');
        $urls = preg_filter('/^/', $siteUrl, $urls);

        // 请求清除缓存
        Http::withHeaders([
            'Authorization' => 'Bearer '.env('CLOUDFLARE_API_TOKEN'),
            'Content-Type' => 'application/json',
        ])->withOptions([
            'verify' => CaBundle::getSystemCaRootBundlePath(),
        ])->post('https://api.cloudflare.com/client/v4/zones/'.env('CLOUDFLARE_ZONE_IDENTIFIER').'/purge_cache', ['files' => $urls]);
    }
}
