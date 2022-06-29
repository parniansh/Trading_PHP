<?php

namespace App\Traits;

use App\Http\Requests\UserWalletTransactionRequest;
use App\Http\Resources\ErrorResource;
use App\Models\OrderExecution;
use App\Models\Order;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait OrderExecutions{
  
    use UserWalletTransactions, UserWallets{
        UserWalletTransactions::add insteadof UserWallets;
        UserWallets::getByUser insteadOf UserWalletTransactions;
        UserWallets::update insteadOf UserWalletTransactions;
    }

    public function handleOrder(Order $order){
        $orderType = $order->order_type;
        $unitPrice = $order->unit_price;
        $remain = $order->remain;
        $doneDeals = array();
        if($orderType == 0){
            $properdeals = DB::table('orders')->where(['order_type'=>'1','unit_price'=> $unitPrice, 'state'=>null])
            ->orWhere(['order_type'=>'1','unit_price'=> $unitPrice, 'state'=>1])->orderBy('created_at', 'desc')->get();
        }else{
            $properdeals = DB::table('orders')->where(['order_type'=>'0', 'unit_price'=> $unitPrice, 'state'=>null])
            ->orWhere(['order_type'=>'0', 'unit_price'=> $unitPrice, 'state'=>1])->orderBy('created_at', 'desc')->get();
        }
        try{
            $n = 0;
            while($remain != 0 || count($properdeals)!=count($doneDeals)){
                $currentDeal = $properdeals[$n];
                if($remain - $currentDeal->remain >= 0){

                    $remain = $remain - $currentDeal->remain;
                    $currentDeal->update(['remain'=>0, 'state'=>1]);
                    $order->update([$remain=>$remain - $currentDeal->remain, 'state'=>0]);


                    $currentDealExe = OrderExecution::create(['order_id'=>$currentDeal->id, 
                    'amount'=>$currentDeal->amount, 'unit_price'=>$currentDeal->unit_price]);
                    $orderExe = OrderExecution::create(['order_id'=>$order->id, 
                    'amount'=>$order->amount, 'unit_price'=>$order->unit_price]);

                    
                    $userWalletTransReq = new UserWalletTransactionRequest(['amount'=>$currentDeal->amount, 
                    'trans_kind'=>$currentDeal->order_type,'user_id'=>$currentDeal->user_id,
                    'token_type'=>$currentDeal->balance_type,'execution_id'=>$currentDealExe->id]);
                    $this->add($userWalletTransReq);

                    $userWalletTransMainReq = new UserWalletTransactionRequest(['amount'=>$currentDeal->amount, 
                    'trans_kind'=>$order->order_type,'user_id'=>$order->user_id,
                    'token_type'=>$order->balance_type,'execution_id'=>$orderExe->id]);
                    $this->add($userWalletTransMainReq);

                    $userWallet = $this->getByUser($currentDeal->User_id);
                    $userWalletMain = $this->getByUser($order->User_id);
                    if($orderType == 0){
                        $freedfreezedmazin = $currentDeal->amount;
                        $userWalletReq = new Request(['user_id'=>$currentDeal->user_id,'mazin_balance'=>$userWallet->mazin_balance - $freedfreezedmazin, 
                        'rial_balance'=> $userWallet->rial_balance + $freedfreezedmazin*$unitPrice,
                        'freezed_mazin'=>$userWallet->freezed_mazin -$freedfreezedmazin ]);
                        $this->update($userWalletReq);

                        $userWalletMainReq = new Request(['user_id'=>$order->user_id,'mazin_balance'=>$userWallet->mazin_balance + $freedfreezedmazin, 
                        'rial_balance'=> $userWalletMain->rial_balance - $freedfreezedmazin*$unitPrice,
                        'freezed_rial'=>$userWalletMain->freezed_rial - $freedfreezedmazin*$unitPrice ]);
                        $this->update($userWalletMainReq);

                    }else{
                        $freedfreezedRial = $currentDeal->amount * $unitPrice;
                        $userWalletReq = new Request(['user_id'=>$currentDeal->user_id,'rial_balance'=>$userWallet->rial_balance - $freedfreezedRial, 
                        'mazin_balance' => $userWallet->mazin_balance +$currentDeal->amount,
                        'freezed_rial'=>$userWallet->freezed_rial -$freedfreezedRial ]);
                        $this->update($userWalletReq);

                        $userWalletMainReq = new Request(['user_id'=>$order->user_id,'mazin_balance'=>$userWallet->mazin_balance - $currentDeal->amount, 
                        'rial_balance'=> $userWalletMain->rial_balance + $freedfreezedRial,
                        'freezed_mazin'=>$userWalletMain->freezed_mazin - $currentDeal->amount ]);
                        $this->update($userWalletMainReq);

                    }



                }else{
                    $currentDealRemain = $currentDeal->remain - $order->remain;

                    $currentDeal->update(['remain'=>$currentDealRemain, 'state'=>0]);
                    $order->update([$remain=>0, 'state'=>1]);

                    $currentDealExe = OrderExecution::create(['order_id'=>$currentDeal->id, 
                    'amount'=>$order->amount, 'unit_price'=>$currentDeal->unit_price]);
                    $orderExe = OrderExecution::create(['order_id'=>$order->id, 
                    'amount'=>$order->amount, 'unit_price'=>$order->unit_price]);

                    $userWalletTransReq = new UserWalletTransactionRequest(['amount'=>$order->amount, 
                    'trans_kind'=>$currentDeal->order_type,'user_id'=>$currentDeal->user_id,
                    'token_type'=>$currentDeal->balance_type,'execution_id'=>$currentDealExe->id]);
                    $this->add($userWalletTransReq);

                    $userWalletTransMainReq = new UserWalletTransactionRequest(['amount'=>$order->amount, 
                    'trans_kind'=>$order->order_type,'user_id'=>$order->user_id,
                    'token_type'=>$order->balance_type,'execution_id'=>$orderExe->id]);
                    $this->add($userWalletTransMainReq);


                    $userWallet = $this->getByUser($currentDeal->User_id);
                    $userWalletMain = $this->getByUser($order->User_id);
                    if($orderType == 0){
                        $freedfreezedmazin = $order->amount;
                        $userWalletReq = new Request(['user_id'=>$currentDeal->user_id,'mazin_balance'=>$userWallet->mazin_balance - $freedfreezedmazin, 
                        'rial_balance'=> $userWallet->rial_balance + $freedfreezedmazin*$unitPrice,
                        'freezed_mazin'=>$userWallet->freezed_mazin -$freedfreezedmazin ]);
                        $this->update($userWalletReq);

                        $userWalletMainReq = new Request(['user_id'=>$order->user_id,'mazin_balance'=>$userWallet->mazin_balance + $freedfreezedmazin, 
                        'rial_balance'=> $userWalletMain->rial_balance - $freedfreezedmazin*$unitPrice,
                        'freezed_rial'=>$userWalletMain->freezed_rial - $freedfreezedmazin*$unitPrice ]);
                        $this->update($userWalletMainReq);

                    }else{
                        $freedfreezedRial = $order->amount * $unitPrice;
                        $userWalletReq = new Request(['user_id'=>$currentDeal->user_id,'rial_balance'=>$userWallet->rial_balance - $freedfreezedRial, 
                        'mazin_balance' => $userWallet->mazin_balance +$order->amount,
                        'freezed_rial'=>$userWallet->freezed_rial -$freedfreezedRial ]);
                        $this->update($userWalletReq);

                        $userWalletMainReq = new Request(['user_id'=>$order->user_id,'mazin_balance'=>$userWallet->mazin_balance - $order->amount, 
                        'rial_balance'=> $userWalletMain->rial_balance + $freedfreezedRial,
                        'freezed_mazin'=>$userWalletMain->freezed_mazin - $order->amount ]);
                        $this->update($userWalletMainReq);

                    }



                }
                array_push($doneDeals,$currentDeal);
                $n = $n +1;

            }
        }catch(Exception $e){}
            return new ErrorResource((object)[
                'error' => __('errors.Error'),
                'message' => __('errors.Server Error Occured'),
            ]);  
        }

        
    





    //---------------------------------------------------------------------------------------------------
   

    public function getById(int $id){
        return OrderExecution::find($id)->first();
    }

    public function getByOrderId(int $orderId){
        return Order::find($orderId)->orderExecution->all();
    }
    
}