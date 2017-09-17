<?php

namespace Agna\Resource\Loader\Resource;

/**
 * FileResource
 *
 * @package Agna\Resource\Loader\Reader\File
 * @author Agoston Nagy <agoston.nagy@use.github.please>
 */
class File extends AbstractResource
{
    use LocationTrait;

    const TYPE_FILE = 'fileResource';

    /**
     * @var string
     */
    protected $basename;

    /**
     * @var string
     */
    protected $dirname;

    /**
     * @var string
     */
    protected $extension;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @var int
     */
    protected $creationTime;

    /**
     * @var int
     */
    protected $modificationTime;

    /**
     * @var int
     */
    protected $size;

    /**
     * Resource constructor.
     *
     * @param mixed|null $location
     * @param null $data
     */
    public function __construct($location, $data = null)
    {
        $this->location = realpath($location);

        $pathInfo = pathinfo($location);
        foreach ($pathInfo as $part => $value) {
            $this->$part = $value;
        }

        $stat = stat($location);

        $this->size = $stat[7];
        $this->creationTime = $stat[10];
        $this->modificationTime = $stat[9];

        parent::__construct($data);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return static::TYPE_FILE;
    }

    /**
     * @return mixed
     */
    public function getBasename()
    {
        return $this->basename;
    }

    /**
     * @return mixed
     */
    public function getDirname()
    {
        return $this->dirname;
    }

    /**
     * @return mixed
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @return mixed
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @return int
     */
    public function getCreationTime()
    {
        return $this->creationTime;
    }

    /**
     * @return int
     */
    public function getModificationTime()
    {
        return $this->modificationTime;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    public function __toString()
    {
        return $this->getLocation();
    }
}