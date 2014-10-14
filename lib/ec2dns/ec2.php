<?php

namespace ec2dns;

/**
 * This class handles the communication with the ec2 api via the aws-sdk-for-php.
 *
 * @copyright Copyright (C) 2012 fruux GmbH. All rights reserved.
 * @author Dominik Tobschall (http://fruux.com/)
 */
class ec2
{
    protected $awsEC2;

    protected $filters = array();

    protected $instances = array();

    protected $defaultRegion = \AmazonEC2::REGION_US_E1;

    /**
     * Creates the class.
     *
     * @param ec2dns $app
     */
    public function __construct($awsKey, $awsSecret, $region = false)
    {
        $this->initEC2($awsKey, $awsSecret, $region);

    }

    /**
     * Sets up the \AmazonEC2 instance.
     *
     * @param string $awsKey
     * @param string $awsSecret
     * @return void
     */
    private function initEC2($awsKey, $awsSecret, $region = false)
    {
        if (!empty($awsKey) && !empty($awsSecret)) {

            $this->awsEC2 = new \AmazonEC2(
                array(
                    'key' => $awsKey,
                    'secret' => $awsSecret
                )
            );

            if (empty($region)) {
                $this->awsEC2->set_region($this->defaultRegion);
            } else {
                $this->awsEC2->set_region($this->getRegionByUrl($region));
            }

        } else {

            throw new \LogicException('AWS Key or Secret are not set.');

        }

    }

    /**
     * Returns the region constants defined in the \AmazonEC2 class
     * for endpoint urls
     *
     * @param string $url
     * @return constant
     */
    private function getRegionByUrl($url)
    {
        $url = parse_url($url, \PHP_URL_HOST);

         $regions = array(
            'ec2.us-east-1.amazonaws.com' => \AmazonEC2::REGION_US_E1, // us-east-1
            'ec2.us-west-1.amazonaws.com' => \AmazonEC2::REGION_US_W1, // us-west-1
            'ec2.us-west-2.amazonaws.com' => \AmazonEC2::REGION_US_W2, // us-west-2
            'ec2.eu-west-1.amazonaws.com' => \AmazonEC2::REGION_EU_W1, // eu-west-1
            'ec2.ap-northeast-1.amazonaws.com' => \AmazonEC2::REGION_APAC_NE1, // ap-northeast-1
            'ec2.ap-southeast-1.amazonaws.com' => \AmazonEC2::REGION_APAC_SE1, // ap-southeast-1
            'ec2.ap-southeast-2.amazonaws.com' => \AmazonEC2::REGION_APAC_SE2, // ap-southeast-2
            'ec2.sa-east-1.amazonaws.com' => \AmazonEC2::REGION_SA_E1, // sa-east-1
            'ec2.us-gov-west-1.amazonaws.com' => \AmazonEC2::REGION_US_GOV1 // us-gov-west-1
        );

        if (!isset($regions[strtolower($url)])) {
            throw new \InvalidArgumentException('The supplied region is unknown. Check your EC2_URL environment variable.');
        }

        return $regions[strtolower($url)];

    }

    /**
     * Adds a filter rule.
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    public function addFilter($name, $value)
    {
        array_push($this->filters, array('Name' => $name, 'Value' => $value));
    }

    /**
     * Makes the actual API request to AWS and stores the returned EC2 instances in the class.
     *
     * @return void
     */
    private function getInstances()
    {
        $instances = $this->awsEC2->describe_instances(
            array(
                'Filter' => $this->filters
            )
        );

        if (!$instances->isOK()) {

            if($instances->status === 401) {
                throw new \RuntimeException('AWS was not able to validate the provided access credentials');
            } else {
                throw new \RuntimeException('Request failed!');
            }

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
    public function getNext()
    {
        if (!$this->instances) {
            $this->getInstances();
            reset($this->instances);
        }

        $return = each($this->instances);

        if ($return['value']) {
            return $return['value'];
        } else {
            return false;
        }

    }
}
