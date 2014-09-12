#!/bin/bash

# This file is very similar to our installation procedure, but I wouldn't
# expect it to work as-is.
# It has a few assumptions:
#   1. User 'munge' is present on all machines
#   2. On the controller there is already a mysql server up and running
#   3. That you are using 14.03.3-2 and want to apply my hacky patches
#
# There are some files not in repository:
#   .
#   |-- SRC
#   |   |-- munge-0.5.11.tar.bz2
#   |   |-- lua-5.1.5.tar.gz
#   |   `-- slurm-14.03.3-2.tar.bz2
#   `-- munge.key
#

echo "You should inspect and modify this file before running it"
exit 0

# Munge
# -----
mkdir --parents /opt/munge/etc/munge
chown --recursive munge /opt/munge/etc/munge
chmod 0700 /opt/munge/etc/munge
chmod 0700 /opt/munge/etc/munge/munge.key
mkdir --parents /var/log/munge; chown --recursive munge /var/log/munge; chmod --recursive 0700 /var/log/munge
mkdir --parents /var/run/munge; chown --recursive munge /var/run/munge; chmod --recursive 0755 /var/run/munge
mkdir --parents /var/lib/munge; chown --recursive munge /var/lib/munge; chmod --recursive 0711 /var/lib/munge

# The key file can be generated anyway you want, taking some random data from
# /dev/urandom is fine.
# It works as a shared secret that you need to know to talk to anything in the
# cluster. This obviously means regular users shouldn't have direct access to
# it.
cp munge.key /opt/munge/etc/munge/munge.key

tar --bzip --extract --file SRC/munge-0.5.11.tar.bz2
cd munge-0.5.11
./configure --prefix=/opt/munge --sysconfdir=/opt/munge/etc --localstatedir=/var
make
make install

cp src/etc/munge.init /etc/rc.d/init.d/munge
chmod +x /etc/rc.d/init.d/munge
rm -f /etc/rc.d/rc3.d/S81munge
ln -s /etc/rc.d/init.d/munge /etc/rc.d/rc3.d/S81munge

cd .. # out of munge-0.5.11/

mkdir -p /opt/slurm/etc
mkdir -p /opt/slurm/lua

# Lua
# ---
export PKG_CONFIG_PATH=$PKG_CONFIG_PATH:/opt/slurm/lua/lib/pkgconfig
tar --gzip --extract --file SRC/lua-5.1.5.tar.gz
cd lua-5.1.5
make linux CFLAGS="-Os -Wall -fPIC"
make install INSTALL_TOP=/opt/slurm/lua
mkdir -p /opt/slurm/lua/lib/pkgconfig
cp etc/lua.pc /opt/slurm/lua/lib/pkgconfig/lua5.1.pc
cd .. # out of lua-5.1.5


# Slurm
# -----
tar --bzip --extract --file SRC/slurm-14.03.3-2.tar.bz2
cd slurm-14.03.3-2

# Before anything else we need to patch the code
for patch in ../patches/*.patch ;
do
    patch -p1 < $patch
done

./configure --prefix=/opt/slurm --sysconfdir=/opt/slurm/etc CFLAGS=-Os
make -j14
make install
cd contribs/lua
make
make install
# We need the perl api to build torque replacements
cd ../perlapi
make
make install
# compile and install replacements for qsub, qstat etc.
cd ../torque
PERL5LIB=/opt/slurm/share/lib64/perl5 make install
cd ../../.. # out of slurm-14.03.3-2/contribs/torque

cp config/slurm.conf /opt/slurm/etc/slurm.conf
cp config/job_submit.lua /opt/slurm/etc/job_submit.lua
cp slurm_ld.conf /etc/ld.so.conf.d/

mkdir --parents /opt/slurm/scripts
cp scripts/slurm-prolog         /opt/slurm/scripts/
cp scripts/slurm-epilog         /opt/slurm/scripts/
cp scripts/slurm-task-prolog    /opt/slurm/scripts/
cp scripts/slurm-remote-prolog  /opt/slurm/scripts/
cp scripts/controller-prolog    /opt/slurm/scripts/

cp replacements/* /opt/slurm/bin

cp init.d/slurm /etc/init.d/slurm
chmod +x /etc/init.d/slurm
ln -s /etc/init.d/slurm /etc/rc.d/rc3.d/S82slurm
ln -s /etc/init.d/slurm /etc/rc.d/rc0.d/K82slurm


cp cgroup.conf /opt/slurm/etc/cgroup.conf
mkdir --parents /opt/slurm/scripts/cgroup
cp slurm-14.03.3-2/etc/cgroup.release_common.example /opt/slurm/scripts/cgroup/release_common
for subsystem in blkio cpuacct cpuset freezer memory; do
    ln -s /opt/slurm/scripts/cgroup/release_common /opt/slurm/scripts/cgroup/release_$subsystem 
done

$SKIP_CONTROLLER="yes"
if [ -z $SKIP_CONTROLLER ]
then
    # The iotuning sets some network parameters that help when submitting a
    # lot of jobs. We don't have exact before/after measurements.
    echo 'DAEMON_ARGS="--num-threads 16"' > /opt/munge/etc/sysconfig/munge
    ./setup-iotuning.sh

    # I haven't included the mysql setup, but you just need to make sure there
    # is a mysql server running, with the same user/pass as specified in the
    # slurmdbd.conf.
    # ./mysql_setup_and_start.sh -u munge -p somepassword

    cp config/slurmdbd.conf /opt/slurm/etc/
    touch /var/run/slurmdbd.pid ; chown munge /var/run/slurmdbd.pid
    # Here you would probably want to add some accounts so there is actually
    # anyone to submit jobs.
    # /opt/slurm/bin/sacctmgr load test_accounts
fi

# Once installed you can start everything with
# /etc/init.d/munge start
# /etc/init.d/slurm start
