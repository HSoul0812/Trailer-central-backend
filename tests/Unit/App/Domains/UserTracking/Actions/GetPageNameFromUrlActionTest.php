<?php

namespace Tests\Unit\App\Domains\UserTracking\Actions;

use App\Domains\UserTracking\Actions\GetPageNameFromUrlAction;
use Tests\Common\TestCase;

class GetPageNameFromUrlActionTest extends TestCase
{
    public const PLP_URL = 'https://trailertrader.com/trailers-for-sale/watercraft-trailers-for-sale?sort=-createdAt';

    public const PLP_URL_OTHER_DOMAIN = 'https://otherdomain.com/trailers-for-sale/watercraft-trailers-for-sale?sort=-createdAt';

    public const PDP_URL = 'https://trailertrader.com/new-2023-load-rite-146-v-bunk-boat-trailer--QS9o.html';

    public const PDP_URL_OTHER_DOMAIN = 'https://otherdomain.com/new-2023-load-rite-146-v-bunk-boat-trailer--QS9o.html';

    public const DEALER_URL = 'https://trailertrader.com/trailer-dealer-in-West-Berlin-NJ/Franklin-Trailers,-Inc.-trailer-sales';

    public const DEALER_URL_OTHER_DOMAIN = 'https://otherdomain.com/trailer-dealer-in-West-Berlin-NJ/Franklin-Trailers,-Inc.-trailer-sales';

    public const OTHER_URL = 'https://google.com';

    public function testItCanGetPageNameFromUrl()
    {
        $action = resolve(GetPageNameFromUrlAction::class);

        config([
            'trailertrader.domains.frontend' => [
                'trailertrader.com',
            ],
        ]);

        $this->assertEquals(GetPageNameFromUrlAction::PAGE_NAMES['TT_PLP'], $action->execute(self::PLP_URL));
        $this->assertNull($action->execute(self::PLP_URL_OTHER_DOMAIN));

        $this->assertEquals(GetPageNameFromUrlAction::PAGE_NAMES['TT_PDP'], $action->execute(self::PDP_URL));
        $this->assertNull($action->execute(self::PDP_URL_OTHER_DOMAIN));

        $this->assertEquals(GetPageNameFromUrlAction::PAGE_NAMES['TT_DEALER'], $action->execute(self::DEALER_URL));
        $this->assertNull($action->execute(self::DEALER_URL_OTHER_DOMAIN));

        $this->assertNull($action->execute(self::OTHER_URL));
    }
}
