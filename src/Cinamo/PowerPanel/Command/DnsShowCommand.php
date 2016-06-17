<?php

namespace Cinamo\PowerPanel\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DnsShowCommand extends PowerPanelCommand
{
    protected function configure()
    {
        $this
            ->setName('dns:show')
            ->setDescription('Show DNS records for a domain')
            ->addArgument(
                'domain',
                InputArgument::REQUIRED,
                'What domain would you like to get the DNS records for?'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $domain = $input->getArgument('domain');
        $details = $this->client->getDomainDetails($domain);
        $records = $this->client->getDnsRecordsForDomain($domain);

        $ns = $details['ns'];
        $dnsAtRealhosting = (stripos($details['ns'], '.2is.') !== false);
        $ns = ($dnsAtRealhosting) ? "<info>$ns</info> (RealHosting)" : "<comment>$ns</comment>";

        $output->writeln('<comment>' . $domain . '</comment>');
        $output->writeln('');
        $output->writeln('   Owner: <info>' . $details['owner'] . '</info>');
        $output->writeln('   DNS hosting: ' . ($details['dns_hosting'] ? '<info>yes</info>' : '<comment>no</comment>'));
        $output->writeln('   NS: ' . $ns);
        if(count($records) === 0) return;

        $output->writeln('');
        foreach($records as $record) {
            $output->writeln('   ' . $record['type'] . ' ' . $record['name'] . ': <info>' . $record['content'] . '</info>');
        }
    }
}