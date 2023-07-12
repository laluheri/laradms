<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;  

class DemoCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daily:insert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'insert attendance from iclock to sentegdiri';

    /**
     * Execute the console command.
     */
    function convertTime($dateString){
        $date           = Carbon::parse($dateString);
        $time           = $date->format('H:i:s');
    
        return $time;
    }
    public function handle()
    {

        try {

            $attendances= DB::connection('local_db')->table('attendance_view')->get();
    
            foreach($attendances as $attendance) {
    
                $employe_id = ltrim($attendance->employee_id, '0');
    
                $employee_attendance = [
                    'device_id' => null,
                    'employee_id' => $employe_id,
                    'timestamp' => $attendance->checktime,
                ];
                
                DB::table('attendances')->insert($employee_attendance);
            }
    
            getTimestamp();
    
            return 'Database connection is successful!';
            
        } catch (\Exception $e) {
            return 'Unable to connect to the database: ' . $e->getMessage();
        }
        
    }

    function getTimestamp(){

        $attendances = DB::table('attendances_12')->get();
        
        foreach($attendances as $attendance){       
            $checkIn        = strtotime(convertTime($attendance->check_in_att))-strtotime('TODAY');
            $checkOut       = strtotime(convertTime($attendance->check_out_att))-strtotime('TODAY');
           
            $batas_awal     = strtotime(convertTime('08:00:00'))-strtotime('TODAY');
            $timeLd         = $checkIn-(strtotime(convertTime('07:30:00'))-strtotime('TODAY'));
   
            $b              = strtotime(convertTime('16:00:00'))-strtotime('TODAY');
            $timeCp         = $b-$checkOut;
            $batas_akhir    = $timeLd+$b;
    
            //Lambat Datang
            $ld=0;
            
            $cp=0;
            if ($checkIn>$batas_awal) {
                $ld=number_format(($checkIn-$batas_awal)/3600,3);
            }
            //Cepet Pulang
            if ($checkOut<$batas_akhir || $b>$checkOut) {
                if ($b>$checkOut) {
                    $cp=number_format($timeCp/3600,3);
                }else{
                    $cp=(number_format(($batas_akhir-$checkOut)/3600,3))-$ld;
                }
            }
            //Jam Kurang
            //hari jumat dikurang 5400
            $hasil            = ($checkOut-$checkIn)-3600;
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
            }
            
           
            $total          = floatval(number_format(($jamKerja/7.5)*100, 3));
           
            DB::table('employee_att')->insert([
                'check_in_att' => $attendance->id_in,
                'check_out_att' => $attendance->id_out,
                'employee_id' => $attendance->employee_id,
                'jam_kerja' => $jamKerja,
                'jam_kurang' => $jamKurang,
                'count_cp'=>$cp,
                'count_ld'=>$ld,
                // 'time_ld'=>$timeLd,
                // 'time_cp'=>$timeCp,
                'total' => $total,
                'date' => date('Y-m-d',strtotime($attendance->check_in_att)),
                // 'date' => DB::raw('CURDATE()'),
            ]);
            
        }
    }
}