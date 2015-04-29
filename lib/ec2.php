<?php

namespace ec2dns;

/**
 * This class handles the communication with the ec2 api via the aws-sdk-for-php.
 *
 * @copyright Copyright (C) 2012-2015 fruux GmbH. All rights reserved.
 * @author Dominik Tobschall (http://fruux.com/)
 */
class ec2 {

    protected $awsEC2;

    protected $filters = [];

    protected $instances = [];

    protected $defaultRegion = 'us-east-1';

    /**
     * Creates the class.
     *
     * @param string $awsKey
     * @param string $awsSecret
     * @param string $Aws\Common\Enum\Region
     */
    function __construct($awsKey, $awsSecret, $awsRegion = false) {

        $this->initEC2($awsKey, $awsSecret, $awsRegion);

    }

    /**
     * Sets up the \Aws\Ec2\Ec2Client instance.
     *
     * @param string $awsKey
     * @param string $awsSecret
     * @param string \Aws\Common\Enum\Region
     * @return void
     */
    private function initEC2($awsKey, $awsSecret, $awsRegion = false) {

        if (!empty($awsKey) && !empty($awsSecret)) {
            $this->awsEC2 = \Aws\Ec2\Ec2Client::factory([
                'credentials' => new \Aws\Common\Credentials\Credentials($awsKey, $awsSecret),
                'region'      => empty($awsRegion) ? $this->defaultRegion : $this->getRegionByUrl($awsRegion)
            ]);
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
    private function getRegionByUrl($url) {

        $url = strtolower(parse_url($url, \PHP_URL_HOST));

        $regions = [
            'ec2.us-east-1.amazonaws.com'      => 'us-east-1',
            'ec2.us-west-1.amazonaws.com'      => 'us-west-1',
            'ec2.us-west-2.amazonaws.com'      => 'us-west-2',
            'ec2.eu-west-1.amazonaws.com'      => 'eu-west-1',
            'ec2.eu-central-1.amazonaws.com'   => 'eu-central-1',
            'ec2.ap-northeast-1.amazonaws.com' => 'ap-northeast-1',
            'ec2.ap-southeast-1.amazonaws.com' => 'ap-southeast-1',
            'ec2.ap-southeast-2.amazonaws.com' => 'ap-southeast-2',
            'ec2.sa-east-1.amazonaws.com'      => 'sa-east-1',
            'ec2.cn-north-1.amazonaws.com.cn'  => 'cn-north-1',
            'ec2.us-gov-west-1.amazonaws.com'  => 'us-gov-west-1'
        ];

        if (!isset($regions[$url])) {
            throw new \InvalidArgumentException('The supplied region is unknown. Check your EC2_URL environment variable.');
        }

        if (!in_array($regions[$url], \Aws\Common\Enum\Region::values())) {
            throw new \InvalidArgumentException('The supplied region is known, but not supported by your version of aws/aws-sdk-php.');
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
    function addFilter($name, $values) {

        $this->filters[] = ['Name' => $name, 'Values' => $values];

    }

    /**
     * Makes the actual API request to AWS and stores the returned EC2 instances in the class.
     *
     * @return void
     */
    private function getInstances() {

        try {
            $this->instances = $this->awsEC2->getIterator('describeInstances',
                [
                    'Filters' => $this->filters
                ]
            );
        } catch (\Aws\Ec2\Exception\Ec2Exception $e) {
            if ($e->getStatusCode() === 401) {
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
    function getNext() {

        if (!$this->instances) {
            $this->getInstances();
        }

        $this->instances->next();
        return $this->instances->current();

    }

}
