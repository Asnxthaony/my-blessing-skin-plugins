<?php

namespace Asnxthaony\PurgeQcloudCdn;

use App\Models\Player;
use App\Services\PluginManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use TencentCloud\Cdn\V20180606\CdnClient;
use TencentCloud\Cdn\V20180606\Models\PurgeUrlsCacheRequest;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;

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

        $siteUrl = env('QCLOUD_CDN_BASE_URL');
        $urls = preg_filter('/^/', $siteUrl, $urls);

        try {
            $credential = new Credential(env('QCLOUD_CDN_SECRET_ID'), env('QCLOUD_CDN_SECRET_KEY'));

            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint('cdn.tencentcloudapi.com');

            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);

            $cdnClient = new CdnClient($credential, 'ap-nanjing', $clientProfile);

            $request = new PurgeUrlsCacheRequest();
            $request->fromJsonString(json_encode([
                'Urls' => $urls,
            ]));

            $response = $cdnClient->PurgeUrlsCache($request);

            $taskId = $response->TaskId;
            $requestId = $response->RequestId;

            Log::channel('purge-qcloud-cdn')->info("name=$name, taskId=$taskId, requestId=$requestId");
        } catch (TencentCloudSDKException $e) {
            $requestId = $e->getRequestId();
            $errorCode = $e->getErrorCode();
            $message = $e->getMessage();

            Log::channel('purge-qcloud-cdn')->warning("requestId=$requestId, errorCode=$errorCode, message=$message");
        }
    }
}
