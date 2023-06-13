<?php

namespace App\Console\Commands\Website;

use App\Models\Website\Config\WebsiteConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDO;
use PDOException;

/**
 * Class RestoreWebsiteHeadScript
 * @package App\Console\Commands\Website
 */
class RestoreWebsiteHeadScript extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Note: excludeIds should be Website IDs instead of Dealer IDs because some sites are classifieds
     * @var string
     */
    protected $signature = '
        website:restore-head-script
        {backupDbUrl : Url from the Backup DB}
        {excludeIds* : Websites Ids comma separated}
        {--debug=false : Debug Mode}
        ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore the website table\'s head_script value with decoded scripts from backup database excluding provided IDs';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $backupDbHost = $this->argument('backupDbUrl');
        $excludeIds = $this->argument('excludeIds');
        $debug = boolval($this->option('debug'));

        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $database = config('database.connections.mysql.database');

        try {
            // I use PDO to avoid setup backup database params on config so each run need update values there.
            $dsn = "mysql:host=$backupDbHost;dbname=$database;charset=utf8";
            $backupDbConnection = new PDO($dsn, $username, $password);
            // Set PDO attributes for read-only mode
            $backupDbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $backupDbConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $e) {
            $this->error('Could not connect to the backup database. Please check your credentials.');
            return 1;
        }

        $sql = "SELECT
                    `id` AS config_id,
                    `website_id` AS website_id,
                    FROM_BASE64(`value`) AS head_script
                FROM
                    trailercentral.website_config
                WHERE `key` = 'general/head_script' AND `value` <> '';";

        if (!empty($excludeIds)) {
            $excludeIdsString = implode(',', $excludeIds);
            $sql .= ' AND `website_id` NOT IN (' . $excludeIdsString . ')';
        }

        $stmt = $backupDbConnection->query($sql);
        $sourceData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        DB::transaction(function () use ($sourceData, $debug) {
            foreach ($sourceData as $source) {
                // Now complete redundancy, but I want to avoid making mistakes on the restore and avoid any breaks.
                // also prevent mismatch if the actual db have same id,website but not key
                $currentWebsite = WebsiteConfig::where([
                    ['id', '=', (int)$source->config_id],
                    ['website_id', '=', (int)$source->website_id],
                    ['key', '=', 'general/head_script']
                ])->first();

                if ($currentWebsite) {
                    $currentDecodedScript = base64_decode($currentWebsite->value);
                    $newScript = $currentDecodedScript . PHP_EOL . $source->head_script;
                    $newEncodedScript = base64_encode($newScript);

                    if ($debug) {
                        $this->info('Would restore website id ' . $source->website_id . ' with script: ' . json_encode($newEncodedScript));
                    } else {
                        $currentWebsite->value = $newEncodedScript;
                        $currentWebsite->save();
                    }
                }
            }
        });

        $this->info($debug ? 'Debug complete.' : 'Update completed.');

        return 0;
    }
}
