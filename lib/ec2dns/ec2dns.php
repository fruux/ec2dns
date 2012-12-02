<?php

namespace ec2dns;

use ec2dns\ec2;
use ec2dns\ec2host;
from('Hoa')->import('Socket.Server')->import('Dns.~');

/**
 * This class provides the functionality for the ec2dns application
 *
 * @copyright Copyright (C) 2012 fruux GmbH. All rights reserved.
 * @author Dominik Tobschall (http://fruux.com/)
 */
class ec2dns {

    protected $ec2;

    protected $dnsCache = array();

    protected $ttl = 300;

    protected $listener = 'udp://127.0.0.1:54';

    /**
     * Creates the class.
     *
     * @param ec2 $ec2
     * @param string $instanceTag
     */
    public function __construct(ec2 $ec2) {

        $this->ec2 = $ec2;
        $this->dns = new \Hoa\Dns(new \Hoa\Socket\Server($this->listener));

    }
    /**
     * dnsCache Setter
     * @param string $type
     * @param string $domain
     * @param string $ip
     *
     * @return void
     */
    protected function setCache($type, $domain, $ip) {

        $this->dnsCache[md5($type.$domain)] = array('ip' => $ip, 'created' => time());

    }

    /**
     * dnsCaceh Getter
     *
     * @param  string $type
     * @param  string $domain
     * @return false|string
     */
    protected function getCache($type, $domain) {

        if(isset($this->dnsCache[md5($type.$domain)])) {
            if(time() - $this->ttl < $this->dnsCache[md5($type.$domain)]['created']) {
                return $this->dnsCache[md5($type.$domain)];
            } else {
                unset($this->dnsCache[md5($type.$domain)]);
                return false;
            }
        } else {
            return false;
        }

    }

    /**
     * This method resolves tags to IPs via ec2host
     *
     * @param string $tag
     * @return false|string
     */
    protected function resolve($type, $domain) {

        if($dnsCache = $this->getCache($type, $domain)) {
            return $dnsCache['ip'];
        } else {
            $ec2host = new ec2host(clone $this->ec2, $domain);

            if($ec2host->instances) {
                $ip = gethostbyname($ec2host->instances[0]['dnsName']);
                $this->setCache($type, $domain, $ip);

                return $ip;
            } else {

                $this->setCache($type, $domain, false);
                return false;

            }

        }

    }

    /**
     * onQuery callback
     *
     * @param \Hoa\Core\Event\Bucket $bucket
     * @return false|string
     */
    public function onQueryCallback(\Hoa\Core\Event\Bucket $bucket) {

        $data = $bucket->getData();
        //var_dump($data);

        return $this->resolve($data['type'], $data['domain']);

    }

    /**
     * This method starts the ec2dns eventloop
     *
     * @return void
     */
    public function run() {

        $this->dns->on('query', xcallable($this, 'onQueryCallback'));
        $this->dns->run();

    }

}
