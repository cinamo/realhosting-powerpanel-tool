<?php

namespace Cinamo\PowerPanel;

use GuzzleHttp\Client;

class PowerPanelClient
{
    /** @var Client */
    private $client;

    /** @var string */
    private $email;

    /** @var string */
    private $password;

    /** @var string */
    private $userFullName;

    /** @var bool */
    private $isAuthenticated;

    /**
     * PowerPanelClient constructor.
     *
     * @param $username
     * @param $password
     */
    public function __construct($username, $password)
    {
        $this->client = new Client(['base_uri' => 'https://cp.realhosting.nl/json-api/', 'cookies' => true]);
        $this->email = $username;
        $this->password = $password;
    }

    /**
     * Authenticate to PowerPanel with the supplied username and password.
     *
     * @throws \Exception
     */
    protected function authenticate()
    {
        if(!$this->isAuthenticated) {
            $res = $this->client->post('user/verifyuser/0', [
                'json' => [
                    'kg_geslacht' => 'm',
                    'email'       => $this->email,
                    'password'    => $this->password
                ]
            ]);

            $userDetails = json_decode($res->getBody()->getContents(), true);
            if ($userDetails['loggedon'] !== true) {
                throw new \Exception("Cannot login");
            }
            $this->userFullName = $userDetails['kg_name'];
        }
    }

    /**
     * Get a list of all domains in your PowerPanel account.
     *
     * @return array
     * @throws \Exception
     */
    public function getDomains()
    {
        $this->authenticate();

        $domainsJson = $this->client->post('grid/domains/0/', ['json' => ['paging' => 'disabled']]);
        $domainsData = json_decode($domainsJson->getBody()->getContents(), true)['rowdata'];

        $domains = [];
        foreach ($domainsData as $domainData) {
            $domains[$domainData['dom_id']] = $domainData['dom_name'];
        }

        return $domains;
    }

    /**
     * Get details for a domain (owner, DNS hosting enabled, first name server)
     *
     * @param $domain
     *
     * @return array
     * @throws \Exception
     */
    public function getDomainDetails($domain)
    {
        $this->authenticate();

        $domainJson = $this->client->post('domains/getDomainDetails/0', ['json' => ['domain' => $domain]]);
        $domainData = json_decode($domainJson->getBody()->getContents(), true);
        if(array_key_exists('status', $domainData) && $domainData['status'] === false) {
            throw new \Exception("Unknown domain name $domain");
        }

        $owner = $domainData['PageBlocks']['DomainWhoisSettings']['DomainContacts']['owner'];
        $owner = ($owner['dc_companyname'] !== null) ? $owner['dc_companyname'] : $owner['dc_firstname'] . ' ' . $owner['dc_lastname'];

        $nsBlock = current($domainData['PageBlocks']['DomainSettings']['BlockContentForm'])['BlockContentValue'];
        $ns = substr($nsBlock, 0, strpos($nsBlock, ' '));

        $domain = [
            'owner' => $owner,
            'dns_hosting' => $domainData['dom_dnshosting'],
            'ns' => $ns
        ];

        return $domain;
    }

    /**
     * Get all DNS records for a domain.
     *
     * @param $domain
     *
     * @return array
     * @throws \Exception
     */
    public function getDnsRecordsForDomain($domain)
    {
        $this->authenticate();

        $res = $this->client->post('domains/LoadDNSZone/0', ['json' => ['domain' => $domain]]);
        $response = json_decode($res->getBody()->getContents(), true);
        if(!array_key_exists('dnszone', $response)) {
            return [];
        }
        $dnsRecords = ['dnszone']['result']['records'];

        return $dnsRecords;
    }

    /**
     * Update the DNS record for a domain.
     *
     * @param $domain
     * @param $recordName
     * @param $type
     * @param $content
     *
     * @return mixed
     * @throws \Exception
     */
    public function updateDnsRecord($domain, $recordName, $type, $content)
    {
        $this->authenticate();

        $dnsRecords = $this->getDnsRecordsForDomain($domain);
        $dnsRecord = null;

        foreach ($dnsRecords as $dnsRecordCheck) {
            if ($dnsRecordCheck['name'] === $recordName && $dnsRecordCheck['type'] === $type) {
                $dnsRecord = $dnsRecordCheck;
                break;
            }
        }

        if ($dnsRecord === null) {
            throw new \Exception("$type-Record $recordName not found for domain $domain");
        }

        if (in_array($type, ['AAAA']) && filter_var($content, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
            throw new \Exception("$content must be a valid IPV6 address");
        }

        if (in_array($type, ['A', 'CNAME']) && filter_var($content, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
            throw new \Exception("$content must be a valid IPV4 address");
        }

        $updateRes = $this->client->post('domainsave/editDNSRecord/0', [
            'json' => [
                'oldRecord' => [
                    'name'       => $recordName,
                    'type'       => $type,
                    'ttl'        => $dnsRecord['ttl'],
                    'disabled'   => $dnsRecord['disabled'],
                    'content'    => $dnsRecord['content'],
                    'DomainName' => $domain
                ],
                'newRecord' => [
                    'name'     => $recordName,
                    'type'     => $type,
                    'ttl'      => $dnsRecord['ttl'],
                    'disabled' => $dnsRecord['disabled'],
                    'content'  => $content
                ],
                'dom_name'  => $domain
            ]
        ]);

        $response = $updateRes->getBody()->getContents();

        return json_decode($response, true)['success'];
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getUserFullName()
    {
        return $this->userFullName;
    }
}