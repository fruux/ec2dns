# ec2dns

ec2dns is a command line tool that displays public hostnames of your EC2 instances.
    
## Usage

```
$ ec2host
i-12345678: appserver-1    ec9-99-99-99-99.compute-1.amazonaws.com
i-87654321: appserver-2    ec1-11-11-11-11.compute-1.amazonaws.com

$ ec2host appserver-1
i-12345678: appserver-1    ec9-99-99-99-99.compute-1.amazonaws.com
```

## Installation

```
cd /usr/local
git clone https://github.com/DominikTo/ec2dns.git
cd /ec2dns
./composer.phar install
ln -s /usr/local/ec2dns/ec2host /usr/local/bin/ec2host
```

## Updating

```
cd /usr/local/ec2dns
git pull
```