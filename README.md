# RealHosting PowerPanel Tool

Very basic command line tool for interacting with RealHosting PowerPanel.

DISCLAIMER: I'm not affiliated with RealHosting in any way, this library is not supported by RealHosting. Please note that the PowerPanel API is currently not public and may change at any moment. No warranties. Usage of this tool might completely ruin your DNS settings.

## Installing

Run `composer install` to get dependencies.
Copy and configuration file `cp config.yml.dist config.yml` and put your PowerPanel email and password.

## Operation

Run `php pp-tool` to get a list of all possible commands.

* `domains:list` – List all domains
* `dns:show` – Show DNS records for a domain
* `dns:update` – Update a DNS record for a domain (does not create new records!)
* `dns:migrate` – Migrate all IP addresses in all DNS records for a domain

Run `php pp-tool help <command-name>` to get a list of parameters for a command.
