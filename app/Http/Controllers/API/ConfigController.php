<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use KubAT\PhpSimple\HtmlDomParser;

use App\Models\Config;
use App\Models\Book;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ItemNotFoundException;

class ConfigController extends Controller
{

    /*
        ##################################################
        # common config utilities
        ##################################################    
    */

    public function addDijalankan($id)
    {
        $config = Config::all()->where('id',$id)->firstOrFail();
        $config->jumlah_dijalankan += 1;
        $config->save();
    }

    /*
        ##################################################
        # Scrapping utilities
        ##################################################    
    */

    private function getPrice(String $judul)
    {
        $judul = explode(" ",$judul);
        $judul = implode("%20",$judul);
        $url = "https://play.google.com/store/search?q=$judul&c=books&hl=in";
        // dd($url);
        $html = file_get_contents($url);
        $dom = HtmlDomParser::str_get_html($html);
        $dom = $dom->find('.ImZGtf',0);
        $price = $dom->find('.VfPpfd',0)->plaintext;
        $price = explode(',',$price)[0];
        return preg_replace("/[^0-9]/", "", $price );
    }

    public function scrap($config)
    {

        $url = $config->link."&hl=in&gl=in";
        $html = file_get_contents($url);
        $dom = HtmlDomParser::str_get_html($html);

        $judul = $dom->find('h1[itemprop=name]',0)->plaintext;
        $img = $dom->find('img[itemprop=image]',0)->src;
        $infoDom = $dom->find('.jXxUjc');
        $penerbit = $infoDom[0]->find('.htlgb',0)->plaintext;
        $penjual = $infoDom[1]->find('.htlgb',0)->plaintext;
        $tanggalTerbit = $infoDom[2]->find('.htlgb',0)->plaintext;
        $jumlahHalaman = $infoDom[3]->find('.htlgb',0)->plaintext;
        $kompatibilitas = $infoDom[6]->find('.htlgb a',0)->plaintext;
        $bahasa = $infoDom[7]->find('.htlgb',0)->plaintext;
        $genre_raw = $infoDom[8]->find('.htlgb',0)->plaintext;
        $genre = explode('/',$genre_raw);
        $rating = (int) $dom->find('.BHMmbe',0)->plaintext;
        $jumlahPemberiRating = (int) $dom->find('.ddprqc span',0)->plaintext;
        $harga = $this->getPrice($judul);
        $deskripsi = $dom->find('div[itemprop=description]',0)->find('span',0)->plaintext;

        $book = [
            "judul" => $judul,
            "img"   => $img,
            "penerbit" => $penerbit,
            "penjual" => $penjual,
            "tanggal_terbit" => $tanggalTerbit,
            "jumlah_halaman" => $jumlahHalaman,
            "kompatibilitas" => $kompatibilitas,
            "bahasa" => $bahasa,
            "genre" => $genre_raw,
            "rating" => $rating,
            "jumlah_pemberi_rating" => $jumlahPemberiRating,
            "harga" => $harga,
            "deskripsi" => $deskripsi
        ];

        LogController::makeInfo($config->id,"Config dijalankan");
        $this->addDijalankan($config->id);
        $bookRes = Book::create($book);
        return $bookRes;

    }

    public function run(Request $req)
    {
        $validator = Validator::make($req->all(),[
            'id' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => "error",
                'data' => [],
                'msg' => $validator->errors(),
            ], 400);  
        }

        try {
            
            $config = Config::all()->where('id',$req->id)->firstOrFail();
            $bookRes = $this->scrap($config);

            return response()->json([
                'status' => "success",
                'data' => $bookRes,
                'msg' => "Successfully run config",
            ],200);

        } catch (ItemNotFoundException $e) {
            return response()->json([
                'status' => "error",
                'data' => [],
                'msg' => "Config with id $req->id not found",
            ], 400);
        }


    }

    /*
        ##################################################
        # config CRUD utilities
        ##################################################    
    */


    public function getAll(Request $req)
    {
        $configs = Config::all();
        return response()->json([
            'status' => "success",
            'data' => $configs,
            'msg' => "",
        ], 200);
    }

    public function getById(Request $req,Int $id)
    {
        try {            
            $config = Config::all()->where('id',$id)->firstOrFail();
            return response()->json([
                'status' => "success",
                'data' => $config,
                'msg' => "",
            ], 200);
        } catch (ItemNotFoundException $e) {
            return response()->json([
                'status' => "error",
                'data' => [],
                'msg' => "Config with id $req->id not found",
            ], 404);
        }
    }

    public function create(Request $req)
    {
        
        $validator = Validator::make($req->all(),[
            'name' => 'required|min:3|string',
            'link' => 'required|min:3|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => "error",
                'data' => [],
                'msg' => $validator->errors(),
            ], 400);
        }

        $user = auth()->user();
        $config = Config::create([
            "name" => $req->name,
            "link" => $req->link,
            "owner" => $user->id
        ]);

        if(!$config){
            return response()->json([
                'status' => "error",
                'data' => [],
                'msg' => "Error when creating config",
            ], 400);
        }

        LogController::makeInfo($config->id,"Membuat config ".$config->nama);
        return response()->json([
            'status' => "success",
            'data' => $config,
            'msg' => "Success creating config",
        ], 200);
    }

    public function update(Request $req)
    {
        $validator = Validator::make($req->all(),[
            "id" => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => "error",
                'data' => [],
                'msg' => $validator->errors(),
            ], 400);        
        }
        try {
            $config = Config::all()->where("id",$req->id)->firstOrFail();
            $config->update($req->except(['id']));
            $updated = array_keys($config->getChanges());
            $updated = implode(", ",$updated);

            LogController::makeInfo($req->id,"Mengupdate $updated");
            return response()->json([
                'status' => "success",
                'data' => $config,
                'msg' => "Success updating config",
            ], 200);
        } catch (ItemNotFoundException $e) {
            return response()->json([
                'status' => "error",
                'data' => [],
                'msg' => "Config with id $req->id not found",
            ], 400);
        }
    }

    public function delete(Request $req)
    {
        $validator = Validator::make($req->all(),[
            "id" => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => "error",
                'data' => [],
                'msg' => $validator->errors(),
            ], 400);        
        }

        try {
            $config = Config::all()->where("id",$req->id)->firstOrFail();
            return response()->json([
                'status' => "success",
                'data' => $config,
                'msg' => "Success deleting config",
            ], 200);
        } catch (ItemNotFoundException $e) {
            return response()->json([
                'status' => "error",
                'data' => [],
                'msg' => "Config with id $req->id not found",
            ], 400);
        }
    }

}

