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

namespace WellCommerce\Bundle\LocaleBundle\DataSet\Admin;

use Doctrine\ORM\QueryBuilder;
use WellCommerce\Bundle\CoreBundle\DataSet\AbstractDataSet;
use WellCommerce\Bundle\AppBundle\Entity\Currency;
use WellCommerce\Bundle\LocaleBundle\Entity\Locale;
use WellCommerce\Component\DataSet\Cache\CacheOptions;
use WellCommerce\Component\DataSet\Configurator\DataSetConfiguratorInterface;

/**
 * Class LocaleDataSet
 *
 * @author Adam Piotrowski <adam@wellcommerce.org>
 */
class LocaleDataSet extends AbstractDataSet
{
    public function getIdentifier(): string
    {
        return 'admin.locale';
    }
    
    public function configureOptions(DataSetConfiguratorInterface $configurator)
    {
        $configurator->setColumns([
            'id'       => 'locale.id',
            'code'     => 'locale.code',
            'currency' => 'default_currency.code',
        ]);
        
        $configurator->setCacheOptions(new CacheOptions(true, 3600, [
            Locale::class,
            Currency::class,
        ]));
    }
    
    protected function createQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->repository->getQueryBuilder();
        $queryBuilder->groupBy('locale.id');
        $queryBuilder->leftJoin('locale.currency', 'default_currency');
        $queryBuilder->orderBy('locale.code', 'asc');
        
        return $queryBuilder;
    }
}
