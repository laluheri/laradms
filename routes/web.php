<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AdmsController;
use Carbon\Carbon;  

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
|plink -ssh bkdpsdm@simpelgati.lombokutarakab.go.id -pw admin212 -P 2212 -N -L 3307:127.0.0.1:3306
|
*/


function convertTime($dateString){
    $date           = Carbon::parse($dateString);
    $time           = $date->format('H:i:s');

        return $time;
}

Route::get('/', function () {
    $awal  = new DateTime('16:00:00');
    $akhir  = new DateTime('16:30:00');
    $diff  = $awal->diff($akhir);
    // $attendances = DB::connection('mysql_remote')->table('attendances_12')->get();
    // foreach($attendances as $attendance){    
    //     $diff  = $awal->diff($akhir);
    // }

        $checkIn        = strtotime(convertTime('07:45:23'))-strtotime('TODAY');
        $checkOut       = strtotime(convertTime('16:09:46'))-strtotime('TODAY');
        
        $batas_awal     = strtotime(convertTime('08:00:00'))-strtotime('TODAY');
        if($checkIn < $batas_awal) {
            $checkIn = $batas_awal;
        }
        $timeLd         = $checkIn-(strtotime(convertTime('07:30:00'))-strtotime('TODAY'));
        
        $hari = new DateTime('2023-05-08 07:22:52');
        $cekDay = ($hari->format('w') == '5' ? '16:30:00' : '16:00:00');
        $b              = strtotime(convertTime($cekDay))-strtotime('TODAY');
        $timeCp         = $b-$checkOut;
        $batas_akhir    = $timeLd+$b;

        //Lambat Datang
        $ld=0;
        $timeLdSend = '';
        $timeCpSend = '';
        $cp=0;
        if ($checkIn>$batas_awal) {
            $ld=number_format(($checkIn-$batas_awal)/3600,3);
            $awal  = new DateTime('08:00:00');
            $diff  = $awal->diff($checkIn);
            $timeLdSend = $diff->format('%H:%I:%S');
        }

        //Cepet Pulang
        if ($checkOut<$batas_akhir || $b>$checkOut) {
            if ($b>$checkOut) {
                 //kalo cekout lebih cepet
                $cp=number_format($timeCp/3600,3);
            }else{
                $cp=(number_format(($batas_akhir-$checkOut)/3600,3))-$ld;
                
            }
            $akhir  = new DateTime('16:00:00');
            $diff  = $checkOut->diff($akhir);
            $timeCpSend = $diff->format('%H:%I:%S');
        }
        //Jam Kurang
        //hari jumat dikurang 5400
        $hasil            = ($checkOut-$checkIn)-($hari->format('w') == '5' ? 5400 : 3600);
        $jamKerja       = floatval(number_format($hasil/3600, 3));
        $jamKurang=0;
        
        if ($ld || $cp) {
            $jamKurang= $ld+$cp;
        }
            
        if($jamKerja > 7.5){
            $jamKerja = 7.5;
        }
        $jamKurang = 7.5 - $jamKerja;
        
        if($jamKurang < 0) {
            $jamKurang = 0;
        } else if($checkOut<$batas_akhir) {
            $jamKurang= $ld+$cp;
        }
        
       
        $total          = floatval(number_format(($jamKerja/7.5)*100, 3));

    
    // $attendances    = DB::connection('local_db')->table('attendance_view')->get();
    // $currentTime    = $attendances[1]->checktime;
    
    // $out            = strtotime(convertTime($currentTime))-strtotime('TODAY');
    // $in             = strtotime("07:00:41")-strtotime('TODAY');
    // $hasil          = $out-$in-3600;

    // $jamKerja = floatval(number_format($hasil/3600, 3));
    // if($jamKerja > 7.5){
    //     $jamKerja = 7.5;
    // }
    // $jamKurang = 7.5 - $jamKerja;
    // if($jamKurang < 0) {
    //     $jamKurang = 0;
    // }
    // $total = floatval(number_format(($jamKerja/7.5)*100, 3));


    
});

function cekStatus($checkIn, $checkOut) {
    
}
 
 
Route::get('/attendance', [AdmsController::class, 'index']);
Route::get('/insert', [AdmsController::class, 'insertEmpAtt']);

Route::get('/check-connection', function () { 
   
    try {
        $attendances= DB::connection('local_db')->table('attendance_view')->get();

        foreach($attendances as $attendance) {

            $employe_id = ltrim($attendance->employee_id, '0');

            $employee_attendance = [
                'device_id' => null,
                'employee_id' => $employe_id,
                'timestamp' => $attendance->checktime,
            ];
            
            DB::connection('mysql_remote')->table('attendances')->insert($employee_attendance);
        }

        getTimestamp();

        return 'Database connection is successful!';
        
    } catch (\Exception $e) {
        return 'Unable to connect to the database: ' . $e->getMessage();
    }
});

//  function getTimestamp(){

//     $attendances = DB::connection('mysql_remote')->table('attendances_13')->get();
    
//     foreach($attendances as $attendance){    
//         $checkIn        = strtotime(convertTime($attendance->check_in_att))-strtotime('TODAY');
//         $checkOut       = strtotime(convertTime($attendance->check_out_att))-strtotime('TODAY');
        
//         $batas_awal     = strtotime(convertTime('08:00:00'))-strtotime('TODAY');
//         if($checkIn < $batas_awal) {
//             $checkIn = $batas_awal;
//         }
//         $timeLd         = $checkIn-(strtotime(convertTime('07:30:00'))-strtotime('TODAY'));

//         $hari = new DateTime($attendance->check_in_att);
//         $cekDay = ($hari->format('w') == '5' ? '16:30:00' : '16:00:00');
//         $b              = strtotime(convertTime($cekDay))-strtotime('TODAY');
//         $timeCp         = $b-$checkOut;
//         $batas_akhir    = $timeLd+$b;

//         //Lambat Datang
//         $ld=0;
        
//         $cp=0;
//         $timeLdSend = '';
//         $timeCpSend = '';
//         $status='';
//         if ($checkIn>$batas_awal) {
//             $ld=number_format(($checkIn-$batas_awal)/3600,3);
//             $awal  = new DateTime('08:00:00');
//             $akhir = new DateTime($attendance->check_in_att);
//             $diff  = $awal->diff(new DateTime($akhir->format('h:i:s')));
//             $timeLdSend = $diff->format('%H:%I:%S');
//             $status='LD';
//         }

//         //Cepet Pulang
//         if ($checkOut<$batas_akhir || $b>$checkOut) {
//             if ($b>$checkOut) {
//                  //kalo cekout lebih cepet
//                 $cp=number_format($timeCp/3600,3);
//             }else{
//                 $cp=(number_format(($batas_akhir-$checkOut)/3600,3))-$ld;
//             }
//             if($checkIn != $checkOut) {
//                 $akhir  = new DateTime(($hari->format('w') == '5' ? '16:30:00' : '16:00:00'));
//                 $awalTemp = new DateTime($attendance->check_out_att);
//                 $diff  = $akhir->diff($awalTemp);
//                 $timeCpSend = $diff->format('%H:%I:%S');
//                 $status='CP';
               
//             }
           
//         }
//         //Jam Kurang
//         //hari jumat dikurang 5400
//         $hasil            = ($checkOut-$checkIn)-($hari->format('w') == '5' ? 5400 : 3600);
//         $jamKerja       = floatval(number_format($hasil/3600, 3));
//         $jamKurang=0;
        
//         if ($ld || $cp) {
//             $jamKurang= $ld+$cp;
//         }
        
//         if($ld && $cp){
//             $status='LD,CP';
//         }
            
//         if($jamKerja > 7.5){
//             $jamKerja = 7.5;
//         }
//         $jamKurang = 7.5 - $jamKerja;
        
//         if($jamKurang < 0) {
//             $jamKurang = 0;
//         } else if($checkOut<$batas_akhir) {
//             $jamKurang= $ld+$cp;
//         }
        
       
//         $total          = floatval(number_format(($jamKerja/7.5)*100, 3));
        
//         if($attendance->check_in_att == $attendance->check_out_att) {
//             $jamKerja = 0;
//             $jamKurang = 0;
//             $total = 0;
//             $timeCpSend = '';
//             $timeLdSend = '';
//             $status = '';
//             $cp = 0;
//             $ld = 0;
//         }
        
//         DB::connection('mysql_remote')->table('employee_att')->insert([
//             'check_in_att' => $attendance->id_in,
//             'check_out_att' => $attendance->id_out,
//             'employee_id' => $attendance->employee_id,
//             'jam_kerja' => $jamKerja,
//             'jam_kurang' => $jamKurang,
//             'count_cp'=>$cp,
//             'count_ld'=>$ld,
//             'time_ld'=>$timeLdSend,
//             'time_cp'=>$timeCpSend,
//             'total' => $total,
//             'status'=>$status,
//             'date' => date('Y-m-d',strtotime($attendance->check_in_att)),
//         ]);
        
//     }
// }