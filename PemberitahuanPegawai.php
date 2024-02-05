<?php

namespace App\Console\Commands;

use App\Models\RiwayatKGB;
use App\Models\RiwayatPangkat;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
class PemberitahuanPegawai extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pemberitahuan:pegawai';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pemberitahuan bulanan pegawai apakah ada kenaikan KGB dan Pangkat pada setiap bulan';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dataKGB     = RiwayatKGB::with(['pegawai','golongan'])->where('status',0)->orderBy('id_riwayat_kgb','desc')->get();
        $users       = User::where('role',2)->get();
        $datakenaikanpangkat = RiwayatPangkat::with(['pegawai'])->where('status',0)->orderBy('id_riwayat_pangkat','desc')->get();    
        $dkgb        = []; 
        $datapangkat = [];
        //jika ada data kgb yang masih aktif
        if ($dataKGB->count() > 0) {
            foreach ($dataKGB as $key => $value) {
                if ($value->pegawai->status == 0) {
                    $akhir =strtotime(now());
                    $awal = strtotime($value->batas_berlaku); 
                    $selisih =floor(($awal-$akhir) / (60 * 60 * 24 * 30));
                    //jika masa aktif 2 bulan lagi maka kirim email ke operator ada pegawai kgb yang mau habis
                    if ($selisih <= 2 && $selisih >= 0) {
                    $dkgb[] = $value->pegawai->nama_pegawai;
                    }
                }
            }

                if (count($dkgb) > 0) {
                //kirim email pemberitahuan ke operator
                        foreach ($users as $user) {
                            Mail::raw('Pada bulan ini ADA pegawai yang Kenaikan Gaji Berkali (KGB) harus segera diurus. Silahkan cek ke sistem: '.config('app.url'),function($message) use($user){
                                $message->to($user->email);
                                $message->subject('Pada bulan ini ADA pegawai yang Kenaikan Gaji Berkali (KGB) harus segera diurus. Silahkan cek ke sistem');
                            });
                        }
                }else{
                //kirim email pemberitahuan ke operator
                foreach ($users as $user) {
                        Mail::raw('Pada bulan ini TIDAK ADA pegawai yang Kenaikan Gaji Berkali (KGB) harus segera diurus.',function($message) use($user){
                            $message->to($user->email);
                            $message->subject('Pada bulan ini TIDAK ADA pegawai yang Kenaikan Gaji Berkali (KGB) harus segera diurus.');
                        });
                    } 
                }
            
        }
        //----------------------pangkat golongan--------------
        if ($datakenaikanpangkat->count() > 0) {
            foreach ($datakenaikanpangkat as $key => $value) {
                if ($value->pegawai->status == 0) {
                    $akhir =strtotime(now());
                    $awal = strtotime($value->batas_berlaku); 
                    $selisih =floor(($awal-$akhir) / (60 * 60 * 24 * 30));
                    //jika masa aktif 2 bulan lagi maka kirim email ke operator ada pegawai pangkat yang mau habis
                    if ($selisih <= 4 && $selisih >= 0) {
                        $datapangkat[] = $value->pegawai->nama_pegawai;
                    }
                }
            }
            if (count($datapangkat) > 0) {
                //kirim email pemberitahuan ke operator
                     foreach ($users as $user) {
                         Mail::raw('Pada bulan ini ADA pegawai yang Pangkat Golongan harus segera diurus. Silahkan cek ke sistem: '.config('app.url'),function($message) use($user){
                             $message->to($user->email);
                             $message->subject('Pada bulan ini ADA pegawai yang Pangkat Golongan harus segera diurus. Silahkan cek ke sistem');
                         });
                     }
             }else{
                //kirim email pemberitahuan ke operator
                foreach ($users as $user) {
                     Mail::raw('Pada bulan ini TIDAK ADA pegawai yang Pangkat Golongan harus segera diurus.',function($message) use($user){
                         $message->to($user->email);
                         $message->subject('Pada bulan ini TIDAK ADA pegawai yang Pangkat Golongan harus segera diurus.');
                     });
                 } 
             }
        }
        $this->info('Pengiriman email berhasil');
        
    }
}
