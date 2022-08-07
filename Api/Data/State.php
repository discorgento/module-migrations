<?php declare(strict_types=1);
/** Copyright © Caravel X. All rights reserved. */

namespace Discorgento\Migrations\Api\Data;

use Magento\Framework\App\Area;

interface StateInterface
{
    public const AREA_CODE_GLOBAL = Area::AREA_GLOBAL;
    public const AREA_CODE_FRONTEND = Area::AREA_FRONTEND;
    public const AREA_CODE_ADMINHTML = Area::AREA_ADMINHTML;
    public const AREA_CODE_DOC = Area::AREA_DOC;
    public const AREA_CODE_CRONTAB = Area::AREA_CRONTAB;
    public const AREA_CODE_WEBAPI_REST = Area::AREA_WEBAPI_REST;
    public const AREA_CODE_WEBAPI_SOAP = Area::AREA_WEBAPI_SOAP;
    public const AREA_CODE_GRAPHQL = Area::AREA_GRAPHQL;
}
