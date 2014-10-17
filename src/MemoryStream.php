<?php
/**
 * Defines MemoryStream class.
 *
 * @package RecAnalyst
 */

namespace RecAnalyst;

/**
 * Class MemoryStream.
 *
 * MemoryStream is a stream that stores its data in dynamic memory.
 *
 * @package RecAnalyst
 */
class MemoryStream extends Stream
{

    /**
     * Internal data holder.
     * @var string
     */
    protected $dataString = '';

    /**
     * Data size.
     * @var int
     */
    protected $size = 0;

    /**
     * Current position.
     * @var int
     */
    protected $position = 0;

    /**
     * Class constructor.
     *
     * @param string $string Data string
     *
     * @return void
     */
    public function __construct($string = '')
    {
        $this->dataString = $string;
        $this->size = strlen($this->dataString);
        $this->position = 0;
    }

    /**
     * Class destructor.
     *
     * @return void
     */
    public function __destruct()
    {
        $this->dataString = '';
        $this->size = $this->position = 0;
    }

    /**
     * @see Stream::read()
     */
    public function read(&$buffer, $count)
    {
        if ($count > 0 && ($len = $this->size - $this->position) > 0) {
            if ($len > $count) {
                $len = $count;
            }
            $buffer = substr($this->dataString, $this->position, $len);
            $this->position += $len;
            return $len;
        }
        return 0;
    }

    /**
     * @see Stream::write()
     */
    public function write($buffer)
    {
        if ($this->position == $this->size) {
            $this->dataString .= $buffer;
        } else {
            $this->dataString = substr_replace($this->dataString, $buffer, $this->position, 0);
        }
        $this->size += ($len = strlen($buffer));
        $this->position += $len;
        return $len;
    }

    /**
     * @see Stream::seek()
     */
    public function seek($offset, $origin)
    {
        switch ($origin) {
        case self::soFromBeginning:
            $this->position = $offset;
            break;
        case self::soFromCurrent:
            $this->position += $offset;
            break;
        case self::soFromEnd:
            $this->position = $this->size - $offset;
            break;
        }
        if ($this->position > $this->size) {
            $this->position = $this->size;
        } elseif ($this->position < 0) {
            $this->position = 0;
        }
        return $this->position;
    }

    /**
     * Moves the current position within the stream by the indicated offset, relative to the current position.
     *
     * @param int $count Offset.
     *
     * @return The current position.
     */
    public function skip($count)
    {
        $this->position += $count;
        if ($this->position > $this->size) {
            $this->position = $this->size;
        } elseif ($this->position < 0) {
            $this->position = 0;
        }
        return $this->position;
    }

    /**
     * Returns the data string.
     *
     * @return string
     */
    public function getDataString()
    {
        return $this->dataString;
    }

    /**
     * Reads the string into the buffer.
     *
     * @param string $buffer
     * @param int    $length Number of bytes holding string length information.
     *
     * @return void
     * @throws Exception
     */
    public function readString(&$buffer, $length = 4)
    {
        switch ($length) {
        case 2:
            $this->readWord($len);
            break;
        case 4:
        default:
            $this->readUInt($len);
            break;
        }
        if ($len) {
            $this->readBuffer($buffer, $len);
        } else {
            $buffer = '';
        }
    }

    /**
     * Reads integer value into the buffer.
     *
     * @param int $buffer
     *
     * @return void
     */
    public function readUInt(&$buffer)
    {
        $bytes = substr($this->dataString, $this->position, 4);
        $this->position += 4;
        $unpacked_data = unpack('L', $bytes);
        $buffer = $unpacked_data[1];
    }

    /**
     * Reads integer value into the buffer.
     *
     * @param int $buffer
     *
     * @return void
     */
    public function readInt(&$buffer)
    {
        // !note: signed long (always 32 bit, machine byte order)
        $bytes = substr($this->dataString, $this->position, 4);
        $this->position += 4;
        $unpacked_data = unpack('l', $bytes);
        $buffer = $unpacked_data[1];
    }

    /**
     * Reads word value into the buffer.
     *
     * @param int $buffer
     *
     * @return void
     */
    public function readWord(&$buffer)
    {
        $bytes = substr($this->dataString, $this->position, 2);
        $this->position += 2;
        $buffer = ord($bytes[0]) | (ord($bytes[1]) << 8);
    }

    /**
     * Reads char value into the buffer.
     *
     * @param int $buffer
     *
     * @return void
     */
    public function readChar(&$buffer)
    {
        $buffer = ord($this->dataString[$this->position]);
        $this->position++;
    }

    /**
     * Reads float value into the buffer.
     *
     * @param int $buffer
     *
     * @return void
     */
    public function readFloat(&$buffer)
    {
        $bytes = substr($this->dataString, $this->position, 4);
        $this->position += 4;
        $unpacked_data = unpack('f', $bytes);
        $buffer = $unpacked_data[1];
    }

    /**
     * Reads Bool value into the buffer.
     *
     * @param int $buffer
     *
     * @return void
     */
    public function readBool(&$buffer)
    {
        $this->readUInt($int);
        $buffer = ($int == 0) ? false : true;
    }

    /**
     * Find position of first occurrence of a string in the stream.
     *
     * @param int $needle The string to find.
     *
     * @return int Position in the stream or -1 if needle has not been not found
     */
    public function find($needle)
    {
        $pos = strpos($this->dataString, $needle, $this->position);
        if ($pos === false) {
            $pos = -1;
        } else {
            $this->position = $pos;
        }
        return $pos;
    }

    /**
     * Find position of last occurrence of a string in the stream.
     *
     * @param int $needle The string to find.
     *
     * @return int Position in the stream or -1 if needle has not been not found
     */
    public function rfind($needle, $offset = 0)
    {
        $pos = strrpos($this->dataString, $needle, ($offset < 0) ? $offset : $this->position);
        if ($pos == false) {
            $pos = -1;
        } else {
            $this->position = $pos;
        }
        return $pos;
    }
}
