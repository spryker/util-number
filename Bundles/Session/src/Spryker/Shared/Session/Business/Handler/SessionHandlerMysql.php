<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\Session\Business\Handler;

use PDO;
use SessionHandlerInterface;
use Spryker\Shared\Kernel\Store;
use Spryker\Shared\Session\Dependency\Service\SessionToMonitoringServiceInterface;

class SessionHandlerMysql implements SessionHandlerInterface
{
    public const METRIC_SESSION_DELETE_TIME = 'Mysql/Session_delete_time';
    public const METRIC_SESSION_WRITE_TIME = 'Mysql/Session_write_time';
    public const METRIC_SESSION_READ_TIME = 'Mysql/Session_read_time';

    /**
     * @var \PDO|null
     */
    protected $connection;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var string|null
     */
    protected $user = null;

    /**
     * @var string|null
     */
    protected $password = null;

    /**
     * @var string
     */
    protected $keyPrefix = 'session:';

    /**
     * @var int
     */
    protected $lifetime = 600;

    /**
     * @var int
     */
    protected $port = 3306;

    /**
     * @var \Spryker\Shared\Session\Dependency\Service\SessionToMonitoringServiceInterface
     */
    protected $monitoringService;

    /**
     * @var string
     */
    protected $environmentName;

    /**
     * @param \Spryker\Shared\Session\Dependency\Service\SessionToMonitoringServiceInterface $monitoringService
     * @param array $hosts
     * @param string|null $user
     * @param string|null $password
     * @param int $lifetime
     * @param string $environmentName
     */
    public function __construct(SessionToMonitoringServiceInterface $monitoringService, $hosts, $user, $password, $lifetime, $environmentName)
    {
        $host = $hosts[0];
        if (strpos($host, ':')) {
            $parts = explode(':', $host);
            $host = $parts[0];
            $this->port = (int)$parts[1];
        }

        $this->monitoringService = $monitoringService;
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->lifetime = $lifetime;
        $this->environmentName = $environmentName;

        $databaseName = 'shared_data';
        $dsn = 'mysql:host=' . $this->host . ';port=' . $this->port . ';dbname=' . $databaseName;
        $this->connection = new PDO($dsn, $this->user, $this->password);

        $this->initDb();
    }

    /**
     * @param string $savePath
     * @param string $sessionName
     *
     * @return bool
     */
    public function open($savePath, $sessionName)
    {
        return $this->connection ? true : false;
    }

    /**
     * @return bool
     */
    public function close()
    {
        unset($this->connection);

        return true;
    }

    /**
     * @param string $sessionId
     *
     * @return string|null
     */
    public function read($sessionId)
    {
        $key = $this->keyPrefix . $sessionId;
        $startTime = microtime(true);

        $store = Store::getInstance()->getStoreName();
        $query = 'SELECT * FROM session WHERE session.key=? AND session.store=? AND session.environment=? AND session.expires >= session.updated_at + ' . $this->lifetime . ' LIMIT 1';

        $statement = $this->connection->prepare($query);
        $statement->execute([$key, $store, $this->environmentName]);
        $result = $statement->fetch();
        $this->monitoringService->addCustomParameter(self::METRIC_SESSION_READ_TIME, microtime(true) - $startTime);

        return $result ? json_decode($result['value'], true) : '';
    }

    /**
     * @param string $sessionId
     * @param string $sessionData
     *
     * @return bool
     */
    public function write($sessionId, $sessionData)
    {
        $key = $this->keyPrefix . $sessionId;

        if (strlen($sessionData) < 1) {
            return false;
        }

        $startTime = microtime(true);
        $data = json_encode($sessionData);
        $expireTimestamp = time() + $this->lifetime;
        $expires = date('Y-m-d H:i:s', $expireTimestamp);

        $storeName = Store::getInstance()->getStoreName();
        $timestamp = date('Y-m-d H:i:s', time());
        $query = 'REPLACE INTO session (session.key, session.value, session.store, session.environment, session.expires, session.updated_at) VALUES (?,?,?,?,?,?)';

        $statement = $this->connection->prepare($query);
        $result = $statement->execute([$key, $data, $storeName, $this->environmentName, $expires, $timestamp]);

        $this->monitoringService->addCustomParameter(self::METRIC_SESSION_WRITE_TIME, microtime(true) - $startTime);

        return $result;
    }

    /**
     * @param int|string $sessionId
     *
     * @return bool
     */
    public function destroy($sessionId)
    {
        $key = $this->keyPrefix . $sessionId;

        $startTime = microtime(true);

        $query = sprintf('DELETE FROM session WHERE key = "%s"', $key);

        $this->connection->exec($query);
        $this->monitoringService->addCustomParameter(self::METRIC_SESSION_DELETE_TIME, microtime(true) - $startTime);

        return true;
    }

    /**
     * @param int $maxLifetime
     *
     * @return bool
     */
    public function gc($maxLifetime)
    {
        return true;
    }

    /**
     * @return void
     */
    protected function initDb()
    {
        $query = "CREATE TABLE IF NOT EXISTS `session` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `key` varchar(255) NOT NULL DEFAULT '',
          `value` longtext NOT NULL,
          `store` varchar(2) NOT NULL DEFAULT '',
          `environment` enum('DEVELOPMENT','TESTING','STAGING','PRODUCTION','QUALITY01','QUALITY02','QUALITY03','QUALITY04') NOT NULL DEFAULT 'DEVELOPMENT',
          `expires` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
          PRIMARY KEY (`id`),
          UNIQUE KEY `key` (`key`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;";

        $statement = $this->connection->query($query);
        $statement->execute();
    }
}
