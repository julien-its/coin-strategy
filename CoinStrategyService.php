<?php
namespace AppBundle\Services\Coins;

abstract class CoinStrategyService implements ICoinStrategy
{
    protected $type, $user, $userCoin;

    /* @var $em \Doctrine\ORM\EntityManager */
    protected $em;

    protected $participationCost = 1;

    public function setType(\AppBundle\Entity\CoinType $type) : ICoinStrategy
    {
        $this->type = $type;
        return $this;
    }

    public function setUser(\AppBundle\Entity\User $user) : ICoinStrategy
    {
        $this->user = $user;
        return $this;
    }

    public function setEm(\Doctrine\ORM\EntityManager $em) : ICoinStrategy
    {
        $this->em = $em;
        return $this;
    }

    public function getParticipationCost()
    {
        return $this->participationCost;
    }

    public function getUserCoin()
    {
        if($this->userCoin == null){
            $this->userCoin = $this->em->getRepository('AppBundle:UserCoin')->findOneBy(array('user' => $this->user, 'type' => $this->type));
        }
        return $this->userCoin;
    }

    public function addCoins(int $number, string $remark=null) : ICoinStrategy
    {
        return $this->addOperation($number, null, $remark);
    }

    public function substractCoins(int $number, \AppBundle\Entity\Contest $contest=null, $remark=null) : ICoinStrategy
    {
        return $this->addOperation($number *-1, $contest, $remark);
    }

    public function addOperation(int $number, \AppBundle\Entity\Contest $contest=null, $remark=null) : ICoinStrategy
    {
        // Increase the balance
        $userCoin = $this->getUserCoin();
        $userCoin->setBalance($userCoin->getBalance() + $number);

        // Add a history row
        $userCoinsHistory = new \AppBundle\Entity\UserCoinsHistory();
        $userCoinsHistory->setUser($this->user);
        $userCoinsHistory->setType($this->type);
        $userCoinsHistory->setCoins($number);
        $userCoinsHistory->setRemark($remark);
        $userCoinsHistory->setContest($contest);

        $this->em->persist($userCoin);
        $this->em->persist($userCoinsHistory);
        $this->em->flush();
        return $this;
    }

    public function getBalance() : int
    {
        $userCoin = $this->getUserCoin();
        if($userCoin == null)
        {
            return 0;
        }
        return $userCoin->getBalance();
    }

    /*
     * Initialize the balance of the user and coin type.
     * If userCoin row not existing, create one with 0 as balance.
     */
    public function initBalance() : ICoinStrategy
    {
        if($this->getUserCoin() == null){
            $this->userCoin = new \AppBundle\Entity\UserCoin();
            $this->userCoin->setUser($this->user);
            $this->userCoin->setType($this->type);
            $this->userCoin->setBalance(0);

            $this->em->persist($this->userCoin);
            $this->em->flush();
        }
        return $this;
    }

    abstract function giveBonus() : ICoinStrategy;
}
