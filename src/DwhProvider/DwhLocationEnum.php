<?php

declare(strict_types=1);

namespace DbtTransformation\DwhProvider;

enum DwhLocationEnum
{
    case LOCAL;
    case REMOTE;
}
