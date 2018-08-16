<?php
namespace AppBundle\Services\Coins;

interface ICoinsService
{
    function setUser(\AppBundle\Entity\User $user);

    function addCoins(string $typeName, int $number);

    function substractCoins(string $typeName, int $number, \AppBundle\Entity\Contest $contest);

    function initBalance();

    function initDailyCoins();

    function getBalance() : array;

    function hasEnoughFor1Participation($typeName) : bool;
}
