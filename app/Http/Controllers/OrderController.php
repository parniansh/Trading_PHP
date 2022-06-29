<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\SuccessResource;
use App\Models\Order;
use App\Traits\OrderExecutions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\UserWallets;
use Exception;

class OrderController extends Controller
{
    use UserWallets;

    private $rial_balance,$freezed_rial,$mazin_balance,$freezed_mazin,$user;

    public function __construct()
    {
        
    }
    public function addOrder(OrderRequest $request)
    {
        $user = Auth::user();
        $getByUser = $this->getByUser($user->id);
        $this->rial_balance = $getByUser->rial_balance;
        $this->freezed_rial = $getByUser->freezed_rial;
        $this->mazin_balance = $getByUser->mazin_balance;
        $this->freezed_mazin = $getByUser->freezed_mazin;
        $this->user = $user;
        
        
        $amount = $request->amount;
        $unitPrice = $request->unitPrice;
        $orderType = $request->orderType;

        //orderType :
        // 0: buy
        // 1: sale
        if ($orderType == 0) {
            $balance = $this->rial_balance - $this->freezed_rial;
            $balanceType = 0; //rial
            $total = $amount * $unitPrice;
            $userWalletRequest = ['freezed_rial'=>$total+$this->freezed_rial, 'userId'=>$this->user->id];
        } else if ($orderType == 1) {
            $balance = $this->mazin_balance - $this->freezed_mazin;
            $balanceType = 1; //mazin
            $total = $amount;
            $userWalletRequest = ['freezed_mazin' => $total+$this->freezed_mazin,'userId'=>$this->user->id];
        }
        if ($balance >= $total) {
            try {
                $order = Order::create([
                    'user_id' => $this->user->id, 'amount' => $amount,
                    'unit_price' => $unitPrice, 'order_type' => $orderType, 'balance' => $balance,
                    'balance_type' => $balanceType, 'remain' => $amount
                ]);
                $this->update((object)$userWalletRequest);
                
            } catch (Exception $e) {
                return new ErrorResource((object)[
                    'error' => __('errors.Error'),
                    'message' => __('errors.Server Error Occured'),
                ]);
            }
        } else {
            return new ErrorResource((object)[
                'error' => __('errors.Error'),
                'message' => __('errors.Balance Is Not Enough'),
            ]);
        }
       // $this->handleOrder($order);

        return $order;
    }



    public function deleteOrder(OrderRequest $request)
    {
        $order = Order::find($request->orderId);
        if ($order->user_id == Auth::user()->id) {
            
            if($order->order_type == 0){
                $userWalletFreezedRial = $this->getByUser($order->user_id)->freezed_rial;
                $remain = $order->remain * $order->unit_price;
                $undoFreezed = $userWalletFreezedRial - $remain;
                $userWalletRequest = new Request(['userId'=>$order->user_id, 'freezed_rial'=>$undoFreezed]);
            }else{
                $userWalletFreezedmazin = $this->getByUser($order->user_id)->freezed_mazin;
                $undoFreezed = $userWalletFreezedmazin - $order->remain;
                $userWalletRequest = new Request(['userId'=>$order->user_id, 'freezed_mazin'=>$undoFreezed]);
            }
        } else {
            return new ErrorResource((object)[
                'error' => __('errors.Error'),
                'message' => __('errors.Credentials Are Incorrect'),
            ]);
        }

        try{
            $this->update($userWalletRequest);
            $order->update(['state'=> 2]);
            return new SuccessResource((object)['data' => (object)[
                'state' => __('executionState.2')
            ]]);

        }catch(Exception $e){
            return new ErrorResource((object)[
                'error' => __('errors.Error'),
                'message' => __('errors.Server Error Occured'),
            ]);
        }
    }


    // public function test(Request $request){
    //    try{
    //     $o = new OrderExecutionHandling();
    //     return $o->returnPrice();
        
    //    }catch(Exception $e){
    //     return new ErrorResource((object)[
    //         'error' => __('errors.Error'),
    //         'message' => __('errors.Edit Is Not Possible'),
    //     ]); 
    //    }
    // }
}
