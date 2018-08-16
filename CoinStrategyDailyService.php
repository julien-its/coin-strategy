<?php
namespace AppBundle\Services\Coins;

abstract class CoinStrategyDailyService extends CoinStrategyService
{
    public function initDailyCoins() : int
    {
        // Get the number max by day
        $applicationSetting = $this->em->getRepository('AppBundle:ApplicationSetting')->findOneByName($this->type->getSettingName());
        if(!$applicationSetting){
            return 0;
        }
        $maxNumberByDay = (int)$applicationSetting->getValue();

        $userCoin = $this->em->getRepository('AppBundle:UserCoin')->findOneBy(array('user' => $this->user, 'type' => $this->type));
        if(!$userCoin) {
            return 0;
        }

        // Add N coins to the limit
        $numberToAdd = $maxNumberByDay - $userCoin->getBalance();
        if($numberToAdd <= 0){
            return 0;
        }

        $this->addCoins($numberToAdd, 'Your daily '.$this->type->getTitle());
        return $numberToAdd;
    }


}
