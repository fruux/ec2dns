<?php

namespace ec2dns;

use ec2dns\ec2dns;
use ec2dns\ec2;

/**
 * This class provides the functionality for the ec2host application
 *
 * @copyright Copyright (C) 2012 fruux GmbH. All rights reserved.
 * @author Dominik Tobschall (http://fruux.com/)
 */
class ec2host {

    protected $ec2;

    protected $app;

    protected $instanceTag;

    public $emptyTag = "[No 'Name' tag]";

    public $instances = array();

    /**
     * Creates the class.
     *
     * @param ec2dns $app
     * @param string $instanceTag
     */
    public function __construct(ec2 $ec2, $instanceTag = false) {

        $this->ec2 = $ec2;
        $this->instanceTag = $instanceTag;

        $this->initFilters();
        $this->run();

    }

    /**
     * This method initializes the filters in the instance of ec2dns\ec2\ec2.
     *
     * @return void
     */
    protected function initFilters() {

        $this->ec2->addFilter('instance-state-name', 'running');

        if($this->instanceTag) {
            $this->ec2->addFilter('tag:Name', $this->instanceTag);
        }
    }

     /**
     * This method executes the request via the instance of ec2dns\ec2\ec2
     * and stores the result in the class.
     *
     * @return void
     */
    protected function run() {

        while ($instance = $this->ec2->getNext()) {

            $tag = false;
            $instanceId = false;
            $dnsName = false;

            foreach($instance->instancesSet->item->tagSet->item as $tag) {
                if($tag->key == 'Name' && !empty($tag->value)) {
                    $tag = $tag->value;
                    break;
                } else {
                    $tag = false;
                }
            }

            $instanceId = $instance->instancesSet->item->instanceId;
            $dnsName = $instance->instancesSet->item->dnsName;
            $tag = ( $tag ) ? $tag : $this->emptyTag;

            array_push($this->instances, array("instanceId" => $instanceId, "dnsName" => $dnsName, "tag" => $tag));

        }

    }

}
