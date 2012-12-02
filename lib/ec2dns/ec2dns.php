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
     * This method resolves tags to IPs via ec2host
     *
     * @param string $tag
     * @return false|string
     */
    protected function resolve($tag) {

        $ec2host = new ec2host(clone $this->ec2, $tag);

        if($ec2host->instances) {
            return gethostbyname($ec2host->instances[0]['dnsName']);
        };

        return false;

    }

    /**
     * onQuery callback
     *
     * @param \Hoa\Core\Event\Bucket $bucket
     * @return false|string
     */
    public function onQueryCallback(\Hoa\Core\Event\Bucket $bucket) {

        $data = $bucket->getData();
        return $this->resolve($data['domain']);

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
