<?php

namespace App\FilterForm;

use App\Entity\SubCategory;
use Spiriit\Bundle\FormFilterBundle\Filter\Doctrine\ORMQuery;

trait SubCategoryFilterTypeTrait
{
    public function getSubCategoryFilterTypeOptions(): array
    {
        return [
            'class' => SubCategory::class,
            'apply_filter' => function (ORMQuery $filterQuery, $field, $values) {
                if (null === $values['value']) {
                    return null;
                }

                $field = sprintf('%s.subCategory', $values['alias']);
                $expression = $filterQuery->getExpr()->eq($field, ':subCategory');
                $parameters = ['subCategory' => $values['value']];

                return $filterQuery->createCondition($expression, $parameters);
            },
            'group_by' => function ($choice) {
                return $this->translator->trans($choice->getTransactionType());
            },
        ];
    }
}
