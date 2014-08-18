The Replacements
================

This is a bunch of program that replace commands we used to have in our old
torque system. Some were specific to our setup and some are more well known
torque commands. If a torque command isn't here we use it straight from slurm
(in the contribs/torque folder).
When installing we just dump all of these in the slurm bin folder, next to
`sbatch`, `squeue` etc.

## js & mj
js is a simplified version of qstat. mj is the same as js except it default to
filtering by your own username.

## je
Gives a list of currently running jobs and the load on the nodes they are
allocated to. Filterable per user, node or jobid.

## gnodes
Gives a graphical overview of the load on the cluster.
Slurm doesn't gather the load data very frequently so it can be a bit out of
date, but still a nice way to see how much machinery is available.

## nodes
Prints an overview of the number of nodes available/occupied per
queue(partition in slurm lingo).
Works off of the output from pbsnodes, with some knowledge of out setup. Might
not be very interesting to anyone else.

## qstat
The only change to qstat is that it couldn't parse -n1 which some of our other
programs expected it to.

## qsub
Our qsub script has a few changes from the one that comes with slurm.
Again it's not clear if these differences are unique to out torque setup or
general, but they are as follows

1. If no `#!`-line is found, we assume `/bin/sh`
2. If no script file is given, we read it from stdin
3. We have a less strict parse of times. 1:0:0 is the same as 1:00:00
4. Interactive jobs puts you on one of the allocated machines

## qwait
Waits for multiple jobs to finish by checking the queue and sleeping.
Used in the original version of qx, not longer necessary.

## qx
Is a tool for users to submit jobs, mostly used by newer users.

## dispatch
Nice little tool for running embarrassingly parallel jobs. Create a file with
one command per line. Now you just need to allocate a bunch of nodes/cores and
`dispatch -r` your commandfile and it will run all of them reasonably well
distributed over all the allocated nodes/cores.

