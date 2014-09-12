#!/bin/sh
########################################################################
# Create /etc/init.d/iotuning
########################################################################
echo "Tuning IO settings..."
cat <<EOF > /etc/rc.d/init.d/iotuning
#!/bin/bash
PATH=/sbin:/usr/sbin:\$PATH

# description: Tuning IO settings

# source function library
. /etc/rc.d/init.d/functions

case "\$1" in
       start) echo -n $"Tuning IO settings: "
               sysctl net.ipv4.tcp_fin_timeout=5 && success || failure
               ifconfig eth0 txqueuelen 4096 && success || failure
               echo 4096 > /proc/sys/net/ipv4/tcp_max_syn_backlog && success || failure
               echo 1024 > /proc/sys/net/core/somaxconn && success || failure
              echo
              ;;

      status)  
               sysctl net.ipv4.tcp_fin_timeout
               ifconfig eth0
               echo -n "/proc/sys/net/ipv4/tcp_max_syn_backlog: "
               cat /proc/sys/net/ipv4/tcp_max_syn_backlog
               echo -n "/proc/sys/net/core/somaxconn: "
               cat /proc/sys/net/core/somaxconn
              ;;

        stop) echo -n $"Resetting Tuned IO settings: "
               sysctl net.ipv4.tcp_fin_timeout=60 && success || failure
               ifconfig eth0 txqueuelen 1024 && success || failure
               echo 1024 > /proc/sys/net/ipv4/tcp_max_syn_backlog && success || failure
               echo 128 > /proc/sys/net/core/somaxconn && success || failure
              echo
              ;;

           *) echo "Usage: \$0 [start|status|stop]"
              ;;
esac
#
EOF

chmod 755 /etc/rc.d/init.d/iotuning
chown root:root /etc/rc.d/init.d/iotuning

rm -f /etc/rc.d/rc3.d/S80iotuning /etc/rc.d/rc5.d/S80iotuning
ln -s /etc/rc.d/init.d/iotuning /etc/rc.d/rc3.d/S80iotuning
ln -s /etc/rc.d/init.d/iotuning /etc/rc.d/rc5.d/S80iotuning

# start
/etc/init.d/iotuning start
