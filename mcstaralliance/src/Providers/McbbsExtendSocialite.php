<?php

namespace mcstaralliance\Providers;

use SocialiteProviders\Manager\SocialiteWasCalled;

class McbbsExtendSocialite
{
    /**
     * Execute the provider.
     */
    public function handle(SocialiteWasCalled $socialiteWasCalled)
    {
        $socialiteWasCalled->extendSocialite('mcbbs', __NAMESPACE__.'\McbbsProvider');
    }
}
