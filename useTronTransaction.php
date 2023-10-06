<?php

// 包含 TronTransaction 类的定义
require 'TronTransaction.php';

// 创建一个新的 TronTransaction 实例
$privateKey = 'your-private-key-here'; // 替换为你的私钥
$tronTransaction = new TronTransaction($privateKey);
$sendAddress = 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t';
$trxReceiverAddress = 'receiver-address'; // 替换 'receiver-address-for-TRX' 为实际接收 TRX 的地址
$tokenReceiverAddress = 'receiver-address-for-Token'; // 替换 'receiver-address-for-Token' 为实际接收代币的地址

// 发送 TRX 到指定地址
// 设置接收者地址和发送数量
$result = $tronTransaction->sendTRX($trxReceiverAddress, 100);
print_r($result);

// 发送 TRC10 代币到指定地址
// 设置代币的合约地址、接收者地址和发送数量
$tokenResult = $tronTransaction->sendToken($sendAddress, $tokenReceiverAddress, 100);
print_r($tokenResult);


// TRC20 合约地址、接收者地址和要发送的代币数量
$contractAddress = 'TRC20-contract-address-here'; // 用实际的 TRC20 合约地址替换这里
$toAddress = 'receiver-address-here'; // 用接收者的 TRON 地址替换这里
$amount = 100; // 要发送的代币数量（注意：数量可能需要根据代币的小数位数进行调整）

// 发送 TRC20 代币
$response = $tronTransaction->sendTRC20($contractAddress, $toAddress, $amount);
print_r($response);