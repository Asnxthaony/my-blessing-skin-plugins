<?php

namespace mcstaralliance\Providers;

use SocialiteProviders\Manager\SocialiteWasCalled;

class DingtalkExtendSocialite
{
    /**
     * Execute the provider.
     */
    public function handle(SocialiteWasCalled $socialiteWasCalled)
    {
        $socialiteWasCalled->extendSocialite('dingtalk', __NAMESPACE__.'\DingtalkProvider');
    }
}
