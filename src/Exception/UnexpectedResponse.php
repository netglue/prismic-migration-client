<?php

declare(strict_types=1);

namespace Prismic\Migration\Exception;

use RuntimeException;

final class UnexpectedResponse extends RuntimeException implements ApiError
{
}
