<?php declare(strict_types=1);

/**
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

namespace danog\MadelineProto\EventHandler\Filter\Combinator;

use Attribute;
use danog\MadelineProto\EventHandler;
use danog\MadelineProto\EventHandler\Filter\Filter;
use danog\MadelineProto\EventHandler\Update;

/**
 * NOTs a filter.
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class FilterNot extends Filter
{
    public function __construct(private readonly Filter $filter)
    {
    }
    public function initialize(EventHandler $API): Filter
    {
        $filter = $this->filter->initialize($API);
        if ($filter instanceof self) {
            // The nested filter is a FilterNot, optimize !!A => A
            return $filter->filter;
        }
        if ($filter === $this->filter) {
            // The nested filter didn't replace itself
            return $this;
        }
        // The nested filter replaced itself, re-wrap it
        return new self($filter);
    }

    public function apply(Update $update): bool
    {
        return !$this->filter->apply($update);
    }
}
