<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\DetailOrder;
use App\Models\RoomType;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
        ]);

        if($valid->fails()){
            return response()->json($valid->errors()->toJson());
        }

        //var date
        $dur = $req->duration;
        $in = Carbon::parse($req->check_in_date);
        $out = $in->addDays($dur);
        $from = date($req->check_in_date);
        $to = date($out);

        //var banyak kamar yang tersedia pada tipe kamar yang dipilih
        $avail = DB::table('room')
                        ->select('room_type.*', DB::raw('count(room.room_id) as available'))
                        ->leftJoin('room_type', 'room.room_type_id', '=', 'room_type.room_type_id')
                        ->leftJoin('detail_order', function($join) use($from, $to){
                            $join->on('room.room_id', '=', 'detail_order.room_id')
                            ->whereBetween('detail_order.access_date', [$from, $to]);
                        })
                        ->where('detail_order.access_date', '=', NULL)
                        ->where('room.room_type_id', $req->room_type_id)
                        ->groupBy('room_type.room_type_id')
                        ->first();
        $availRoom = $avail->available;

        //percabangan jika yang dipesan lebih banyak dari yang tersedia
        if($req->room_qty > $availRoom){
            return response()->json([
                'status' => false,
                'message' => 'Not enough room for your order'
            ]);
        }

        //var order terakhir
        $latest = Order::orderBy('order_date','DESC')->first();
        if(is_null($latest)) {
            $id = 0;
        } else {
            $id = $latest->order_id;
        }

        //var price
        $roomType = RoomType::where('room_type_id', '=', $req->room_type_id)
                        ->first();

        $order = new Order();
        $order->order_number = 'ORD-NMB-'.str_pad($id + 1, 8, "0", STR_PAD_LEFT);
        $order->customer_name = $req->customer_name;
        $order->customer_email = $req->customer_email;
        $order->check_in_date = $req->check_in_date;
        $order->check_out_date = $out;
        $order->guest_name = $req->guest_name;
        $order->room_qty = $req->room_qty;
        $order->room_type_id = $req->room_type_id;
        $order->order_status = 1;
        $order->save();

        //var total
        $total = 0;

        for($i = 0; $i < $req->room_qty; $i++){
            //select room
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
            //reset var access_date
            $masuk = new Carbon($req->check_in_date);
            for($j = 0; $j < $req->duration; $j++){
                $detail = new DetailOrder();
                $detail->order_id = $order->order_id;
                $detail->room_id = $room->room_id;
                $detail->access_date = $masuk;
                $detail->price = $roomType->price;
                $total += $roomType->price;
                $detail->save();
                $masuk->addDays(1);
            }
        }

        $updateTotal = Order::where('order_id', '=', $order->order_id)->update([
            'total' => $total
        ]);

        if($order && $detail && $updateTotal){
            $dt = Order::select('order.*', 'room_type.room_type_id', 'room_type.room_type_name')
                        ->join('room_type', 'room_type.room_type_id', '=', 'order.room_type_id')
                        ->where('order_id', $order->order_id)
                        ->get();

            $dt_detail = DetailOrder::select('detail_order.*', 'room_type.room_type_name', 'room.room_number')
                                    ->join('room', 'detail_order.room_id', '=', 'room.room_id')
                                    ->join('room_type', 'room.room_type_id', '=', 'room_type.room_type_id')
                                    ->where('detail_order.order_id', '=', $order->order_id)
                                    ->get();
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

    public function findByOrderNumber(Request $req){
        $valid = Validator::make($req->all(),[
            'order_number' => 'required',
            'email' => 'required|email'
        ]);

        if($valid->fails()){
            return response()->json($valid->errors()->toJson());
        }

        if(Order::where('order_number', '=', $req->order_number)->exists()){
            $order = Order::where('order_number', '=', $req->order_number)->first();
            if($req->email == $order->customer_email){
                $dt = Order::select('order.*', 'room_type.room_type_id', 'room_type.room_type_name')
                    ->join('room_type', 'room_type.room_type_id', '=', 'order.room_type_id')
                    ->where('order.order_number', $req->order_number)
                    ->get();

                $room = DetailOrder::select('room.room_number')
                                    ->join('room', 'detail_order.room_id', '=', 'room.room_id')
                                    ->join('room_type', 'room.room_type_id', '=', 'room_type.room_type_id')
                                    ->where('detail_order.order_id', '=', $order->order_id)
                                    ->distinct()
                                    ->get();

                return response()->json([
                    'status' => true,
                    'message' => 'Data Found!',
                    'data' => $dt,
                    'room' => $room 
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Email does not match, please check again'
                ]);
            }

        } else {
            return response()->json([
                'status' => false,
                'message' => 'Data Not Found'
            ]);
        }
    }

    public function findByName(Request $req){
        $valid = Validator::make($req->all(),[
            'name' => 'string',
            'date' => 'date'
        ]);

        if($valid->fails()){
            return response()->json($valid->errors()->toJson());
        }

        $dt= [];

        if($req->date != null && $req->name != null){
            $dt = Order::select('order.*', 'room_type.room_type_id', 'room_type.room_type_name')
                        ->where('guest_name', 'like', '%'.$req->name.'%')
                        ->join('room_type', 'room_type.room_type_id', '=', 'order.room_type_id')
                        ->where('check_in_date', '=', $req->date)
                        ->get();
        }

        elseif($req->name == null && $req->date != null){
            $dt = Order::select('order.*', 'room_type.room_type_id', 'room_type.room_type_name')
                        ->where('check_in_date', '=', $req->date)
                        ->join('room_type', 'room_type.room_type_id', '=', 'order.room_type_id')
                        ->get();
        }

        elseif($req->date == null && $req->name != null){
            $dt = Order::select('order.*', 'room_type.room_type_id', 'room_type.room_type_name')
                        ->where('guest_name', 'like', '%'.$req->name.'%')
                        ->join('room_type', 'room_type.room_type_id', '=', 'order.room_type_id')
                        ->get();
        } else {
            $dt = Order::select('order.*', 'room_type.room_type_id', 'room_type.room_type_name')
                        ->join('room_type', 'room_type.room_type_id', '=', 'order.room_type_id')
                        ->get();
        }
        
        if(sizeof($dt)){
            return response()->json([
                'status' => true,
                'data' => $dt
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Data Not Found'
            ]);
        }
    }

    public function status(Request $req, $id){
        $valid = Validator::make($req->all(),[
            'order_status' => 'required',
            'user_id' => 'required|integer'
        ]);

        if($valid->fails()){
            return response()->json($valid->errors()->toJson());
        }

        $update = Order::where('order_id', '=', $id)->update([
            'order_status' => $req->order_status,
            'user_id' => $req->user_id
        ]);

        if($update){
            return response()->json([
                'status' => true,
                'message' => 'Succeed update data'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Failed update data'
            ]);
        }
    }

    public function show(){
        $dt = Order::select('order.*', 'room_type.room_type_id', 'room_type.room_type_name')
                ->join('room_type', 'room_type.room_type_id', '=', 'order.room_type_id')
                ->get();
        if(sizeof($dt)){
            return response()->json([
                'status' => true,
                'data' => $dt
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Data Not Found'
            ]);
        }
    }
}
