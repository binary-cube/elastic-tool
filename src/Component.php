<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool;

use Psr\Log\NullLogger;
use Psr\Log\LoggerInterface;

/**
 * Class Component
 */
class Component
{

    /**
     * @var string
     */
    protected $id;

    /**
     * The logger instance.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @return string
     */
    public static function generateUniqueId(): string
    {
        return \vsprintf('%s.%s', [static::class, \uniqid('', true)]);
    }

    /**
     * Constructor.
     *
     * @param string|null          $id
     * @param LoggerInterface|null $logger
     */
    public function __construct(string $id = null, $logger = null)
    {
        $this->id     = (! empty($id) ? $id : self::generateUniqueId());
        $this->logger = empty($logger) ? new NullLogger() : $logger;
    }

    /**
     * @return string
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger
     *
     * @return $this
     */
    public function logger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @return void
     */
    public function __destruct()
    {
        unset($this->id, $this->logger);
    }

}
