<?php

namespace Asnxthaony\PurgeBdyCdn;

use App\Models\Player;
use App\Services\PluginManager;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        $legacy = $plugins->get('legacy-api');
        $yggdrasil = $plugins->get('yggdrasil-api');

        // 列出需要刷新的 URL
        $name = urlencode($this->player->name);
        $urls = ['/'.$name.'.json', '/csl/'.$name.'.json'];

        // [legacy-api]
        if (isset($legacy) && $legacy->isEnabled()) {
            array_push(
                $urls,
                '/skin/'.$name.'.png',
                '/cape/'.$name.'.png'
            );
        }

        // [yggdrasil-api]
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

        $siteUrl = env('BDY_SITE_URL');
        $urls = preg_filter('/^/', $siteUrl, $urls);
        $urls2 = [];
        foreach ($urls as $url) {
            $urls2[] = ['url' => $url];
        }

        // 生成认证字符串 @See https://cloud.baidu.com/doc/Reference/s/njwvz1yfu
        $authStringPrefix = 'bce-auth-v1/'.env('BDY_ACCESS_KEY').'/'.Carbon::now()->toIso8601ZuluString().'/1800';
        $canonicalRequest = "POST\n/v2/cache/purge\n\nhost:cdn.baidubce.com";
        $signingKey = hash_hmac('sha256', $authStringPrefix, env('BDY_SECRET_KEY'));
        $signature = hash_hmac('sha256', $canonicalRequest, $signingKey);
        $authorization = $authStringPrefix.'/host/'.$signature;

        try {
            $response = Http::withToken($authorization, '')
                ->acceptJson()
                ->post('https://cdn.baidubce.com/v2/cache/purge', ['tasks' => $urls2])
                ->json();

            if (Arr::exists($response, 'code')) {
                Log::channel('purge-bdy-cdn')->warning("name=$name, requestId={$response['requestId']}, code={$response['code']}, message={$response['message']}");
            } else {
                Log::channel('purge-bdy-cdn')->info("name=$name, purgeId={$response['id']}");
            }
        } catch (\Exception $e) {
            Log::channel('purge-bdy-cdn')->warning("name=$name, error={$e->getMessage()}");
        }
    }
}
