<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PriceProductOffer\Business\Constraint;

use Generated\Shared\Transfer\PriceProductOfferCriteriaTransfer;
use Generated\Shared\Transfer\PriceProductStoreCriteriaTransfer;
use Generated\Shared\Transfer\PriceProductTransfer;
use Spryker\Zed\Kernel\Communication\Validator\AbstractConstraintValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ValidUniqueStoreCurrencyGrossNetPriceDataConstraintValidator extends AbstractConstraintValidator
{
    /**
     * Checks if the Valid from value is earlier than Valid to.
     *
     * @param \Generated\Shared\Transfer\PriceProductTransfer $value
     * @param \Symfony\Component\Validator\Constraint $constraint
     *
     * @throws \Symfony\Component\Validator\Exception\UnexpectedTypeException
     *
     * @return void
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof PriceProductTransfer) {
            throw new UnexpectedTypeException($value, PriceProductTransfer::class);
        }

        if (!$constraint instanceof ValidUniqueStoreCurrencyGrossNetPriceDataConstraint) {
            throw new UnexpectedTypeException($constraint, ValidUniqueStoreCurrencyGrossNetPriceDataConstraint::class);
        }

        if (!$value->getPriceDimension()->getIdProductOffer()) {
            return;
        }

        $moneyValueTransfer = $value->getMoneyValueOrFail();

        $priceProductStoreCriteria = new PriceProductStoreCriteriaTransfer();
        $priceProductStoreCriteria->addIdStore($moneyValueTransfer->getFkStoreOrFail())
            ->addIdCurrency($moneyValueTransfer->getFkCurrencyOrFail());

        $priceProductOfferCriteriaTransfer = new PriceProductOfferCriteriaTransfer();
        $priceProductOfferCriteriaTransfer->setIdProductOffer($value->getPriceDimension()->getIdProductOffer())
            ->setPriceProductStoreCriteria($priceProductStoreCriteria)
            ->addIdPriceType($value->getPriceType()->getIdPriceTypeOrFail());

        $priceProductTransfers = $constraint->getPriceProductOfferRepository()->getProductOfferPrices($priceProductOfferCriteriaTransfer);
        if (
            $priceProductTransfers->count() > 1
            || ($priceProductTransfers->count() === 1
                && $priceProductTransfers->offsetGet(0)->getMoneyValue()->getIdEntity() !== $value->getMoneyValue()->getIdEntity())
        ) {
            $this->context->addViolation($constraint->getMessage());
        }
    }
}
