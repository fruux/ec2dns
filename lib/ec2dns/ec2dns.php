<?php

namespace ec2dns;

/**
 * This is the main application class.
 * It is responsible for configuring the environment.
 *
 * @copyright Copyright (C) 2012 Dominik Tobschall. All rights reserved.
 * @author Dominik Tobschall (http://github.com/DominikTo/)
 */
class ec2dns {

    public $aws_key;

    public $aws_secret;

    /**
     * This method stores the credentials in the class.
     *
     * @param string $aws_key
     * @param string $aws_secret
     * @return void
     */
    public function setCredentials($aws_key, $aws_secret) {

        $this->aws_key = $aws_key;
        $this->aws_secret = $aws_secret;

    }

}