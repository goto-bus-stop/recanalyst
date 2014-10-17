<?php
/**
 * Defines Stream class.
 *
 * @package RecAnalyst
 */

namespace RecAnalyst;

/**
 * Class Stream.
 *
 * Stream is a base class type for stream objects.
 * @package RecAnalyst
 * @subpackage basics
 * @abstract
 */
abstract class Stream
{

    /* Stream seek origins */
    const soFromBeginning = 0;
    const soFromCurrent = 1;
    const soFromEnd = 2;

    /**
     * Class constructor.
     *
     * @return void
     *
     * @abstract
     */
    abstract public function __construct();

    /**
     * Class destructor.
     *
     * @return void
     *
     * @abstract
     */
    abstract public function __destruct();

    /**
     * Get the current position in the stream.
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->seek(0, self::soFromCurrent);
    }

    /**
     * Set the current position in the stream.
     *
     * @param int $pos Position
     *
     * @return void
     */
    public function setPosition($pos)
    {
        $this->seek($pos, self::soFromBeginning);
    }

    /**
     * Get the size of the stream.
     *
     * @return int
     */
    public function getSize()
    {
        $pos = $this->seek(0, self::soFromCurrent);
        $result = $this->seek(0, self::soFromEnd);
        $this->seek($pos, self::soFromBeginning);
        return $result;
    }

    /**
     * Reads the contents of the stream from the current position into a buffer.
     *
     * @param mixed $buff Buffer the data will be transferred into
     * @param int $count Number of bytes to read
     *
     * @return int Number of bytes actually transferred to buffer
     * @abstract
     */
    abstract protected function read(&$buffer, $count);

    /**
     * Writes the buffer into the stream, starting at the current position.
     *
     * @param mixed $buff Data we want to insert
     * @return int Number of bytes actually inserted.
     *
     * @abstract
     */
    abstract protected function write($buffer);

    /**
     * Moves the current position within the stream by the indicated offset, relative to the origin.
     *
     * @param int $offset Offset
     * @param int $origin One of the seek stream origings
     *
     * @return int The current position
     * @abstract
     */
    abstract public function seek($offset, $origin);

    /**
     * Reads bytes from the stream into buffer.
     *
     * @param mixed $buff
     * @param int $count
     *
     * @return void
     * @throws Exception
     */
    public function readBuffer(&$buffer, $count)
    {
        if ($count != 0 && $this->read($buffer, $count) != $count) {
            throw new \Exception('Stream read error');
        }
    }

    /**
     * Writes bytes from buffer onto the stream.
     *
     * @param mixed $buff
     * @param int $count
     *
     * @return void
     * @throws Exception
     */
    public function writeBuffer($buffer)
    {
        if (($count = strlen($buffer)) != 0 && $this->write($buffer) != $count) {
            throw new \Exception('Stream write error');
        }
    }

    /**
     * Copies a specified number of bytes from one stream to another.
     *
     * @param Stream $source Source stream
     * @param int $count Number of bytes
     *
     * @return int Number of bytes copied.
     */
    public function copyFrom(Stream $source, $count)
    {
        $maxBufSize = 0xF000;
        if ($count == 0) {
            $source->position = 0;
            $count = $source->size;
        }
        $result = $count;
        $bufSize = ($count > $maxBufSize) ? $maxBufSize : $count;
        while ($count != 0) {
            $n = ($count > $bufSize) ? $bufSize : $count;
            $source->readBuffer($buffer, $n);
            $this->writeBuffer($buffer, $n);
            $count -= $n;
        }
        return $result;
    }
}
