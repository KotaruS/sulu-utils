<?php

declare(strict_types=1);

namespace Kotaru\SuluUtils\Common;

enum PropertyAccess: int
{
    case NOT_FOUND = -1;
    case IS_NULL = 0;
    case FOUND = 1;
}
