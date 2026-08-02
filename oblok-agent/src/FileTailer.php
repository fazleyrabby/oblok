<?php

namespace OblokAgent;

class FileTailer
{
    /** @var resource|null */
    private $handle = null;

    private int $size = 0;

    public function __construct(private readonly string $file) {}

    /**
     * Read lines appended to the file since the last call.
     *
     * @return array<int, string>
     */
    public function readNewLines(): array
    {
        if ($this->handle === null) {
            $this->open();
        }

        clearstatcache(true, $this->file);
        $currentSize = @filesize($this->file);

        if ($currentSize === false) {
            $this->reset();
        } elseif ($currentSize < $this->size) {
            $this->reset();
        }

        $lines = [];

        while (($line = fgets($this->handle)) !== false) {
            $line = rtrim($line, "\r\n");

            if ($line !== '') {
                $lines[] = $line;
            }
        }

        if (feof($this->handle)) {
            // Reset the sticky EOF flag so newly appended lines are seen on the next poll.
            fseek($this->handle, 0, SEEK_CUR);
        }

        $this->size = (int) ftell($this->handle);

        return $lines;
    }

    /**
     * Re-open the file from the start (handles rotation/truncation).
     */
    private function reset(): void
    {
        if (is_resource($this->handle)) {
            fclose($this->handle);
        }

        $this->handle = null;
        $this->size = 0;
        $this->open();
    }

    private function open(): void
    {
        $handle = @fopen($this->file, 'r');

        if ($handle === false) {
            $this->handle = null;
            $this->size = 0;

            return;
        }

        $this->handle = $handle;
        fseek($this->handle, 0, SEEK_END);
        $this->size = (int) ftell($this->handle);
    }

    public function __destruct()
    {
        if (is_resource($this->handle)) {
            fclose($this->handle);
        }
    }
}
