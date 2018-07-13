<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Yaquawa\Laravel\EmailReset\ResetEmail;

class ResetEmailController extends Controller
{
    use ResetEmail;
}