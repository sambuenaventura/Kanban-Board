<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class NotificationController extends Controller
{
    public function sendEmail() 
    {
        $name = "Funny coder";

        Mail::to('email@gmail.com')->send(new WelcomeMail($name));
    }
}
