misc-slurm-stuff
================

The various configs and tools we use on the GenomeDK cluster

Folder overview
---------------

    folder          install location        note
    ------          ----------------        ----
    config/         /opt/slurm/etc/         Our configuration files (see further notes before installing)
    scripts/        /opt/slurm/scripts/     This folder holds the various prolog/epilog scripts
    replacements/   /opt/slurm/bin/         Replacements for most of our old tools
    tools/          /opt/slurm/bin/         New tools to make things nicer for the user
    support-bin/    various                 A few supporting programs

Config files
------------
You need to pay attention to what you are installing on what machines here.
`slurmdbd.conf` contains the user/pass for the database and should _only_ be
installed on the controller machine(s).

### slurm.conf
This is the main config file that is needed on all machines.
Theoretically you could probably get away with a smaller version on compute
nodes and frontends, but the files are compared via hashing by default so just
install identical configs everywhere.

Must be readable by all users.

### job_submit.lua
This script is only needed on the controller, but is not sensitive so you can
install it everywhere if that is easier.
It does two things for our setup:

1. A job that asks for 1 hour or less of walltime is automatically added in
our express partition.
2. If no specific stdout/stderr names have been asked for we set it to
jobname-jobid.out in stead of the standard slurm-jobid.out.

Must be readable by the user that slurmctld runs under.

### slurmdbd.conf
This is the config for the accounting module. Since it has the password and
user for the database it is important that it is not accessible to regular
users.

Must be readable by the user that slurmdbd runs under.

### cgroup.conf
We configure cgroups to contrain cores, memory and to include swap in the
calculations.

Scripts
-------

### slurm-prolog & slurm-epilog
These are the standard prolog and epilog scripts that run before and after a
job, with root permissions.
The default for slurm is to run the epilog on all nodes involved in a job, at
the end of the job -- as expected.
I found the behaviour for the prolog surprising though, it only runs on a node
when the job starts something on the node.
That means that with a script like this:

    #SBATCH -n 32
    echo nothing
    sleep 1000
    srun hostname

The prolog will run immediately on _one_ node, the other nodes will only run
with srun -- leaving 1000 seconds where the user can't ssh in, or can ssh in
but without the node having been setup.
In order to change this we have set `PrologFlags=Alloc` in `slurm.conf`. This
ensures that the prolog is run on all machines as soon as they are allocated to
a job.

The scripts them selves are pretty simple. We create job specific folders, make
sure our audit service is running and call `bash-login-update` to open for ssh
connections from the user.
The epilog then closes for ssh connections from the user (disconnecting them,
        and deleting all their /tmp data).
Then it deletes the job specific folders, and runs a sanity check to make sure
the node is still healthy.

Must be present on all compute-nodes.

### slurm-task-prolog
The task prolog is run as the user before the users script, it sets a few
environment variables for compatibility with the old Torque system.

Must be present on all compute-nodes.

### controller-prolog & slurm-remote-prolog
We don't want a node to take a job and then immediately fail. It should
probably be avoidable by putting a sanity-check in the regular prolog script,
         but we couldn't get it to work so we went for another solution.
When the controller has found a suitable set of nodes to run a job, it calls
the controller-prolog.
The `controller-prlog` script then connects to all the proposed nodes and have
them run a sanity-check (the `slurm-remote-prolog`). If any of the nodes fail,
the proposed set of nodes is discarded and the job goes back in the queue.

The remote prolog must be present on all compute-nodes, the controller prolog
only needs to be on the controller.

Tools
-----
We have only needed one completely new tool so far. `jobinfo` collects the most
useful fields from sacct (and sstat for running jobs) and presents it in a
format that is easier to read and grep.

It takes a very wide format with multiple entries, like this:

       JobID        JobName    Partition  MaxVMSize  MaxVMSizeNode  MaxVMSizeTask  ...
       ------------ ---------- ---------- ---------- -------------- -------------- ...
       219304               94 express,n+                                          ...
       219304.batch      batch               314132K         s01n36              0 ...

And converts it in to something like this:

    Name                : 94
    User                : qianyuxx
    Partition           : express,normal
    Nodes               : s01n36
    ...
    Max Mem used        : 3.54M (s01n36)
    Max Disk Write      : 348.00M (s01n36)
    Max Disk Read       : 348.00M (s01n36)


Misc
----
The `slurm_ld.conf` files is put into `/etc/ld.so.conf.d/` to make sure the
binaries can find the libraries they need.

### cgroups
Enabling cgroups means that when ever a job is started it is allocated a set of
cores and some amount of memory. Every subprocess of the job is also bound to
these constraints. This means that we can have a bad job, pushing the load
average of a machine to 100 with no discernible impact on the other jobs.

The install procedure is to install the cgroup.conf file next to the slurm.conf
file on all compute nodes, and installing the slurm release_common script where
your `CgroupReleaseAgentDir` variable points.
Finally you should create some aliases of the script, like this:

    cd /opt/slurm/scripts/cgroup/
    for subsystem in blkio cpuacct cpuset freezer memory; do
        ln -s release_common release_$subsystem 
    done

