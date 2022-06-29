<?php

namespace App\Helpers;

use App\Http\Requests\UserWalletTransactionRequest;
use App\Models\Order;
use App\Models\OrderExecution;
use App\Traits\UserWallets;
use App\Traits\UserWalletTransactions;

class OrderExecutionHandling
{

        use UserWalletTransactions, UserWallets{
            UserWalletTransactions::add insteadof UserWallets;
            UserWallets::getByUser insteadOf UserWalletTransactions;
            UserWallets::update insteadOf UserWalletTransactions;
            UserWallets::getById insteadOf UserWalletTransactions;

        }


    public function __construct()
    {
        $this->bestOrders();

    }

    public function bestOrders()
    {
        $bestBuyer = Order::where(['order_type' => '0', 'state' => null])
            ->orWhere(['order_type' => '0', 'state' => 1])->orderBy('unit_price')->first();
        $bestSeller = Order::where(['order_type' => '1', 'state' => null])
            ->orwhere(['order_type' => '1', 'state' => 1])->orderby('unit_price', 'desc')->first();
        if ($bestBuyer->unit_price == $bestSeller->unit_price) {
            $this->handleTrade($bestSeller, $bestBuyer);
        } else {
            $this->Log::error("deal didnt succeded");
        }
    }

    public function handleTrade(Order $seller, Order $buyer)
    {
        $tradeAmount = min($seller->amount, $buyer->amount);
        
        $this->orderUpdate($seller, $tradeAmount);
        $this->orderUpdate($buyer, $tradeAmount);

        $this->userWalletUpdate($seller->user_id, $tradeAmount, $seller->order_type, $seller->unit_price);
        $this->userWalletUpdate($buyer->user_id, $tradeAmount, $buyer->order_type, $buyer->unit_price);

        $sellerExeId = $this->orderExecutionRecordCreate($seller, $tradeAmount);
        $buyerExeId = $this->orderExecutionRecordCreate($buyer, $tradeAmount);

        $this->transactionRecordCreate($sellerExeId, $seller, $tradeAmount,$sellerExeId);
        $this->transactionRecordCreate($buyerExeId, $buyer, $tradeAmount, $buyerExeId);

        return $tradeAmount;

    }

    public function orderUpdate(Order $order, float $tradeAmount){
        $remain = $order->amount - $tradeAmount;
        if($remain == 0){
            $order->update(['remain'=>0, 'state'=>1]);
        }else{

            $order->update(['remain'=>$remain, 'state'=>0]);

        }
    }

    public function userWalletUpdate(int $userId, float $tradeAmount, string $order_type, float $unit_price){
        $userWallet  = $this->getByUser($userId);
        if($order_type == '0'){
            //buy
            
            $mazinBalance = $userWallet->mazin_balance + $tradeAmount;
            $rialBalance = $userWallet->rial_balance - $tradeAmount* $unit_price;
            $freezed_rial = $userWallet->freezed_rial - $tradeAmount* $unit_price;
            $userWallet->update(['mazin_balance'=>$mazinBalance, 'rial_balance'=>$rialBalance, 'freezed_rial'=>$freezed_rial]);
        }elseif($order_type == '1'){
            //sell
            $mazinBalance = $userWallet->mazin_balance - $tradeAmount;
            $rialBalance = $userWallet->rial_balance + $tradeAmount* $unit_price;
            $freezed_mazin = $userWallet->freezed_mazin - $tradeAmount;
            $userWallet->update(['mazin_balance'=>$mazinBalance, 'rial_balance'=>$rialBalance, 'freezed_mazin'=>$freezed_mazin]);

        }
    }

    public function orderExecutionRecordCreate(Order $order, float $tradeAmount){
        //var_dump($order->id);
        $orderExeRecord = OrderExecution::create(['order_id'=>$order->id, 
        'amount'=>$tradeAmount, 'unit_price'=>$order->unit_price]);
        //var_dump($orderExeRecord);
        return $orderExeRecord->id;
    }

    public function transactionRecordCreate(int $orderExeId , Order $order, float $tradeAmount, int $exeId){
        $userWalletTransReq = new UserWalletTransactionRequest(['amount'=>$tradeAmount, 
                    'trans_kind'=>$order->order_type,'user_id'=>$order->user_id,
                    'token_type'=>$order->balance_type,'execution_id'=>$exeId]);
                    $this->add($userWalletTransReq);
    }

}
