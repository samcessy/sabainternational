<?php

use Illuminate\Support\Facades\Schedule;

// Scheduled jobs (sitemap regeneration, backup verification, etc.) are
// added here as those features are built.

// saba.md §16.3 — quarterly content audit reminder sent to editors.
Schedule::command('content:freshness-reminder')->quarterly();
