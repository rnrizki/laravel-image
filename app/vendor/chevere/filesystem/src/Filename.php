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

use Chevere\Filesystem\Interfaces\FilenameInterface;
use InvalidArgumentException;
use LengthException;
use function Chevere\Message\message;

final class Filename implements FilenameInterface
{
    private string $extension;

    private string $name;

    public function __construct(
        private string $filename
    ) {
        $this->assertFilename();
        $this->extension = pathinfo($this->filename, PATHINFO_EXTENSION);
        $this->name = pathinfo($this->filename, PATHINFO_FILENAME);
    }

    public function __toString(): string
    {
        return $this->filename;
    }

    public function extension(): string
    {
        return $this->extension;
    }

    public function name(): string
    {
        return $this->name;
    }

    private function assertFilename(): void
    {
        if ($this->filename === '' || ctype_space($this->filename)) {
            throw new InvalidArgumentException();
        }
        if (strlen($this->filename) <= self::MAX_LENGTH_BYTES) {
            return;
        }
        $message = (string) message(
            'String `%string%` provided exceed the limit of `%bytes%` bytes',
            string: $this->filename,
            bytes: self::MAX_LENGTH_BYTES,
        );

        throw new LengthException($message, 110);
    }
}
