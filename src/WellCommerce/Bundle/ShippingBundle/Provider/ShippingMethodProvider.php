<?php
/*
 * WellCommerce Open-Source E-Commerce Platform
 * 
 * This file is part of the WellCommerce package.
 *
 * (c) Adam Piotrowski <adam@wellcommerce.org>
 * 
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace WellCommerce\Bundle\ShippingBundle\Provider;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use WellCommerce\Bundle\ShippingBundle\Calculator\ShippingCalculatorInterface;
use WellCommerce\Bundle\ShippingBundle\Calculator\ShippingSubjectInterface;
use WellCommerce\Bundle\ShippingBundle\Entity\ShippingMethod;
use WellCommerce\Bundle\ShippingBundle\Entity\ShippingMethodCost;
use WellCommerce\Bundle\ShippingBundle\Exception\CalculatorNotFoundException;
use WellCommerce\Bundle\ShippingBundle\Repository\ShippingMethodRepositoryInterface;
use WellCommerce\Bundle\AppBundle\Entity\ShopInterface;
use WellCommerce\Bundle\AppBundle\Storage\ShopStorageInterface;

/**
 * Class ShippingMethodProvider
 *
 * @author  Adam Piotrowski <adam@wellcommerce.org>
 */
final class ShippingMethodProvider implements ShippingMethodProviderInterface
{
    /**
     * @var ShippingMethodRepositoryInterface
     */
    private $repository;
    
    /**
     * @var Collection
     */
    private $calculators;
    
    /**
     * @var ShopStorageInterface
     */
    private $shopStorage;
    
    /**
     * ShippingMethodProvider constructor.
     *
     * @param ShippingMethodRepositoryInterface $repository
     * @param Collection                        $calculators
     * @param ShopStorageInterface              $shopStorage
     */
    public function __construct(ShippingMethodRepositoryInterface $repository, Collection $calculators, ShopStorageInterface $shopStorage)
    {
        $this->repository  = $repository;
        $this->calculators = $calculators;
        $this->shopStorage = $shopStorage;
    }
    
    public function getCosts(ShippingSubjectInterface $subject): Collection
    {
        $methods    = $this->getShippingMethods($subject);
        $collection = new ArrayCollection();
        
        $methods->map(function (ShippingMethod $shippingMethod) use ($subject, $collection) {
            $costs = $this->getShippingMethodCosts($shippingMethod, $subject);
            
            $costs->map(function (ShippingMethodCost $cost) use ($collection) {
                $collection->add($cost);
            });
        });
        
        return $collection;
    }
    
    public function getShippingMethodCosts(ShippingMethod $method, ShippingSubjectInterface $subject): Collection
    {
        $calculator = $this->getCalculator($method);
        $country    = $subject->getCountry();
        $countries  = $method->getCountries();
        $shop       = $this->getCurrentShop($subject);
        
        if (strlen($country) && count($countries) && !in_array($country, $countries)) {
            return new ArrayCollection();
        }
        
        if (false === $method->getShops()->contains($shop)) {
            return new ArrayCollection();
        }
        
        return $calculator->calculate($method, $subject);
    }
    
    private function getCalculator(ShippingMethod $shippingMethod): ShippingCalculatorInterface
    {
        $calculator = $shippingMethod->getCalculator();
        
        if (false === $this->calculators->containsKey($calculator)) {
            throw new CalculatorNotFoundException($calculator);
        }
        
        return $this->calculators->get($calculator);
    }
    
    private function getShippingMethods(ShippingSubjectInterface $subject): Collection
    {
        $methods = $this->repository->getShippingMethods();
        $country = $subject->getCountry();
        $shop    = $this->getCurrentShop($subject);
        
        return $methods->filter(function (ShippingMethod $method) use ($country, $shop) {
            if (strlen($country) && count($method->getCountries()) && !in_array($country, $method->getCountries())) {
                return false;
            }
            
            return $method->getShops()->contains($shop);
        });
    }
    
    private function getCurrentShop(ShippingSubjectInterface $subject): ShopInterface
    {
        if (!$subject->getShop() instanceof ShopInterface) {
            return $this->shopStorage->getCurrentShop();
        }
        
        return $subject->getShop();
    }
}
