<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Service\Shipment;

use Generated\Shared\Transfer\ShipmentGroupTransfer;
use Generated\Shared\Transfer\ShipmentTransfer;
use Spryker\Service\Kernel\AbstractService;
use \ArrayObject;

/**
 * @method \Spryker\Service\Shipment\ShipmentServiceFactory getFactory()
 */
class ShipmentService extends AbstractService implements ShipmentServiceInterface
{
    /**
     * {@inheritdoc}
     *
     * @api
     *
     * @param \ArrayObject|\Generated\Shared\Transfer\ItemTransfer[] $itemTransfers
     *
     * @return \ArrayObject|\Generated\Shared\Transfer\ShipmentGroupTransfer[]
     */
    public function groupItemsByShipment(ArrayObject $itemTransfers): ArrayObject
    {
        $shipmentGroupTransfers = new ArrayObject();

        foreach ($itemTransfers as $itemTransfer) {
            $itemTransfer->requireShipment();

            $hash = $this->getItemHash($itemTransfer->getShipment());
            if (!isset($shipmentGroupTransfers[$hash])) {
                $shipmentGroupTransfers[$hash] = (new ShipmentGroupTransfer())
                    ->setShipment($itemTransfer->getShipment());
            }

            $shipmentGroupTransfers[$hash]->addItem($itemTransfer);
        }

        return $shipmentGroupTransfers;
    }

    /**
     * @param \Generated\Shared\Transfer\ShipmentTransfer $shipmentTransfer
     *
     * @return string
     */
    protected function getItemHash(ShipmentTransfer $shipmentTransfer): string
    {
        $shippingMethod = '';

        if ($shipmentTransfer->getMethod() !== null) {
            $shippingMethod = (string)$shipmentTransfer->getMethod()->getIdShipmentMethod();
        }

        return md5(implode([
            $shippingMethod,
            $shipmentTransfer->getShippingAddress()->serialize(),
            $shipmentTransfer->getRequestedDeliveryDate(),
        ]));
    }

    /**
     * @api
     *
     * @param array $shipmentFormData
     *
     * @return \Generated\Shared\Transfer\ShipmentGroupTransfer
     */
    public function createShipmentGroupTransfer(array $shipmentFormData): ShipmentGroupTransfer
    {
        $shipmentGroupTransfer = new ShipmentGroupTransfer();
        $shipmentGroupTransfer->setShipment(
            (new ShipmentTransfer)->fromArray($data, true)
        );

        foreach ($data[ShipmentForm::FIELD_ORDER_ITEMS] as $item) {
            $shipmentGroupTransfer->addItem($item);
        }

        return $shipmentGroupTransfer;
    }
}
