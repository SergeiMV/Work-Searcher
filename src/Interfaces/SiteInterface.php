<?php

namespace WorkSearcher\Interfaces;

use WorkSearcher\Models\User;

interface SiteInterface
{
    public function parsingProcess(User $user);
    public function getNewJobs();
}
