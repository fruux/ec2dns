<?php

namespace ec2dns;

/**
 * This class handles the communication with the ec2 api via the aws-sdk-for-php.
 *
 * @copyright Copyright (C) 2012-2015 fruux GmbH. All rights reserved.
 * @author Dominik Tobschall (http://fruux.com/)
 */
class ec2
{
    protected $awsEC2;

    protected $filters = array();

    protected $instances = array();

    protected $defaultRegion = \Aws\Common\Enum\Region::US_EAST_1;

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
     * Sets up the \Aws\Ec2\Ec2Client instance.
     *
     * @param string $awsKey
     * @param string $awsSecret
     * @return void
     */
    private function initEC2($awsKey, $awsSecret, $region = false)
    {
        if (!empty($awsKey) && !empty($awsSecret)) {

            $this->awsEC2 = \Aws\Ec2\Ec2Client::factory(array(
                'credentials' => array(
                    'key' => $awsKey,
                    'secret' => $awsSecret,
                ),
                'region' => empty($region) ? $this->defaultRegion : $this->getRegionByUrl($region)
            ));

        } else {

            throw new \LogicException('AWS Key or Secret are not set.');

        }

    }

    /**
     * Returns the region constants defined in the \Aws\Common\Enum\Region class
     * for endpoint urls
     *
     * @param string $url
     * @return constant
     */
    private function getRegionByUrl($url)
    {
        $url = parse_url($url, \PHP_URL_HOST);

        $regions = array(
            'ec2.us-east-1.amazonaws.com' => \Aws\Common\Enum\Region::US_EAST_1, // us-east-1
            'ec2.us-west-1.amazonaws.com' => \Aws\Common\Enum\Region::US_WEST_1, // us-west-1
            'ec2.us-west-2.amazonaws.com' => \Aws\Common\Enum\Region::US_WEST_2, // us-west-2
            'ec2.eu-west-1.amazonaws.com' => \Aws\Common\Enum\Region::EU_WEST_1, // eu-west-1
            'ec2.eu-central-1.amazonaws.com' => \Aws\Common\Enum\Region::EU_CENTRAL_1, // eu-central-1
            'ec2.ap-northeast-1.amazonaws.com' => \Aws\Common\Enum\Region::AP_NORTHEAST_1, // ap-northeast-1
            'ec2.ap-southeast-1.amazonaws.com' => \Aws\Common\Enum\Region::AP_SOUTHEAST_1, // ap-southeast-1
            'ec2.ap-southeast-2.amazonaws.com' => \Aws\Common\Enum\Region::AP_SOUTHEAST_2, // ap-southeast-2
            'ec2.sa-east-1.amazonaws.com' => \Aws\Common\Enum\Region::SA_EAST_1, // sa-east-1
            'ec2.cn-north-1.amazonaws.com.cn' => \Aws\Common\Enum\Region::CN_NORTH_1, // cn-north-1
            'ec2.us-gov-west-1.amazonaws.com' => \Aws\Common\Enum\Region::US_GOV_WEST_1 // us-gov-west-1
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
     * @param array $values
     * @return void
     */
    public function addFilter($name, $values)
    {
        array_push($this->filters, array('Name' => $name, 'Values' => $values));
    }

    /**
     * Makes the actual API request to AWS and stores the returned EC2 instances in the class.
     *
     * @return void
     */
    private function getInstances()
    {
        try {

            $this->instances = $this->awsEC2->getIterator('describeInstances',
                array(
                    'Filters' => $this->filters
                )
            );

        } catch (\Aws\Ec2\Exception\Ec2Exception $e) {

            if($e->getStatusCode() === 401) {
                throw new \RuntimeException('AWS was not able to validate the provided access credentials');
            } else {
                throw new \RuntimeException('Request failed!');
            }

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
        }

        $this->instances->next();
        return $this->instances->current();
    }
}
