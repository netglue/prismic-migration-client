<?php

declare(strict_types=1);

namespace Prismic\Migration\Exception;

use RuntimeException;

final class RuntimeError extends RuntimeException implements ApiError
{
}
