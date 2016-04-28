Changelog
=========

2.0.2 (2016-04-28)
------------------

* Fixed: Instances that had no tags at all were not processed correctly, #18.

2.0.1 (2016-03-18)
------------------

* Updated to latest v2 aws/aws-sdk-php, #17.
* Fixed: Default timezone to suppress PHP warnings, #16.

2.0.0 (2015-04-29)
------------------

* Added: Built-in DNS server, #13.
* Fixed: Coding standards using sabre/cs.
* Migrated to aws/aws-sdk-php v2, #12.

1.4.1 (2014-10-14)
------------------

* Fixed: Better exception on auth failure, #10.

1.4.0 (2013-07-08)
------------------

* Added: Support for EC2_URL env var, #3.
* Added: Window name support for tmux, #5 (Thanks, @evert).
* Added: Updated AWS regions.
* Fixed: Instance tag parsing, #4 (Thanks, @sleets).
* Fixed: Failing hostname lookup in ec2ssh, #6 (Thanks, @evert).

1.3.0 (2012-11-28)
------------------

* Added: Interactive shell support for ec2ssh.

1.2.0 (2012-09-12)
------------------

* Added: Updater tool for `/etc/hosts`.
* Improved error and exception handling.

1.1.0 (2012-08-31)
------------------

* Added: ec2scp tool.

1.0.0 (2012-08-30)
------------------

* First release.
