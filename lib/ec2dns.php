<?php

namespace ec2dns;

/**
 * This class provides the functionality for the ec2dns application
 *
 * @copyright Copyright (C) 2012-2015 fruux GmbH. All rights reserved.
 * @author Dominik Tobschall (http://fruux.com/)
 */
class ec2dns {

    protected $ec2;

    protected $dnsCache = [];

    protected $ttl = 30;

    protected $tld = "ec2";

    protected $listener = 'udp://127.0.0.1:57005';

    /**
     * Creates the class.
     *
     * @param ec2 $ec2
     * @param string $instanceTag
     */
    function __construct(ec2 $ec2) {

        $this->ec2 = $ec2;
        $this->dns = new \Hoa\Dns\Resolver(new \Hoa\Socket\Server($this->listener));

    }

    /**
     * dnsCache Setter
     *
     * @param string $type
     * @param string $tag
     * @param string $ip
     * @return void
     */
    protected function setCache($type, $tag, $ip) {

        $this->dnsCache[md5($type . $tag)] = ['ip' => $ip, 'created' => time()];

    }

    /**
     * dnsCache Getter
     *
     * @param  string $type
     * @param  string $tag
     * @return false|string
     */
    protected function getCache($type, $tag)
    {
        if (isset($this->dnsCache[md5($type . $tag)])) {
            if (time() - $this->ttl < $this->dnsCache[md5($type . $tag)]['created']) {
                return $this->dnsCache[md5($type . $tag)];
            } else {
                unset($this->dnsCache[md5($type . $tag)]);
                return false;
            }
        } else {
            return false;
        }

    }

    /**
     * Returns the tag.
     *
     * @param string $domain
     * @return string
     */
    protected function stripTld($domain) {

        return preg_replace('/.' . $this->tld . '$/', '', $domain);

    }

    /**
     * This method resolves tags to IPs via ec2host.
     *
     * @param string $type
     * @param string $tag
     * @return false|string
     */
    protected function resolve($type, $tag) {

        if ($dnsCache = $this->getCache($type, $tag)) {
            return $dnsCache['ip'];
        } else {
            $ec2host = new ec2host(clone $this->ec2, $tag);

            if ($ec2host->instances) {
                $ip = gethostbyname($ec2host->instances[0]['dnsName']);
                $this->setCache($type, $tag, $ip);

                return $ip;
            } else {
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
    function onQueryCallback(\Hoa\Core\Event\Bucket $bucket) {

        $data = $bucket->getData();
        return $this->resolve($data['type'], $this->stripTld($data['domain']));

    }

    /**
     * This method starts the ec2dns eventloop
     *
     * @return void
     */
    function run() {

        try {
            $this->dns->on('query', xcallable($this, 'onQueryCallback'));
            $this->dns->run();
        } catch (\Hoa\Socket\Exception $e) {
            throw new \InvalidArgumentException($e->getMessage());
        }

    }

}
