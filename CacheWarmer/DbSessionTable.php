<?php
/**
 * WindowsAzure DistributionBundle
 *
 * LICENSE
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@beberlei.de so I can send you a copy immediately.
 */

namespace WindowsAzure\DistributionBundle\CacheWarmer;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use PDO;

/**
 * Checks if the Database Session Table is created and if not creates it with
 * the appropriate name and table columns so that sessions are automatically
 * working.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class DbSessionTable implements CacheWarmerInterface
{
    /**
     * @var PDO
     */
    private $pdo;

    /**
     * @var array
     */
    private $dbOptions;

    public function __construct(PDO $pdo, array $dbOptions)
    {
        if (!array_key_exists('db_table', $dbOptions)) {
            throw new \InvalidArgumentException('You must provide the "db_table" option for a PdoSessionStorage.');
        }

        $this->pdo = $pdo;
        $this->dbOptions = array_merge(array(
            'db_id_col'   => 'sess_id',
            'db_data_col' => 'sess_data',
            'db_time_col' => 'sess_time',
            'db_id_length' => '32',
        ), $dbOptions);
    }

    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     */
    public function warmUp($cacheDir)
    {
        $sql = "SELECT name FROM sysobjects WHERE type = 'U' AND name = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(1, $this->dbOptions['db_table']);
        $stmt->execute();

        if ( ! $stmt->fetch()) {
            $sql = "CREATE TABLE " . $this->dbOptions['db_table'] . " (" .
                   $this->dbOptions['db_id_col'] . " VARCHAR(" . $this->dbOptions['db_id_length'].") PRIMARY KEY NONCLUSTERED, " .
                   $this->dbOptions['db_data_col'] . " NVARCHAR(MAX), " .
                   $this->dbOptions['db_time_col'] . " INT)";
            $this->pdo->exec($sql);

            $sql = "CREATE CLUSTERED INDEX sess_time_idx ON " .
                    $this->dbOptions['db_table'] . " (" . $this->dbOptions['db_time_col'] . ")";
            $this->pdo->exec($sql);
        }
    }

    public function isOptional()
    {
        return false;
    }
}

