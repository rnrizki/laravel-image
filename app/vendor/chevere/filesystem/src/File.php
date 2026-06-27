<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevere.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\Filesystem;

use Chevere\Filesystem\Exceptions\FileNotExistsException;
use Chevere\Filesystem\Exceptions\FileUnableToCreateException;
use Chevere\Filesystem\Exceptions\FileUnableToGetException;
use Chevere\Filesystem\Exceptions\FileUnableToPutException;
use Chevere\Filesystem\Exceptions\FileUnableToRemoveException;
use Chevere\Filesystem\Exceptions\PathExistsException;
use Chevere\Filesystem\Exceptions\PathIsDirectoryException;
use Chevere\Filesystem\Interfaces\FileInterface;
use Chevere\Filesystem\Interfaces\PathInterface;
use RuntimeException;
use function Chevere\Message\message;
use function file_get_contents;
use function file_put_contents;
use function filesize;
use function unlink;

final class File implements FileInterface
{
    private bool $isPhp;

    public function __construct(
        private PathInterface $path
    ) {
        $this->isPhp = str_ends_with($this->path->__toString(), '.php');
        $this->assertIsNotDirectory();
    }

    public function path(): PathInterface
    {
        return $this->path;
    }

    public function isPhp(): bool
    {
        return $this->isPhp;
    }

    public function exists(): bool
    {
        return $this->path->isFile();
    }

    public function assertExists(): void
    {
        if (! $this->exists()) {
            throw new FileNotExistsException(
                (string) message(
                    "File `%path%` doesn't exists",
                    path: $this->path->__toString()
                )
            );
        }
    }

    public function getChecksum(): string
    {
        $this->assertExists();
        $hashFile = hash_file(FileInterface::CHECKSUM_ALGO, $this->path->__toString());
        if (is_string($hashFile)) {
            return $hashFile;
        }
        // @codeCoverageIgnoreStart
        // @infection-ignore-all
        throw new RuntimeException(
            (string) message(
                'Unable to get checksum for file `%path%`',
                path: $this->path->__toString()
            )
        );
        // @codeCoverageIgnoreEnd
    }

    public function getSize(): int
    {
        $this->assertExists();
        $return = filesize($this->path->__toString());
        if ($return === false) {
            throw new RuntimeException();
        }

        return $return;
    }

    /**
     * @throws FileNotExistsException
     * @throws FileUnableToGetException
     */
    public function getContents(): string
    {
        $this->assertExists();
        $return = file_get_contents($this->path->__toString());
        if ($return === false) {
            throw new FileUnableToGetException(
                (string) message(
                    'Unable to read the contents of the file at `%path%`',
                    path: $this->path->__toString()
                )
            );
        }

        return $return;
    }

    public function remove(): void
    {
        $this->assertExists();
        $this->assertUnlink();
    }

    public function removeIfExists(): void
    {
        if (! $this->exists()) {
            return;
        }
        $this->assertUnlink();
    }

    public function create(): void
    {
        if ($this->path->exists()) {
            throw new PathExistsException(
                (string) message(
                    'Unable to create file `%path%` (file already exists)',
                    path: $this->path->__toString()
                )
            );
        }
        $this->createPath();
        $puts = file_put_contents($this->path->__toString(), '');
        if ($puts === false) {
            throw new FileUnableToCreateException();
        }
    }

    public function createIfNotExists(): void
    {
        if ($this->exists()) {
            return;
        }
        $this->create();
    }

    public function put(string $contents): void
    {
        $this->assertExists();
        $puts = file_put_contents($this->path->__toString(), $contents);
        if ($puts === false) {
            throw new FileUnableToPutException(
                (string) message(
                    'Unable to write content to file `%filepath%`',
                    filepath: $this->path->__toString()
                )
            );
        }
    }

    private function createPath(): void
    {
        $dirname = dirname($this->path->__toString());
        $path = new Path($dirname . '/');
        if (! $path->exists()) {
            (new Directory($path))->create();
        }
    }

    private function assertIsNotDirectory(): void
    {
        if ($this->path->isDirectory()) {
            throw new PathIsDirectoryException(
                (string) message(
                    'Path `%path%` is a directory',
                    path: $this->path->__toString()
                )
            );
        }
    }

    private function assertUnlink(): void
    {
        $unlink = unlink($this->path->__toString());
        if ($unlink === false) {
            throw new FileUnableToRemoveException();
        }
    }
}
