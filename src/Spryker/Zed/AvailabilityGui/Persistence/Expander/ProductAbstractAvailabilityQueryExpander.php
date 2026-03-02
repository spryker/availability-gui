<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AvailabilityGui\Persistence\Expander;

use Generated\Shared\Transfer\QueryCriteriaTransfer;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;

class ProductAbstractAvailabilityQueryExpander implements ProductAbstractAvailabilityQueryExpanderInterface
{
    /**
     * @var array<\Spryker\Zed\AvailabilityGuiExtension\Dependency\Plugin\AvailabilityAbstractTableQueryCriteriaExpanderPluginInterface>
     */
    protected $availabilityAbstractTableQueryCriteriaExpanderPlugins;

    /**
     * @param array<\Spryker\Zed\AvailabilityGuiExtension\Dependency\Plugin\AvailabilityAbstractTableQueryCriteriaExpanderPluginInterface> $availabilityAbstractTableQueryCriteriaExpanderPlugins
     */
    public function __construct(array $availabilityAbstractTableQueryCriteriaExpanderPlugins)
    {
        $this->availabilityAbstractTableQueryCriteriaExpanderPlugins = $availabilityAbstractTableQueryCriteriaExpanderPlugins;
    }

    public function expandQuery(ModelCriteria $query): ModelCriteria
    {
        $this->addJoin($query, $this->buildQueryCriteriaTransfer());

        return $query;
    }

    protected function buildQueryCriteriaTransfer(): QueryCriteriaTransfer
    {
        $queryCriteriaTransfer = new QueryCriteriaTransfer();

        foreach ($this->availabilityAbstractTableQueryCriteriaExpanderPlugins as $availabilityAbstractTableQueryCriteriaExpanderPlugin) {
            $queryCriteriaTransfer = $availabilityAbstractTableQueryCriteriaExpanderPlugin->expandQueryCriteria($queryCriteriaTransfer);
        }

        return $queryCriteriaTransfer;
    }

    protected function addJoin(
        ModelCriteria $query,
        QueryCriteriaTransfer $queryCriteriaTransfer
    ): ModelCriteria {
        foreach ($queryCriteriaTransfer->getJoins() as $queryJoinTransfer) {
            $joinType = $queryJoinTransfer->getJoinType() ?? Criteria::INNER_JOIN;
            if ($queryJoinTransfer->getRelation()) {
                $query->join($queryJoinTransfer->getRelation(), $joinType);

                if ($queryJoinTransfer->getCondition()) {
                    $query->addJoinCondition($queryJoinTransfer->getRelation(), $queryJoinTransfer->getCondition());
                }

                continue;
            }
            $query->addJoin($queryJoinTransfer->getLeft(), $queryJoinTransfer->getRight(), $joinType);
        }

        return $query;
    }
}
