<?php declare(strict_types=1);

/**
 * RPCErrorException module.
 *
 * This file is part of MadelineProto.
 * MadelineProto is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * MadelineProto is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 * You should have received a copy of the GNU General Public License along with MadelineProto.
 * If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2025 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPLv3
 * @link https://docs.madelineproto.xyz MadelineProto documentation
 */

namespace danog\MadelineProto\RPCError;

use Amp\Cancellation;
use danog\MadelineProto\RPCErrorException;
use Exception;

use function Amp\delay;

/**
 * Represents a rate limiting RPC error returned by telegram.
 */
class RateLimitError extends RPCErrorException
{
    /**
     * Indicates the absolute expiration time of the flood wait.
     *
     * @var positive-int
     */
    public readonly int $expires;

    /** @internal */
    public function __construct(
        string $message,
        /** @var positive-int */
        public readonly int $waitTime,
        int $code,
        string $caller,
        ?Exception $previous = null
    ) {
        $this->expires = time() + $waitTime;
        parent::__construct($message, "A rate limit was encountered, please repeat the method call after $waitTime seconds", $code, $caller, $previous);
    }

    /**
     * Returns the required waiting period in seconds before repeating the RPC call.
     *
     * @return positive-int
     */
    public function getWaitTime(): int
    {
        return $this->waitTime;
    }

    /**
     * Returns the remaining waiting period in seconds before repeating the RPC call.
     *
     * @return int<0, max>
     */
    public function getWaitTimeLeft(): int
    {
        return max(0, $this->expires - time());
    }

    /**
     * Waits for the remaining waiting period.
     */
    public function wait(?Cancellation $cancellation = null): void
    {
        $left = $this->getWaitTimeLeft();
        if ($left > 0) {
            delay($left, cancellation: $cancellation);
        }
    }
}
