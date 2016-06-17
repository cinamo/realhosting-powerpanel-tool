<?php

namespace Cinamo\PowerPanel\Command;

use Cinamo\PowerPanel\PowerPanelClient;
use Symfony\Component\Console\Command\Command;

abstract class PowerPanelCommand extends Command
{
    /** @var PowerPanelClient */
    protected $client;

    public function __construct(PowerPanelClient $powerPanelClient)
    {
        parent::__construct();
        $this->client = $powerPanelClient;
    }
}