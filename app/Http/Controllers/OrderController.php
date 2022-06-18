<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Http\Resources\ErrorResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\UserWallets;

class OrderController extends Controller
{
    use UserWallets;

    public function addOrder(OrderRequest $request){
         $user = Auth::user();
         $amount = $request->amount;
         $unitPrice = 1000;
         if($request->orderType == 'buy'){
            $balance = $this->getById($user->id)->rial_balance;
            $total = $amount*$unitPrice;
            
         }else{
            $balance = $this->getById($user->id)->mazin_balance;
            $total = $amount;
            
         }
         if($balance >= $total){
            //add order
            $order = Order::create(['user_id'=> $user->id, 'amount'=> $amount, 
            'unit_price' => $unitPrice, 'order_type'=>$request->orderType,'balance'=>$balance]);
        }else{
            return new ErrorResource((object)[
                'error' => __('errors.Error'),
                'message' => __('errors.Balance Is Not Enough'),
            ]);
        }
        return $order;

    }

    
}
