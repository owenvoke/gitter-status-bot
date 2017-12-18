<?php

namespace pxgamer\GitterStatusBot;

use Dotenv\Dotenv;
use Gitter\Client as Gitter;
use UptimeRobot\API as UptimeRobot;

/**
 * Class Bot.
 */
class Bot
{
    public const STATUS_ONLINE = 2;
    public const STATUS_EXPERIENCING_ISSUES = 8;
    public const STATUS_OFFLINE = 9;

    /**
     * @var Gitter
     */
    protected $gitter;
    /**
     * @var UptimeRobot
     */
    protected $uptimerobot;

    /**
     * @var array
     */
    protected $info = [
        'status' => null
    ];

    /**
     * Bot constructor.
     *
     * @param string $dotEnvPath
     */
    public function __construct(string $dotEnvPath)
    {
        $dotEnv = new Dotenv($dotEnvPath);
        $dotEnv->load();
        $dotEnv->required([
            'GITTER_KEY',
            'GITTER_ROOM',
            'UPTIME_ROBOT_KEY'
        ])->notEmpty();

        $this->uptimerobot = new UptimeRobot([
            'apiKey' => getenv('UPTIME_ROBOT_KEY'),
            'url'    => 'https://api.uptimerobot.com'
        ]);
        $this->gitter = new Gitter(getenv('GITTER_KEY'));
    }

    /**
     * Check the status from UptimeRobot.
     *
     * @throws \Exception | \Throwable
     */
    public function checkUptime()
    {
        $results = $this->uptimerobot->request('/getMonitors');

        if (isset($results['stat']) && $results['stat'] === 'ok') {
            $status = (int)$results['monitors']['monitor'][0]['status'];
        } else {
            $status = null;
        }
        switch ($status) {
            case self::STATUS_ONLINE:
                $this->info['status'] = Statuses::ONLINE;
                break;
            case self::STATUS_EXPERIENCING_ISSUES:
                $this->info['status'] = Statuses::EXPERIENCING_ISSUES;
                break;
            case self::STATUS_OFFLINE:
                $this->info['status'] = Statuses::OFFLINE;
                break;
            default:
                $this->info['status'] = Statuses::UNKNOWN;
                break;
        }
    }

    /**
     * Post the status to Gitter.
     *
     * @throws \Exception | \Throwable
     */
    public function postToGitter()
    {
        if (!$this->info['status']) {
            throw new \Exception(Statuses::NO_STATUS);
        }

        $this->gitter->messages->create(
            getenv('GITTER_ROOM'),
            'Current status: ' . $this->info['status']
        );
    }
}
