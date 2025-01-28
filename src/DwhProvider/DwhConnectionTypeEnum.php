<?php

declare(strict_types=1);

namespace DbtTransformation\DwhProvider;

enum DwhConnectionTypeEnum
{
    case LOCAL;
    case REMOTE;
}
