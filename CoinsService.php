<?php
// src/AppBundle/Services/UserService.php
namespace AppBundle\Services\Coins;

use Doctrine\ORM\EntityManager;

use AppBundle\Entity\User;
use AppBundle\Services\Coins\Types;

class CoinsService implements ICoinsService
{
    protected $em;

    /* @var $user \AppBundle\Entity\User */
    protected $user;

    protected $types;
    protected $coinServices;

    protected $coinStrategy;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->types = array();
        $this->coinServices = array();
    }

    public function setUser(\AppBundle\Entity\User $user)
    {
        $this->user = $user;
        return $this;
    }

    public function reset()
    {
        $this->user = null;
        $this->types = array();
        $this->coinStrategy = null;
        $this->coinServices = array();
        return $this;
    }

    private function _checkUser()
	{
		if(!$this->user){
			Throw new \Exception('Set user first');
		}
	}

	private function _getCoinService(\AppBundle\Entity\CoinType $type)
    {
        if(!key_exists($type->getName(), $this->coinServices) || $this->coinServices[$type->getName()] == null){
            $coinServiceName = "\\AppBundle\\Services\\Coins\Types\\{$type->getClassName()}Service";
            $coinService = new $coinServiceName();
            $coinService->setEm($this->em)
                ->setType($type)
                ->setUser($this->user);
            $this->coinServices[$type->getName()] = $coinService;
        }
        return $this->coinServices[$type->getName()];
    }

    private function _getType(string $typeName)
    {
        if(!key_exists($typeName, $this->types) || $this->types[$typeName] == null){
            $this->type[$typeName] = $this->em->getRepository('AppBundle:CoinType')->findOneByName($typeName);
        }
        return $this->type[$typeName];
    }

    public function hasEnoughFor1Participation($typeName) : bool
    {
        $coinService = $this->_getCoinService($this->_getType($typeName));
        return $coinService->getBalance() >= $coinService->getParticipationCost();
    }

    public function use1Participation($typeName, \AppBundle\Entity\Contest $contest)
    {
        $coinService = $this->_getCoinService($this->_getType($typeName));
        $coinService->substractCoins($coinService->getParticipationCost(), $contest, 'Play to contest');
    }

    /*
     *  Add coins to user
     */
    public function addCoins(string $typeName, int $number)
    {
        $this->_checkUser();

        $this->_getCoinService($this->_getType($typeName))->addCoins($number);

        return $this;
    }

    /*
     *  Substract coins to user and contest
     */
    public function substractCoins(string $typeName, int $number, \AppBundle\Entity\Contest $contest)
    {
        $this->_checkUser();
        $this->_getCoinService($this->_getType($typeName))
                ->substractCoins($number, $contest, 'Play to contest');

        return $this;
    }

    /*
    * Initalize balance for the defined user and for all coin types.
    * Create a userCoin row if not existing yet and set balance to 0.
    */
    public function initBalance()
    {
        $this->_checkUser();
        $coinTypes = $this->em->getRepository('AppBundle:CoinType')->findAll();
        foreach($coinTypes as $coinType)
        {
            $this->_getCoinService($coinType)->initBalance();
        }
        return $this;
    }

    /*
     * return the balance for the defined user and all coin types
     */
    public function getBalance() : array
    {
        $balance = array();
        $coinTypes = $this->em->getRepository('AppBundle:CoinType')->findAll();
        foreach($coinTypes as $coinType)
        {
            $balance[$coinType->getName()] = $this->_getCoinService($coinType)->getBalance();
        }
        return $balance;
    }

    /*
     * Initialize daily coins for the defined user and for all daily coin types
     */
    public function initDailyCoins() : array
    {
        $report = array();
        $this->_checkUser();
        $coinTypes = $this->em->getRepository('AppBundle:CoinType')->findAll();
        foreach($coinTypes as $coinType)
        {
            if($coinType->getStrategy() == \AppBundle\Entity\CoinType::STRATEGY_DAILY){
                $coinsAdded = $this->_getCoinService($coinType)->initDailyCoins();
                $report[] = array('type' => $coinType->getName(), 'coins' => $coinsAdded);
            }
        }

        $this->user->setLastVCoinInitialization(new \DateTime());
        $this->em->persist($this->user);
        $this->em->flush();

        return $report;
    }
}
