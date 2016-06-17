<?php

namespace Cinamo\PowerPanel\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DnsUpdateCommand extends PowerPanelCommand
{
    protected function configure()
    {
        $this
            ->setName('dns:update')
            ->setDescription('Update a DNS record for a domain')
            ->addArgument(
                'domain',
                InputArgument::REQUIRED,
                'For which domain would you like to update the DNS records?'
            )
            ->addArgument(
                'record_type',
                InputArgument::REQUIRED,
                'DNS type record (A, CNAME, TXT, PTR)'
            )
            ->addArgument(
                'record_name',
                InputArgument::REQUIRED,
                'Full name of the DNS record (www.domain.ext, mail.domain.ext, ...)'
            )
            ->addArgument(
                'content',
                InputArgument::REQUIRED,
                'New content for the DNS record'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $domain = $input->getArgument('domain');
        $type = $input->getArgument('record_type');
        $name = $input->getArgument('record_name');

        // Add domain to create a FQDN
        if(stripos($name, $domain) === false) {
            $name .= ".$domain";
        }

        $content = $input->getArgument('content');
        $result = $this->client->updateDnsRecord($domain, $name, $type, $content);

        if($result === true) {
            $output->writeln("DNS record updated successfully.");
        } else {
            $output->writeln("An error occurred during the update.");
        }
    }
}