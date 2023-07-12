<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
class AdmsController extends Controller
{
    public function index (){
        
        $attendances= DB::connection('local_db')->table('attendance_view')->get();
        
        return view('attendance', ['attendances'=>$attendances]);
    }
}