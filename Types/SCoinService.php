<?php
namespace AppBundle\Services\Coins\Types;

use AppBundle\Services\Coins;

class SCoinService extends Coins\CoinStrategyDailyService
{
    protected $participationCost = 10;

    public function giveBonus() : \AppBundle\Services\Coins\ICoinStrategy
    {
        // TODO: Implement giveBonus() method.
    }
}
