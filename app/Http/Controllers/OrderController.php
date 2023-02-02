<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
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
        ]);

        if($valid->fails()){
            return response()->json($valid->errors());
        }

        $dur = $req->duration;
        $in = Carbon::parse($req->check_in_date);
        $out = $in->addDays($dur);

        $save = Order::create([
            'order_number' => $req->order_number,
            'customer_name' => $req->customer_name,
            'customer_email' => $req->customer_email,
            // 'order_date' => $req->order_date,
            'check_in_date' => $req->check_in_date,
            'check_out_date' => $out,
            'guest_name' => $req->guest_name,
            'room_qty' => $req->room_qty,
            'room_type_id' => $req->room_type_id,
            'order_status' => $req->order_status,
            'user_id' => $req->user_id
        ]);

        if($save){
            $dt = Order::where('order_number', $req->order_number)->get();
            return response()->json([
                'status' => true,
                'message' => 'Succeed Ordering',
                'data' => $dt
            ]);
        }
        else {
            return response()->json([
                'status' => false,
                'message' => 'Failed Ordering'
            ]);
        }
    }
}
