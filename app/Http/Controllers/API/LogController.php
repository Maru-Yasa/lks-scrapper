<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Log;
use Illuminate\Http\Request;

class LogController extends Controller
{
    /*
        ##################################################
        # Log's uilities helper
        ##################################################    
    */

    static function makeInfo($config_id, $msg)
    {
        Log::create([
            "msg" => "[INFO] $msg",
            "type" => "info",
            "config_id" => $config_id
        ]);      
    }

    static function makeError($config_id, $msg)
    {
        Log::create([
            "msg" => "[ERROR] $msg",
            "type" => "error",
            "config_id" => $config_id
        ]);     
    }

}
