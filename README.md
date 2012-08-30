# ec2dns

ec2dns is a set of command line tools that makes it easy to display public hostnames of [EC2](http://aws.amazon.com/ec2/) instances and ssh into them via their tag name.

## Usage

### ec2host

#### Get a list of ec2 instances

```
$ ec2host
i-12345678: appserver-1    ec9-99-99-99-99.compute-1.amazonaws.com
i-87654321: appserver-2    ec1-11-11-11-11.compute-1.amazonaws.com
$ 
```

#### Get the hostname of an ec2 instance by its name tag

```
$ ec2host appserver-1
ec9-99-99-99-99.compute-1.amazonaws.com
$ 
```

#### combine ec2host with other commands by using backticks

```
$ mysql --host=`ec2host appserver-1` --user=someUser --password=somePassword someDatabase
Welcome to the MySQL monitor.  Commands end with ; or \g.
Your MySQL connection id is 348
Server version: 5.5.25a Source distribution

Copyright (c) 2000, 2011, Oracle and/or its affiliates. All rights reserved.

Oracle is a registered trademark of Oracle Corporation and/or its
affiliates. Other names may be trademarks of their respective
owners.

Type 'help;' or '\h' for help. Type '\c' to clear the current input statement.

mysql> 
```

### ec2ssh

#### ssh into an instance via its name tag

```
$ ec2ssh appserver-2
ubuntu@ip-1-11-11-11:~$

```

#### ssh into an instance via its name tag and execute a command

```
$ ec2ssh appserver-1 uptime
 13:09:10 up 1 day, 14:23,  0 users,  load average: 0.35, 0.36, 0.35
$ 

$ ec2ssh appserver-1 'uname -a'
 Linux ip-10-140-78-75 3.2.0-23-virtual #36-Ubuntu SMP Tue Apr 10 22:29:03 UTC 2012 x86_64 x86_64 x86_64 GNU/Linux
$ 
```

## Prerequisites

* Obviously an [AWS](http://aws.amazon.com) account and at least one running EC2 instance.
* Correctly set `AWS_ACCESS_KEY_ID` and `AWS_SECRET_ACCESS_KEY` environment variables.
* You need [Composer](http://getcomposer.org) to install the dependencies - you should install it globally, as described [here](http://getcomposer.org/doc/00-intro.md#globally).
* You should have a `~/bin` directory and it should be included in your `PATH` environment variable.

## Installation

* Create the file `~/bin/composer.json` with your favourite text editor and paste the following content (or update your existing `composer.json` accordingly, if you already use this approach for something else).

```
{
    "require" : {

        "fruux/ec2dns" : "dev-master"

    },
    "config" : {
        "bin-dir" : "."
    }
}
```

* Save the file.
* Run `composer install` in your `~/bin` directory.
* Composer will now install `ec2host` and its dependencies.

## Updating

```
cd ~/bin
composer update
```

## Roadmap

* Set a nice prompt with the tag name when sshing into a machine via `ec2ssh appserver-1`.
* A cli tool (`ec2dns`) that updates `/etc/hosts` with the current public hostnames of all ec2 instances.

## Credits
ec2dns is inspired by [ec2-ssh](http://github.com/Instagram/ec2-ssh) and powered by [aws-sdk-for-php](http://github.com/amazonwebservices/aws-sdk-for-php).