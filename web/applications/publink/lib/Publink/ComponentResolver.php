<?php
namespace Publink;

class ComponentResolver
{
    public function getFcAndProj($key)
    {
        # once we change the UDI format, this will break.  At this point,
        # this will have to be replaced by a database/persistence lookup
        # for these details.
        list($fc, $proj, $task) = preg_split('/\./', $key);
        return array($fc, $proj);
    }
}
