<?php

namespace Cinamo\PowerPanel\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DomainsListCommand extends PowerPanelCommand
{
    protected function configure()
    {
        $this
            ->setName('domains:list')
            ->setDescription('List all domains')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $domains = $this->client->getDomains();
        sort($domains);
        foreach($domains as $domain) {
            $output->writeln($domain);
        }
    }
}