<?php

namespace ec2dns;

/**
 * This class provides the functionality for the ec2host application
 *
 * @copyright Copyright (c) fruux GmbH. All rights reserved.
 * @author Dominik Tobschall (http://fruux.com/)
 */
class ec2host {

    protected $ec2;

    protected $instanceTag;

    public $emptyTag = "[No 'Name' tag]";

    public $instances = [];

    /**
     * Creates the class.
     *
     * @param ec2 $ec2
     * @param string $instanceTag
     */
    function __construct(ec2 $ec2, $instanceTag = false) {

        $this->ec2 = $ec2;
        $this->instanceTag = $instanceTag;

        $this->initFilters();
        $this->run();

    }

    /**
     * This method initializes the filters in the instance of ec2dns\ec2.
     *
     * @return void
     */
    protected function initFilters() {

        $this->ec2->addFilter('instance-state-name', ['running']);

        if ($this->instanceTag) {
            $this->ec2->addFilter('tag:Name', [$this->instanceTag]);
        }

    }

    /**
     * This method executes the request via the instance of ec2dns\ec2
     * and stores the result in the class.
     *
     * @return void
     */
    protected function run() {

        while ($instance = $this->ec2->getNext()) {

            $tag = false;
            $instanceId = false;
            $dnsName = false;

            if (isset($instance['Tags'])) {

                foreach ($instance['Tags'] as $tag) {
                    if ($tag['Key'] == 'Name' && !empty($tag['Value'])) {
                        $tag = $tag['Value'];
                        break;
                    } else {
                        $tag = false;
                    }
                }

            }

            $instanceId = $instance['InstanceId'];
            $dnsName = $instance['PublicDnsName'];
            $tag = ($tag) ? $tag : $this->emptyTag;

            $this->instances[] = ["instanceId" => $instanceId, "dnsName" => $dnsName, "tag" => $tag];

        }

    }

}
