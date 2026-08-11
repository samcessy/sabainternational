<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class VolunteerPageController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Volunteer');
    }
}
