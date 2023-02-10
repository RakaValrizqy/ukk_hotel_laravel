<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\DetailOrder;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class OrderController extends Controller
{
    public function store(Request $req){
        $valid = Validator::make($req->all(),[
            'order_number' => 'required|unique:order',
            'customer_name' => 'required',
            'customer_email' => 'required',
            // 'order_date' => 'required|date',
            'check_in_date' => 'required|date',
            'duration' => 'required|integer',
            'guest_name' => 'required',
            'room_qty' => 'required|integer',
            'room_type_id' => 'required|integer',
            'order_status' => 'required',
            'user_id' => 'required|integer',
            'room_id' => 'required|integer',
            'price' => 'required|integer'
        ]);

        if($valid->fails()){
            return response()->json($valid->errors());
        }

        $dur = $req->duration;
        $in = Carbon::parse($req->check_in_date);
        $masuk = new Carbon($req->check_in_date);
        $out = $in->addDays($dur);

        // $save = Order::create([
        //     'order_number' => $req->order_number,
        //     'customer_name' => $req->customer_name,
        //     'customer_email' => $req->customer_email,
        //     // 'order_date' => $req->order_date,
        //     'check_in_date' => $req->check_in_date,
        //     'check_out_date' => $out,
        //     'guest_name' => $req->guest_name,
        //     'room_qty' => $req->room_qty,
        //     'room_type_id' => $req->room_type_id,
        //     'order_status' => $req->order_status,
        //     'user_id' => $req->user_id
        // ]);

        $order = new Order();
        $order->order_number = $req->order_number;
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

        //insert detail
        for($i = 0; $i < $req->duration; $i++){
            $detail = new DetailOrder();
            $detail->order_id = $order->order_id;
            $detail->room_id = $req->room_id;
            $detail->access_date = $masuk->addDays($i);
            $detail->price = $req->price;
            $detail->save();
        }

        if($order && $detail){
            $dt = Order::select('order.*', 'room_type.room_type_id', 'room_type.room_type_name', 'user.user_id', 'user.user_name')
            ->join('room_type', 'room_type.room_type_id', '=', 'order.room_type_id')
            ->join('user', 'user.user_id', '=', 'order.user_id')
            ->where('order_number', $req->order_number)
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
            // 'order_id' => 'required|integer',
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
