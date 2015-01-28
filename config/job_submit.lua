--[[

 Example lua script demonstrating the SLURM job_submit/lua interface.
 This is only an example, not meant for use in its current form.

 Leave the function names, arguments, local varialbes and setmetatable
 set up logic in each function unchanged. Change only the logic after
 the line containing "*** YOUR LOGIC GOES BELOW ***".

 For use, this script should be copied into a file name "job_submit.lua"
 in the same directory as the SLURM configuration file, slurm.conf.

--]]

function _build_part_table ( part_list )
    local part_rec = {}
    for i in ipairs(part_list) do
        part_rec[i] = { part_rec_ptr=part_list[i] }
        setmetatable (part_rec[i], part_rec_meta)
    end
    return part_rec
end

--########################################################################--
--
--  SLURM job_submit/lua interface:
--
--########################################################################--

function slurm_job_submit ( job_desc, part_list, submit_uid )
    setmetatable (job_desc, job_req_meta)
    local part_rec = _build_part_table (part_list)

--      *** YOUR LOGIC GOES BELOW ***
    --[[
    if job_desc.pn_min_memory == slurm.NO_VAL then                   
        log_user(" ** ERROR ** You _must_ specify required memory")
        return slurm.ERROR
    end --]]
    -- express jobs can also be handled by the normal partition
    if job_desc.partition == "express"
        then
            log_info("slurm_job_submit: job from uid %d, add 'normal'", job_desc.user_id)
            job_desc.partition = "express,normal"
        end

    if job_desc.std_out == nil and job_desc.std_err == nil and job_desc.name ~= nil
        then
            job_desc.std_out = job_desc.name .. "-%j.out"
            job_desc.std_err = job_desc.name .. "-%j.out"
        end

        return 0
end

function slurm_job_modify ( job_desc, job_rec, part_list, modify_uid )
    setmetatable (job_desc, job_req_meta)
    setmetatable (job_rec,  job_rec_meta)
    local part_rec = _build_part_table (part_list)

--      *** YOUR LOGIC GOES BELOW ***

    return 0
end

--########################################################################--
--
--  Initialization code:
--
--  Define functions for logging and accessing slurmctld structures
--
--########################################################################--


log_info = slurm.log_info
log_verbose = slurm.log_verbose
log_debug = slurm.log_debug
log_err = slurm.error
log_user = slurm.log_user

job_rec_meta = {
    __index = function (table, key)
        return _get_job_rec_field(table.job_rec_ptr, key)
    end
}
job_req_meta = {
    __index = function (table, key)
        return _get_job_req_field(table.job_desc_ptr, key)
    end,
    __newindex = function (table, key, value)
        return _set_job_req_field(table.job_desc_ptr, key, value or "")
    end
}
part_rec_meta = {
    __index = function (table, key)
        return _get_part_rec_field(table.part_rec_ptr, key)
    end
}

log_info("initialized")

return slurm.SUCCESS
