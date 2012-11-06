<?php

namespace ec2dns;

/**
 * This class handles the communication with the ec2 api via the aws-sdk-for-php.
 *
 * @copyright Copyright (C) 2012 fruux GmbH. All rights reserved.
 * @author Dominik Tobschall (http://fruux.com/)
 */
class ec2 {

    protected $awsEC2;

    protected $filters = array();

    protected $instances = array();

    /**
     * Creates the class.
     *
     * @param ec2dns $app
     */
    public function __construct($awsKey, $awsSecret) {

        $this->initEC2($awsKey, $awsSecret);

    }

    /**
     * Sets up the \AmazonEC2 instance.
     *
     * @param string $awsKey
     * @param string $awsSecret
     * @return void
     */
    private function initEC2($awsKey, $awsSecret) {

        if(!empty($awsKey) && !empty($awsSecret)) {

            $this->awsEC2 = new \AmazonEC2(
                array(
                    'key' => $awsKey,
                    'secret' => $awsSecret
                )
            );

        } else {

            throw new \LogicException('AWS Key or Secret are not set.');

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

        $instances = $this->awsEC2->describe_instances(array(
            'Filter' => $this->filters
        ));

        if (!$instances->isOK()) {
            throw new \RuntimeException('Request failed!');
        }

        foreach ($instances->body->reservationSet->item as $instance) {
            array_push($this->instances, $instance);
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
