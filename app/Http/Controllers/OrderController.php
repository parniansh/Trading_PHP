<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\SuccessResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\UserWallets;
use Exception;

class OrderController extends Controller
{
    use UserWallets;

    public function addOrder(OrderRequest $request)
    {
        
        $user = Auth::user();
        $amount = $request->amount;
        $unitPrice = $request->unitPrice;
        $orderType = $request->orderType;
        $userWallet = $this->getByUser($user->id);

        //orderType :
        // 0: buy
        // 1: sale
        if ($orderType == 0) {
            $balance = $this->getByUser($user->id)->rial_balance - $this->getByUser($user->id)->freezed_rial;
            $balanceType = 0; //rial
            $total = $amount * $unitPrice;
            $userWalletRequest = new Request(['freezed_rial'=>$total+$userWallet->freezed_rial, 'userId'=>$user->id]);

        } else if ($orderType == 1) {
            $balance = $this->getByUser($user->id)->mazin_balance - $this->getByUser($user->id)->freezed_mazin;
            $balanceType = 1; //mazin
            $total = $amount;
            $userWalletRequest = new Request(['freezed_mazin'=>$total+$userWallet->freezed_mazin,'userId'=>$user->id]);

        }
        if ($balance >= $total) {
            try {
                $order = Order::create([
                    'user_id' => $user->id, 'amount' => $amount,
                    'unit_price' => $unitPrice, 'order_type' => $orderType, 'balance' => $balance,
                    'balance_type' => $balanceType, 'remain' => $amount
                ]);
                $this->update($userWalletRequest);
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
        return $order;
    }

    public function updateOrder(OrderRequest $request)
    {
        //not complete

        $order = Order::find($request->orderId);
        if ($order->user_id == Auth::user()->id) {
            $order->amount = $request->amount;
            $order->order_type = $request->orderType;
            $order->save();
            return $order;
        } else {
            return new ErrorResource((object)[
                'error' => __('errors.Error'),
                'message' => __('errors.Credentials Are Incorrect'),
            ]);
        }
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
}
