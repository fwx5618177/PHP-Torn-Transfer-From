<?php

require 'vendor/autoload.php';

use IEXBase\TronAPI\Tron;

class TronTransaction
{
    private $tron;
    private $privateKey;

    /**
     * 构造函数初始化 TRON 类和设置私钥
     *
     * @param string $privateKey 发送者的私钥
     */
    public function __construct($privateKey)
    {
        $this->tron = new Tron();
        $this->privateKey = $privateKey;
        $this->tron->setPrivateKey($privateKey);
    }

    /**
     * 发送 TRX 到指定地址
     *
     * @param string $toAddress 接收者的 TRON 地址
     * @param int $amount 要发送的 TRX 数量，单位为 sun（1 TRX = 1,000,000 sun）
     * @return array|string 交易结果或错误消息
     */
    public function sendTRX($toAddress, $amount)
    {
        try {
            $transaction = $this->tron->getTransactionBuilder()->sendTrx($toAddress, $amount, $this->tron->address);
            $signedTransaction = $this->tron->signTransaction($transaction);
            $result = $this->tron->sendRawTransaction($signedTransaction);
            return $result;
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * 发送 TRC10 代币到指定地址
     *
     * @param string $contractAddress 代币的合约地址
     * @param string $toAddress 接收者的 TRON 地址
     * @param int $amount 要发送的代币数量，单位取决于代币的小数位数
     * @return array|string 交易结果或错误消息
     */
    public function sendToken($contractAddress, $toAddress, $amount)
    {
        try {
            // 检查金额是否为负数
            if ($amount < 0) {
                throw new Exception("The amount should not be negative.");
            }
            
            $transaction = $this->tron->getTransactionBuilder()->sendToken($toAddress, $amount, $contractAddress);
            $signedTransaction = $this->tron->signTransaction($transaction);
            $result = $this->tron->sendRawTransaction($signedTransaction);
            return $result;
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * 发送 TRC20 代币
     *
     * @param string $contractAddress TRC20 代币的合约地址
     * @param string $toAddress 接收代币的 TRON 地址
     * @param int $amount 发送的代币数量（根据代币的小数位数来计算，例如：1 代币可能需要写成 1000000，如果代币有6位小数）
     * @return array 返回交易的结果
     */
    public function sendTRC20($contractAddress, $toAddress, $amount)
    {
        // 校验金额，确保不为负数。金额为0是允许的。
        if ($amount < 0) {
            throw new Exception("The amount should not be negative.");
        }

        // 获取合约的 ABI
        $contractABI = $this->getContractABI($contractAddress);

        // 创建一个触发智能合约的交易
        // 这里调用了 TRC20 合约的 `transfer` 方法来发送代币
        // 参数包括接收者的地址和发送的数量
        $transaction = $this->tron->getTransactionBuilder()->triggerSmartContract(
            $contractAddress,
            'transfer(address,uint256)',
            '0',
            [
                [
                    'type' => 'address',
                    'value' => $toAddress
                ],
                [
                    'type' => 'uint256',
                    'value' => $amount
                ]
            ],
            $this->tron->address,
            $contractABI,   // Call ABI
        );

        // 签名交易
        $signedTransaction = $this->tron->signTransaction($transaction['transaction']);

        // 发送已签名的交易
        $response = $this->tron->sendRawTransaction($signedTransaction);

        return $response;
    }

     /**
     * TRC20 代理支付: 使用账户A的能量，让账户C给账户B转账
     *
     * @param string $accountA 私钥 - 用于签名交易和支付能量费用
     * @param string $accountC 合约地址 - 代币将从这个地址转出
     * @param string $accountB 接收地址 - 代币将转入这个地址
     * @param int $amount 转账金额
     * @return array 返回交易结果
     */
    public function sendByDelegation($accountA, $accountC, $accountB, $amount)
    {
        try {
            // 设置账户A的私钥
            $this->tron->setPrivateKey($accountA);

            // 获取合约的 ABI
            $contractABI = $this->getContractABI($accountC);

            // 创建一个触发账户C的合约转账的交易
            // 注意: 这里需要适当的合约方法和参数
            $transaction = $this->tron->getTransactionBuilder()->triggerSmartContract(
                $accountC,
                'transfer(address,address,uint256)',  // 这里需要具体的合约方法和参数格式
                '0',
                [
                    ['type' => 'address', 'value' => $accountC],
                    ['type' => 'address', 'value' => $accountB],
                    ['type' => 'uint256', 'value' => $amount]
                ],
                $this->tron->address,
                $contractABI  // 添加 ABI 信息到方法调用
            );

            // 签名交易
            $signedTransaction = $this->tron->signTransaction($transaction['transaction']);

            // 发送已签名的交易
            $result = $this->tron->sendRawTransaction($signedTransaction);
            
            return $result;
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * 获取合约的 ABI
     * 
     * 这里是一个抽象示例，具体实现可能需要调用 TRON 网络上的某个 API endpoint
     * @param string $contractAddress 合约地址
     * @return array 返回合约的 ABI
     * @throws Exception
     * @throws \IEXBase\TronAPI\Exception\TronException
     */
    private function getContractABI($contractAddress)
    {
        // 使用 TRON 网络 API 或其他方式获取合约的 ABI
        $apiEndpoint = "https://api.trongrid.io/v1/contracts/{$contractAddress}/abi";
        $responseJson = file_get_contents($apiEndpoint);
        $responseData = json_decode($responseJson, true);


        if (!isset($responseData['data'][0]['abi'])) {
            throw new Exception('ABI not found for the given contract address');
        }

        $contractABI = $responseData['data'][0]['abi'];

        return $contractABI;
    }
}




