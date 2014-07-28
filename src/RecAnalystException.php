<?php
/**
 * Defines RecAnalystException class.
 *
 * @package recAnalyst
 */

namespace RecAnalyst;

/**
 * Class RecAnalystException.
 * @package recAnalyst
 */
class RecAnalystException extends \Exception {

    /* Generic exceptions */

    /**
     * The feature or option is planned but has not yet been implemented. It
     * should be available in a future revision of the package.
     */
    const NOT_IMPLEMENTED = 0x0001;

    /**
     * Trigger info block has not been found in the header section.
     */
    const TRIGGERINFO_NOTFOUND = 0x0001;

    /**
     * Game settings block has not been found in the header section.
     */
    const GAMESETTINGS_NOTFOUND = 0x0002;

    /**
     * No input has been specified for analysis.
     */
    const FILE_NOT_SPECIFIED = 0x0003;

    /**
     * File format is not supported for analysis.
     */
    const FILEFORMAT_NOT_SUPPORTED = 0x0004;

    /**
     * Error reading the header length information.
     */
    const HEADERLEN_READERROR = 0x0005;

    /**
     * Empty header length has been found.
     */
    const EMPTY_HEADER = 0x0006;

    /**
     * Error decompressing header section.
     */
    const HEADER_DECOMPRESSERROR = 0x0007;

    /**
     * Class constructor.
     * @param string $mgs Exception message
     * @param int $code Exception code
     * @return void
     */
    public function __construct($msg = '', $code = 0) {
        parent::__construct($msg, (int)$code);
    }

    /**
     * String representation of the exception.
     * @return string
     */
    public function __toString() {
        return parent::__toString();
    }
}
