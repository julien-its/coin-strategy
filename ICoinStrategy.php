<?php
namespace AppBundle\Services\Coins;

interface ICoinStrategy
{
    function setEm(\Doctrine\ORM\EntityManager $em) : ICoinStrategy;

    function setType(\AppBundle\Entity\CoinType $type) : ICoinStrategy;

    function setUser(\AppBundle\Entity\User $user) : ICoinStrategy;

    function addCoins(int $number, string $remark=null) : ICoinStrategy;

    function substractCoins(int $number, \AppBundle\Entity\Contest $contest=null, $remark=null) : ICoinStrategy;

    function addOperation(int $number, \AppBundle\Entity\Contest $contest=null, $remark=null) : ICoinStrategy;

    function initBalance() : ICoinStrategy;

    function getBalance() : int;

    function giveBonus() : ICoinStrategy;
}
