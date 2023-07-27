<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use DateTime;
use DateInterval;

class AdmController extends Controller {

    public function index(){
        $this->insertToAttendance();
    }
    public function insertEmpAtt(){
        $this->getTimestamp();
    }

    function convertTime($dateString){
        $date           = Carbon::parse($dateString);
        $time           = $date->format('H:i:s');
    
        return $time;
    }

    public function insertToAttendance(){
        // $this->getTimestamp();
        try {
            $attendances= DB::connection('local_db')->table('iclock_att_juli')->get();

            foreach($attendances as $attendance) {

                $employe_id = ltrim($attendance->employee_id, '0');

                $employee_attendance = [
                    'device_id' => $attendance->device_id,
                    'opd_id' => $attendance->opd_id,
                    'employee_id' => $employe_id,
                    'timestamp' => $attendance->checktime,
                ];

                DB::connection('mysql_remote')->table('attendances')->insert($employee_attendance);
            }

            //$this->getTimestamp();

            return 'Database connection is successful!';

        } catch (\Exception $e) {
            return 'Unable to connect to the database: ' . $e->getMessage();
        }
    }

    function getTimestamp(){

       $attendances = DB::connection('mysql_remote')->table('attendaces_21')->get();
       foreach ($attendances as $attendance) {
        
        $checkIn = strtotime(convertTime($attendance->check_in_att)) - strtotime('TODAY');
        $checkOut = strtotime(convertTime($attendance->check_out_att)) - strtotime('TODAY');

        $batas_telat = strtotime(convertTime('00:30:00')) - strtotime('TODAY');
        $batas_cekIn = strtotime(convertTime('07:30:00')) - strtotime('TODAY');
        $batas_awal = strtotime(convertTime('08:00:00')) - strtotime('TODAY');
        
        $hari = new DateTime($attendance->check_in_att);
        $ld=0;
        $cp=0;
        $status='';
        $timeLdSend ='00:00:00';
        $timeCpSend = '00:00:00';
        $menitTambahAkhir ='PT00M00S';
        
        
        if ($checkIn < $batas_cekIn) {
            $checkIn = $batas_cekIn;
            $ld = 0;
        } else if($checkIn > $batas_awal) {
            $checkIn = $batas_awal;
            $awal = new DateTime(convertTime($attendance->check_in_att));
            $akhir = new DateTime(convertTime($batas_awal));
            $diff = $awal->diff(new DateTime($akhir->format('h:i:s')));
            $timeLdSend = $diff->format('%H:%I:%S');
            $tempLD = strtotime(convertTime($timeLdSend)) - strtotime('TODAY');
            $ld = number_format($tempLD / ($hari->format('w') == '5' ? '5400' : '3600'), 3);
            $menitTambahAkhir ='PT30M00S';
        } else {
            $checkIn = $checkIn;
            $ld=0;
            $menitAwal = new DateTime(convertTime($checkIn));
            $menitAkhir = new DateTime('07:30:00');
            $diff = $menitAkhir->diff($menitAwal);
            $menitTambahAkhir = $diff->format('PT%IM%SS');
            // $telatKurangDari8 = $diff->format('%H:%I:%S');
        }
       

        $cekDay = ($hari->format('w') == '5' ? '16:30:00' : '16:00:00');
        $time = new DateTime($cekDay);
        $interval = new DateInterval($menitTambahAkhir);
        $time->add($interval);
        $tambahMenitKeCheckday = $time->format('H:i:s');


        if ($checkOut < (strtotime($tambahMenitKeCheckday)-strtotime('TODAY'))) {
            $menitAwal = new DateTime($tambahMenitKeCheckday);
            $menitAkhir = new DateTime(convertTime($checkOut));
            $diff = $menitAkhir->diff($menitAwal);
            $timeCpSend = $diff->format('%H:%I:%S');
            $tempCp = strtotime(convertTime($timeCpSend)) - strtotime('TODAY');
            $cp = number_format($tempCp / ($hari->format('w') == '5' ? '5400' : '3600'), 3);
        }
       
        if (($ld>0) && ($cp > 0)) {
            $status='LD,CP';
        }else if(($ld >0) && ($cp==0)){
            $status='LD';
        }else if(($ld==0) && ($cp > 0)){
            $status='CP';
        }else{
            $status='';
        }

        $jamKerja = 7.5;
        $jamKurang = $ld+$cp;
        $jamKerja=$jamKerja - $jamKurang;
        $total = floatval(number_format(($jamKerja / 7.5) * 100, 3));;


        // dd('timeLdSend: '.$timeLdSend,'LD: '.$ld,'timeCpSend:'.$timeCpSend,'CP: '.$cp,'status : '.$status,'Jam Kerja :'.$jamKerja,'Jam Kurang :'.$jamKurang,'Total :'.$total,'menitTambahAkhir: '.$menitTambahAkhir,'tambahMenitKeCheckday: '.$tambahMenitKeCheckday,);
        
        $batas=strtotime(convertTime('12:00:01')) - strtotime('TODAY');
        
        $id_in=null;
        $id_out=null;

        if (($checkIn <= $batas) && ($checkOut <= $batas)) {
            $id_in = $attendance->id_in;
            $id_out = null;

            $jamKerja = 6.0;
            $jamKurang = 1.5;
            $total = 0;
            $timeCpSend = '';
            $timeLdSend = '';
            $status = 'TAP';
            $cp = 0;
            $ld = 0;

        }else if($checkIn > $batas){
            $id_out = $attendance->id_out;
            $id_in = null;

            $jamKerja = 6.0;
            $jamKurang = 1.5;
            $total = 0;
            $timeCpSend = '';
            $timeLdSend = '';
            $status = 'TAD';
            $cp = 0;
            $ld = 0;
        }else{
            $id_in = $attendance->id_in;
            $id_out = $attendance->id_out;
        }

        // if ($attendance->check_in_att == $attendance->check_out_att) {
        //     $jamKerja = 0;
        //     $jamKurang = 0;
        //     $total = 0;
        //     $timeCpSend = '';
        //     $timeLdSend = '';
        //     $status = 'TAP';
        //     $cp = 0;
        //     $ld = 0;
        // }
        
        DB::connection('mysql_remote')->table('employee_att')->insert([
            'check_in_att' => $id_in ,
            'check_out_att' => $id_out,
            'employee_id' => $attendance->employee_id,
            'jam_kerja' => $jamKerja,
            'jam_kurang' => $jamKurang,
            'count_cp' => $cp,
            'count_ld' => $ld,
            'time_ld' => $timeLdSend,
            'time_cp' => $timeCpSend,
            'total' => $total,
            'status' => $status,
            'opd_id' => $attendance->opd_id,
            'date' => date('Y-m-d', strtotime($attendance->check_in_att)),
        ]);
       }
    }
}