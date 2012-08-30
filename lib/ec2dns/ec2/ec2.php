<?php

namespace ec2dns\ec2;

/**
 * This class handles the communication with the ec2 api via the aws-sdk-for-php.
 *
 * @package ec2dns
 * @subpackage ec2
 * @copyright Copyright (C) 2012 Dominik Tobschall. All rights reserved.
 * @author Dominik Tobschall (http://github.com/DominikTo/)
 */
class ec2 {

    protected $aws_ec2;

    protected $filters = array();

    protected $instances = array();

    protected $emptyTag = "[No 'Name' tag]";

    /**
     * Creates the class.
     *
     * @param ec2dns $app
     */
    public function __construct($app) {

        $this->initEC2($app->aws_key, $app->aws_secret);
        
    }

    /**
     * Sets up the \AmazonEC2 instance.
     *
     * @param string $aws_key
     * @param string $aws_secret
     * @return void
     */
    private function initEC2($aws_key, $aws_secret) {

        if(!empty($aws_key) && !empty($aws_secret)) {

            $this->aws_ec2 = new \AmazonEC2(
                array(
                    'key' => $aws_key,
                    'secret' => $aws_secret
                )
            );

        } else {

            throw new LogicException('AWS Key or Secret are not set.');

        }

    }

    /**
     * Adds a filter rule.
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    public function addFilter($name, $value) {

        array_push($this->filters, array('Name' => $name, 'Value' => $value));

    }

    /**
     * Makes the actual API request to AWS and stores the returned EC2 instances in the class.
     *
     * @return void
     */
    private function getInstances() {

        $instances = $this->aws_ec2->describe_instances(array(
            'Filter' => $this->filters
        ));

        if (!$instances->isOK()) {
            throw new RuntimeException('Request failed!');
        }

        foreach ($instances->body->reservationSet->item as $instance) {
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

    /**
     * This method returns the found instances.
     *
     * @return array $instance
     */
    public function getNext() {

        if(!$this->instances) {
            $this->getInstances();
            reset($this->instances);
        }

        $return = each($this->instances);

        if($return['value']) {
            return $return['value'];
        } else {
            return false;
        }

    }

}