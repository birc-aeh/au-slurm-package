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
1. A job that asks for 1 hour or less of walltime is automatically added in our
express partition.
2. If no specific stdout/stderr names have been asked for we set it to
"jobname"-"jobid".out in stead of the standard slurm-"jobid".out.

Must be readable by the user that slurmctld runs under.

### slurmdbd.conf
This is the config for the accounting module. Since it has the password and
user for the database it is important that it is not accessible to regular
users.

Must be readable by the user that slurmdbd runs under.

Misc
----
The `slurm_ld.conf` files is put into `/etc/ld.so.conf.d/` to make sure the
binaries can find the libraries they need.
