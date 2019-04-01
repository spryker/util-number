<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CompanyRolesRestApi\Processor\CompanyRole\Reader;

use Generated\Shared\Transfer\CompanyRoleTransfer;
use Generated\Shared\Transfer\RestCompanyRoleAttributesTransfer;
use Spryker\Glue\CompanyRolesRestApi\Dependency\Client\CompanyRolesRestApiToCompanyRoleClientInterface;
use Spryker\Glue\CompanyRolesRestApi\Processor\CompanyRole\Mapper\CompanyRoleMapperInterface;
use Spryker\Glue\CompanyRolesRestApi\Processor\CompanyRole\RestResponseBuilder\CompanyRoleRestResponseBuilderInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;

class CompanyRoleReader implements CompanyRoleReaderInterface
{
    /**
     * @var \Spryker\Glue\CompanyRolesRestApi\Dependency\Client\CompanyRolesRestApiToCompanyRoleClientInterface
     */
    protected $companyRoleClient;

    /**
     * @var \Spryker\Glue\CompanyRolesRestApi\Processor\CompanyRole\Mapper\CompanyRoleMapperInterface
     */
    protected $companyRoleMapperInterface;

    /**
     * @var \Spryker\Glue\CompanyRolesRestApi\Processor\CompanyRole\RestResponseBuilder\CompanyRoleRestResponseBuilderInterface
     */
    protected $companyRoleRestResponseBuilder;

    /**
     * @param \Spryker\Glue\CompanyRolesRestApi\Dependency\Client\CompanyRolesRestApiToCompanyRoleClientInterface $companyRoleClient
     * @param \Spryker\Glue\CompanyRolesRestApi\Processor\CompanyRole\Mapper\CompanyRoleMapperInterface $companyRoleMapperInterface
     * @param \Spryker\Glue\CompanyRolesRestApi\Processor\CompanyRole\RestResponseBuilder\CompanyRoleRestResponseBuilderInterface $companyRoleRestResponseBuilder
     */
    public function __construct(
        CompanyRolesRestApiToCompanyRoleClientInterface $companyRoleClient,
        CompanyRoleMapperInterface $companyRoleMapperInterface,
        CompanyRoleRestResponseBuilderInterface $companyRoleRestResponseBuilder
    ) {
        $this->companyRoleClient = $companyRoleClient;
        $this->companyRoleMapperInterface = $companyRoleMapperInterface;
        $this->companyRoleRestResponseBuilder = $companyRoleRestResponseBuilder;
    }

    /**
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function getCompanyRole(RestRequestInterface $restRequest): RestResponseInterface
    {
        $companyRoleUuid = $restRequest->getResource()->getId();
        if (!$companyRoleUuid) {
            return $this->companyRoleRestResponseBuilder->createCompanyRoleIdMissingError();
        }

        $companyRoleResponseTransfer = $this->companyRoleClient->findCompanyRoleByUuid(
            (new CompanyRoleTransfer())->setUuid($companyRoleUuid)
        );

        if (!$companyRoleResponseTransfer->getIsSuccessful()
            || !$this->isCurrentCompanyUserAuthorizedToAccessResource($restRequest, $companyRoleResponseTransfer->getCompanyRoleTransfer())
        ) {
            return $this->companyRoleRestResponseBuilder->createCompanyRoleNotFoundError();
        }

        $restCompanyRoleAttributesTransfer = $this->companyRoleMapperInterface
            ->mapCompanyRoleTransferToRestCompanyRoleAttributesTransfer(
                $companyRoleResponseTransfer->getCompanyRoleTransfer(),
                new RestCompanyRoleAttributesTransfer()
            );

        return $this->companyRoleRestResponseBuilder
            ->createCompanyRoleRestResponse(
                $companyRoleUuid,
                $restCompanyRoleAttributesTransfer,
                $companyRoleResponseTransfer->getCompanyRoleTransfer()
            );
    }

    /**
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     * @param \Generated\Shared\Transfer\CompanyRoleTransfer $companyRoleTransfer
     *
     * @return bool
     */
    protected function isCurrentCompanyUserAuthorizedToAccessResource(
        RestRequestInterface $restRequest,
        CompanyRoleTransfer $companyRoleTransfer
    ): bool {
        return $restRequest->getUser()->getRestUser()
            && $restRequest->getUser()->getRestUser()->getIdCompany()
            && $restRequest->getUser()->getRestUser()->getIdCompany() === $companyRoleTransfer->getFkCompany();
    }
}
