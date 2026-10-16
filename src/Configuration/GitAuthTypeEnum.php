<?php

declare(strict_types=1);

namespace DbtTransformation\Configuration;

enum GitAuthTypeEnum: string
{
    case PASSWORD = 'password';
    case GITHUB_APP = 'githubApp';
}
