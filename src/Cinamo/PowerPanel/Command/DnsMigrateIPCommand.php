<?php

namespace Cinamo\PowerPanel\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DnsMigrateIPCommand extends PowerPanelCommand
{
    protected function configure()
    {
        $this
            ->setName('dns:migrate')
            ->setDescription('Migrate all IP addresses in all DNS records for a domain')
            ->addArgument(
                'domain',
                InputArgument::REQUIRED,
                'For which domain would you like to update the IP address in DNS records?'
            )
            ->addArgument(
                'old_ip',
                InputArgument::REQUIRED,
                'Old IP address'
            )
            ->addArgument(
                'new_ip',
                InputArgument::REQUIRED,
                'New IP address'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $domain = $input->getArgument('domain');
        $oldIP = $input->getArgument('old_ip');
        $newIP = $input->getArgument('new_ip');

        $records = $this->client->getDnsRecordsForDomain($domain);

        foreach ($records as $record) {
            switch ($record['type']) {
                case 'A':
                case 'CNAME':
                    if ($record['content'] === $oldIP) {
                        $this->client->updateDnsRecord($domain, $record['name'], $record['type'], $newIP);
                        $output->writeln("Updated {$record['type']} {$record['name']} to $newIP");
                    }
                    break;
                case 'TXT':
                    if (strpos($record['content'], $oldIP) !== false) {
                        $newContent = str_replace($oldIP, $newIP, $record['content']);
                        $this->client->updateDnsRecord($domain, $record['name'], $record['type'], $newContent);
                        $output->writeln("Updated {$record['type']} {$record['name']} to $newContent");
                    }
                    break;
            }
        }
    }
}