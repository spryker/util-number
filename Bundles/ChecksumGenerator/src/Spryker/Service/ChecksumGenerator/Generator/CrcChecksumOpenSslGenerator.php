<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Service\ChecksumGenerator\Generator;

use Spryker\Service\ChecksumGenerator\Dependency\Service\CheckSumToUtilEncryptionServiceInterface;

class CrcChecksumOpenSslGenerator implements ChecksumOpenSslGeneratorInterface
{
    /**
     * @var \Spryker\Service\ChecksumGenerator\Dependency\Service\CheckSumToUtilEncryptionServiceInterface
     */
    protected $utilEncryptionService;

    /**
     * @param \Spryker\Service\ChecksumGenerator\Dependency\Service\CheckSumToUtilEncryptionServiceInterface $utilEncryptionService
     */
    public function __construct(CheckSumToUtilEncryptionServiceInterface $utilEncryptionService)
    {
        $this->utilEncryptionService = $utilEncryptionService;
    }

    /**
     * @param array $data
     * @param string $encryptionKey
     * @param string $hexInitializationVector
     *
     * @return string
     */
    public function generateOpenSslChecksum(array $data, string $encryptionKey, string $hexInitializationVector): string
    {
        $dataChecksum = $this->prepareDataCheckSum($data);
        $initializationVector = $this->prepareInitializationVector($hexInitializationVector);

        return $this->utilEncryptionService->encryptOpenSsl($dataChecksum, $initializationVector, $encryptionKey);
    }

    /**
     * @param string $hexInitializationVector
     *
     * @return string
     */
    protected function prepareInitializationVector(string $hexInitializationVector): string
    {
        return hex2bin($hexInitializationVector);
    }

    /**
     * @param array $data
     *
     * @return string
     */
    protected function prepareDataCheckSum(array $data): string
    {
        $serializedData = serialize($data);
        $dataChecksum = crc32($serializedData);

        return (string)$dataChecksum;
    }
}
