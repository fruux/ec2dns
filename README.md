# ec2dns

ec2dns is a set of command line tools that makes it easy to resolve public hostnames of [EC2](http://aws.amazon.com/ec2/) instances and ssh into them via their tag name.

## Usage of ec2dns

ec2dns provides DNS resolution of ec2 tag names under the ".ec2" TLD. If a servers tag name would be `appserver-1`, it could be resolved as `appserver-1.ec2`.

In essence ec2dns allows working with ec2 tag names like with normal domain names as shown in the following examples.

### ssh

```
ssh ubuntu@appserver-2.ec2
ubuntu@ip-1-11-11-11:~$
```

### scp

```
$ scp ubuntu@appserver-1.ec2:/etc/nginx/nginx.conf .
nginx.conf                                                                           100%  221     0.2KB/s   00:00
$
```

### mysql

```
$ mysql --host=appserver-1.ec2 --user=someUser --password=somePassword someDatabase
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

## Legacy tools

ec2dns also includes wrappers around ssh and scp (if you can't set up or don't want to use the DNS feature on your machine), as well as a tool to lookup hostnames and a tool to update your /etc/hosts file.

You won't need these legacy tools if you have set up the DNS feature as show in the installation instructions below.

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
$ ec2ssh ubuntu@appserver-2
ubuntu@ip-1-11-11-11:~$
```

#### ssh into an instance via its name tag and execute commands

```
$ ec2ssh appserver-1 uptime
 13:09:10 up 1 day, 14:23,  0 users,  load average: 0.35, 0.36, 0.35
$

$ ec2ssh appserver-1 'uname -a'
 Linux ip-10-140-78-75 3.2.0-23-virtual #36-Ubuntu SMP Tue Apr 10 22:29:03 UTC 2012 x86_64 x86_64 x86_64 GNU/Linux
$
```

### ec2scp

#### copy a file from an ec2 instance onto your machine

```
$ ec2scp ubuntu@appserver-1:/etc/nginx/nginx.conf .
nginx.conf                                                                           100%  221     0.2KB/s   00:00
$
```

### ec2updatehostsfile

#### update /etc/hosts with your ec2 instances

```
$ sudo -E ec2updatehostsfile
Updated/Added 2 hosts.
```

Your machine is now able to resolve your ec2 instances by tags directly, so for example the following will just work

```
$ ping appserver-1
```

## Prerequisites

* Obviously an [AWS](http://aws.amazon.com) account and at least one running EC2 instance.
* Correctly set `AWS_ACCESS_KEY_ID` and `AWS_SECRET_ACCESS_KEY` environment variables.
* You need [Composer](http://getcomposer.org) to install the dependencies - you should install it globally, as described [here](http://getcomposer.org/doc/00-intro.md#globally).
* You should have a `~/bin` directory and it should be included in your `PATH` environment variable.

## Installation

### Basic

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

* Run `composer install` in your `~/bin` directory.
* Composer will now install `ec2dns` and its dependencies.

### DNS feature

These instructions are for OS X.

* Add the configuration for the dns resolver by creating the file `/etc/resolver/ec2` and pasting the following content.

```
nameserver 127.0.0.1
port 57005
```

* Create the LaunchAgent configuration that starts the DNS server by creating the file `~/Library/LaunchAgents/com.fruux.ec2dns.plist` and pasting the following content (you have to adjust the path for your user directory).

```
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple Computer//DTD PLIST 1.0//EN"
"http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
  <dict>
    <key>Label</key>
    <string>com.fruux.ec2dns</string>
    <key>Program</key>
    <string>/Users/YourUser/bin/ec2dns</string>
    <key>RunAtLoad</key>
    <true/>
  </dict>
</plist>
```

* Finally activate the LaunchAgent config by pasting the following into the terminal.

```
launchctl load ~/Library/LaunchAgents/com.fruux.ec2dns.plist
```

## Updating

```
cd ~/bin
composer update

launchctl unload ~/Library/LaunchAgents/com.fruux.ec2dns.plist
launchctl load ~/Library/LaunchAgents/com.fruux.ec2dns.plist
```

## Contributing

Please submit all pull requests against the master branch. Code accompanied with phpunit tests is highly appreciated. Thanks!

## Acknowledgements

ec2dns is inspired by [ec2-ssh](http://github.com/Instagram/ec2-ssh) and powered by [aws-sdk-for-php](http://github.com/amazonwebservices/aws-sdk-for-php).

## Copyright and license

Copyright (C) 2013 [fruux GmbH](http://fruux.com). All rights reserved.

*fruux is a free service that takes care of your contacts, calendars and more so you don't have to (powered by CardDAV and CalDAV).*

Check the [license](https://github.com/fruux/ec2dns/blob/master/LICENSE).