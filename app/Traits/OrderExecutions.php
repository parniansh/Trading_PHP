<?php

namespace App\Traits;

use App\Models\OrderExecution;
use App\Models\Order;


trait OrderExecutions{



    public function add(){

        $orderExecution = OrderExecution::create(['order_id'=>1, 'amout'=>1, 'price'=>1]);
        return $orderExecution;
    }

    public function getById(int $id){
        return OrderExecution::find($id)->first();
    }

    public function getByOrderId(int $orderId){
        return Order::find($orderId)->orderExecution->all();
    }
    
}