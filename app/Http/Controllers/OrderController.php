<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\DetailOrder;;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
// use Haruncpi\LaravelIdGenerator\IdGenerator;

class OrderController extends Controller
{
    public function store(Request $req){
        $valid = Validator::make($req->all(),[
            'customer_name' => 'required',
            'customer_email' => 'required',
            'check_in_date' => 'required|date',
            'duration' => 'required|integer',
            'guest_name' => 'required',
            'room_qty' => 'required|integer',
            'room_type_id' => 'required|integer',
            'order_status' => 'required',
            'user_id' => 'required|integer',
            // 'room_id' => 'required|integer',
            'price' => 'required|integer'
        ]);

        if($valid->fails()){
            return response()->json($valid->errors());
        }

        //var date
        $dur = $req->duration;
        $in = Carbon::parse($req->check_in_date);
        // $masuk = new Carbon($req->check_in_date);
        $out = $in->addDays($dur);
        $from = date($req->check_in_date);
        $to = date($out);

        //var order terakhir
        $latest = Order::orderBy('order_date','DESC')->first();

        //var room terpilih
        // $room = DB::table('room')
        //             ->select('room.room_id')
        //             ->leftJoin('room_type', 'room_type.room_type_id', 'room.room_type_id')
        //             ->leftJoin('detail_order',  function($join) use($from, $to){
        //                 $join->on('room.room_id', '=', 'detail_order.room_id')
        //                 ->whereBetween('detail_order.access_date', [$from, $to]);
        //             })
        //             ->where('detail_order.access_date', '=', NULL)
        //             ->where('room.room_type_id', '=', $req->room_type_id)
        //             ->orderBy('room.room_id')
        //             ->first();

        $order = new Order();
        $order->order_number = 'ORD-NMB-'.str_pad($latest->order_id + 1, 8, "0", STR_PAD_LEFT);
        $order->customer_name = $req->customer_name;
        $order->customer_email = $req->customer_email;
        $order->check_in_date = $req->check_in_date;
        $order->check_out_date = $out;
        $order->guest_name = $req->guest_name;
        $order->room_qty = $req->room_qty;
        $order->room_type_id = $req->room_type_id;
        $order->order_status = $req->order_status;
        $order->user_id = $req->user_id;
        $order->save();

        //insert details
        // for($i = 0; $i < $req->duration; $i++){
        //     $detail = new DetailOrder();
        //     $detail->order_id = $order->order_id;
        //     $detail->room_id = $room->room_id;
        //     $detail->access_date = $masuk;
        //     $detail->price = $req->price;
        //     $detail->save();
        //     $masuk->addDays(1);
        // }
        for($i = 0; $i < $req->room_qty; $i++){
            $room = DB::table('room')
                    ->select('room.room_id')
                    ->leftJoin('room_type', 'room_type.room_type_id', 'room.room_type_id')
                    ->leftJoin('detail_order',  function($join) use($from, $to){
                        $join->on('room.room_id', '=', 'detail_order.room_id')
                        ->whereBetween('detail_order.access_date', [$from, $to]);
                    })
                    ->where('detail_order.access_date', '=', NULL)
                    ->where('room.room_type_id', '=', $req->room_type_id)
                    ->orderBy('room.room_id')
                    ->first();
            $masuk = new Carbon($req->check_in_date);
            for($j = 0; $j < $req->duration; $j++){
                $detail = new DetailOrder();
                $detail->order_id = $order->order_id;
                $detail->room_id = $room->room_id;
                $detail->access_date = $masuk;
                $detail->price = $req->price;
                $detail->save();
                $masuk->addDays(1);
            }
        }

        if($order && $detail){
            $dt = Order::select('order.*', 'room_type.room_type_id', 'room_type.room_type_name', 'user.user_id', 'user.user_name')
            ->join('room_type', 'room_type.room_type_id', '=', 'order.room_type_id')
            ->join('user', 'user.user_id', '=', 'order.user_id')
            ->where('order_id', $order->order_id)
            ->get();
            $dt_detail = DetailOrder::where('order_id', $order->order_id)->get();
            return response()->json([
                'status' => true,
                'message' => 'Succeed Order Room',
                'data' => $dt,
                'detail' => $dt_detail
            ]);
        }
        else {
            return response()->json([
                'status' => false,
                'message' => 'Failed Order Room'
            ]);
        }
    }

    public function detail(Request $req, $id){
        $valid = Validator::make($req->all(),[
            'room_id' => 'required|integer',
            'check_in_date' => 'required|date',
            'duration' => 'required|integer',
            'price' => 'required|integer' 
        ]);

        $in = new Carbon($req->check_in_date);

        for($i = 0; $i < $req->duration; $i++){
            $detail = new DetailOrder();
            $detail->order_id = $id;
            $detail->room_id = $req->room_id;
            $detail->access_date = $in->addDays($i);
            $detail->price = $req->price;
            $detail->save();
        }

        if($detail){
            $dt = DetailOrder::where('order_id', $id)->get();
            return response()->json([
                'status' => true,
                'message' => 'Succeed',
                'data' => $dt
            ]);
        }
    }
}
