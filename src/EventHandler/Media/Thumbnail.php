<?php declare(strict_types=1);

/**
 * This file is part of MadelineProto.
 * MadelineProto is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * MadelineProto is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 * You should have received a copy of the GNU General Public License along with MadelineProto.
 * If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    Mahdi <mahdi.talaee1379@gmail.com>
 * @copyright 2016-2023 Mahdi <mahdi.talaee1379@gmail.com>
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPLv3
 * @link https://docs.madelineproto.xyz MadelineProto documentation
 */

namespace danog\MadelineProto\EventHandler\Media;

use Amp\ByteStream\ReadableStream;
use Amp\Cancellation;
use danog\MadelineProto\Ipc\IpcCapable;
use danog\MadelineProto\MTProto;
use JsonSerializable;

/**
 * This object represents one size of a photo or a file / sticker thumbnail.
 */
class Thumbnail extends IpcCapable implements JsonSerializable
{
    /** Identifier for this file, which can be used to download or reuse the file */
    public readonly string $botApiFileId;
    /** Unique identifier for this file, which is supposed to be the same over time and for different bots. Can't be used to download or reuse the file. */
    public readonly string $botApiFileUniqueId;
    /** Photo width */
    public readonly int $width;
    /** Photo height */
    public readonly int $height;
    /** File size in bytes */
    public readonly string $size;
    /** Thumb file name */
    public readonly string $fileName;

    /** Thumb file extension */
    public readonly string $fileExt;
    public readonly ?string $mimeType;
    public function __construct(
        MTProto $API,
        array $rawThumbnail,

        /** Whether this media is protected */
        public readonly bool $protected = false
    ) {
        parent::__construct($API);
        $this->botApiFileId = $rawThumbnail['file_id'];
        $this->botApiFileUniqueId = $rawThumbnail['file_unique_id'];
        $this->width = $rawThumbnail['width'];
        $this->height = $rawThumbnail['height'];
        $this->size = $rawThumbnail['file_size'];
        $this->fileName = $rawThumbnail['file_name'] ?? 'Thumbnail';
        $this->fileExt = $rawThumbnail['file_ext'] ?? '.jpg';
        $this->mimeType = $rawThumbnail['mime_type'] ?? null;

    }

    /** @internal */
    public function jsonSerialize(): mixed
    {
        $v = get_object_vars($this);
        unset($v['API'], $v['session']);
        return $v;
    }

    /**
     * Gets a download link for any file up to 4GB.
     *
     * @param string|null $scriptUrl Optional path to custom download script (not needed when running via web)
     */
    public function getDownloadLink(?string $scriptUrl = null): string
    {
        return $this->getClient()->getDownloadLink($this, $scriptUrl);
    }

    /**
     * Get a readable amp stream with the file contents.
     *
     * @param (callable(float, float, float): void)|null $cb Progress callback
     */
    public function getStream(?callable $cb = null, int $offset = 0, int $end = -1, ?Cancellation $cancellation = null): ReadableStream
    {
        return $this->getClient()->downloadToReturnedStream($this, $cb, $offset, $end, $cancellation);
    }

    /**
     * Download the media to working directory or passed path.
     *
     * @param string $dir Directory where to download the file
     * @param (callable(float, float, float): void)|null $cb Progress callback
     */
    public function downloadToDir(?string $dir = null, ?callable $cb = null, ?Cancellation $cancellation = null): string
    {
        $dir ??= getcwd();
        return $this->getClient()->downloadToDir($this, $dir, $cb, $cancellation);
    }
    /**
     * Download the media to file.
     *
     * @param string $file Downloaded file path
     * @param (callable(float, float, float): void)|null $cb Progress callback
     */
    public function downloadToFile(string $file, ?callable $cb = null, ?Cancellation $cancellation = null): string
    {
        return $this->getClient()->downloadToFile($this, $file, $cb, $cancellation);
    }

    /**
     * @return array{
     *      ext: string,
     *      name: string,
     *      mime: string,
     *      size: int
     * }
     */
    public function getDownloadInfo(): array
    {
        $result = [
            'name' => basename($this->fileName, $this->fileExt),
            'ext' => $this->fileExt,
            'mime' => $this->mimeType,
            'size' => $this->size,
        ];
        return $result;
    }
}
