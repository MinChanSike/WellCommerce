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
namespace WellCommerce\Bundle\CompanyBundle\DataGrid;

use WellCommerce\Bundle\DataGridBundle\DataGrid\AbstractDataGrid;
use WellCommerce\Bundle\DataGridBundle\DataGrid\Column\Column;
use WellCommerce\Bundle\DataGridBundle\DataGrid\Column\ColumnCollection;
use WellCommerce\Bundle\DataGridBundle\DataGrid\Column\Options\Appearance;
use WellCommerce\Bundle\DataGridBundle\DataGrid\Column\Options\Filter;
use WellCommerce\Bundle\DataGridBundle\DataGrid\Column\Options\Sorting;
use WellCommerce\Bundle\DataGridBundle\DataGrid\DataGridInterface;
use WellCommerce\Bundle\DataGridBundle\DataGrid\Options\OptionsInterface;

/**
 * Class CompanyDataGrid
 *
 * @package WellCommerce\Bundle\CompanyBundle
 * @author  Adam Piotrowski <adam@wellcommerce.org>
 */
class CompanyDataGrid extends AbstractDataGrid implements DataGridInterface
{
    /**
     * {@inheritdoc}
     */
    public function configureColumns(ColumnCollection $collection)
    {
        $collection->add(new Column([
            'id'         => 'id',
            'caption'    => 'company.id',
            'sorting'    => new Sorting([
                'default_order' => Sorting::SORT_DIR_ASC
            ]),
            'appearance' => new Appearance([
                'width'   => 90,
                'visible' => false
            ]),
            'filter'     => new Filter([
                'type' => Filter::FILTER_BETWEEN
            ])
        ]));

        $collection->add(new Column([
            'id'         => 'name',
            'caption'    => 'company.name',
            'appearance' => new Appearance([
                'width' => 70,
            ]),
            'filter'     => new Filter([
                'type' => Filter::FILTER_INPUT
            ])
        ]));

        $collection->add(new Column([
            'id'         => 'shortName',
            'caption'    => 'company.short_name',
            'appearance' => new Appearance([
                'width' => 70,
            ]),
            'filter'     => new Filter([
                'type' => Filter::FILTER_INPUT
            ])
        ]));
    }
}