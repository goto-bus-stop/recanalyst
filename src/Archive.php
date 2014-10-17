<?php
/**
 * Defines Archive class.
 *
 * @package RecAnalyst
 */

namespace RecAnalyst;

/**
 * Class Archive.
 * Archive implements Zip archive containing recorded games.
 *
 * @package RecAnalyst
 * @todo rar extension support
 * @todo test for server zlib/zip extension support
 * @todo be useful (i.e. have analyze methods on this analyze all included recs etc)
 */
class Archive
{

    /**
     * Contains entry details.
     * @var array
     */
    protected $stats;

    /**
     * Zip file archive.
     * @var ZipArchive
     */
    protected $zip;

    /**
     * Determines if the archive is open.
     * @var bool
     */
    protected $open;

    const MGX_EXT = 'mgx';
    const MGL_EXT = 'mgl';
    const MGZ_EXT = 'mgz';
    const MGX2_EXT = 'mgx2';

    /**
     * Class constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->stats = array();
        $this->zip = new \ZipArchive();
        $this->open = false;
    }

    /**
     * Opens a file archive.
     *
     * @param string $filename The file name of the archive to open.
     *
     * @return void
     * @throws Exception
     */
    public function open($filename)
    {
        if ($this->zip->open($filename) !== true) {
            throw new \Exception('Unable to open zip archive ' . $filename);
        }
        $this->open = true;
        $this->getDetails();
    }

    /**
     * Close the active archive.
     *
     * @return void
     */
    public function close()
    {
        if ($this->open) {
            $this->zip->close();
        }
    }

    /**
     * Get a file handler to the entry defined by its name.
     *
     * @param string $name The name of the entry to use.
     *
     * @return resource|bool File pointer (resource) on success or false on failure.
     * @throws Exception
     */
    public function getFileHandler($name)
    {
        if (!$this->open) {
            throw new \Exception('No archive has been opened');
        }
        return $this->zip->getStream($name);
    }

    /**
     * Returns the entry contents using its name.
     *
     * @param string $name The name of the entry.
     *
     * @return mixed The contents of the entry on success or false on failure.
     * @throws Exception
     */
    public function getFileContents($name)
    {
        if (!$this->open) {
            throw new \Exception('No archive has been opened');
        }
        return $this->zip->getFromName($name);
    }

    /**
     * Get the details of the entries in the archive.
     *
     * @return void
     *
     * @throws Exception
     */
    protected function getDetails()
    {
        if (!$this->open) {
            throw new \Exception('No archive has been opened');
        }
        for ($i = 0; false !== ($stat = $this->zip->statIndex($i)); $i++) {
            // skip directories and 0-bytes files
            if (!$stat['size']) {
                continue;
            }
            // skip useless files
            $ext = strtolower(pathinfo($stat['name'], PATHINFO_EXTENSION));
            if ($ext != self::MGX_EXT && $ext != self::MGL_EXT
                && $ext != self::MGZ_EXT && $ext != self::MGX2_EXT
            ) {
                continue;
            }
            $this->stats[] = $stat;
        }
    }

    /**
     * Returns entry details.
     *
     * @return array
     */
    public function getStats()
    {
        return $this->stats;
    }
}
