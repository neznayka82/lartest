<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class ManagerController extends Controller
{
    public function getall(Request $r){
        return User::all()->toJson();
    }
}
