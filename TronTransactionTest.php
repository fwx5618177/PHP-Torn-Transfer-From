<?php

use PHPUnit\Framework\TestCase;

// 导入 TronTransaction 类
require 'TronTransaction.php';

class TronTransactionTest extends TestCase
{
    private $tronTransaction;

    protected function setUp(): void
    {
        // 用一个测试私钥初始化 TronTransaction
        // 注意：这应该是一个用于测试的私钥，不要在测试中使用真实的私钥
        $this->tronTransaction = new TronTransaction('your-test-private-key-here');
    }

    /**
     * 测试 sendTRX 方法
     */
    public function testSendTRX()
    {
        // 使用一个测试接收地址和数量来测试 sendTRX 方法
        // 这里的 'test-receiver-address' 应该是一个有效的 TRON 地址，但用于测试目的
        $result = $this->tronTransaction->sendTRX('test-receiver-address', 100);
        
        // 在这里我们简单地检查返回的结果是否是一个数组，具体的测试断言需要根据实际情况编写
        $this->assertIsArray($result);
    }

    /**
     * 测试 sendToken 方法
     */
    public function testSendToken()
    {
        // 使用一个测试合约地址、接收地址和数量来测试 sendToken 方法
        $result = $this->tronTransaction->sendToken('test-contract-address', 'test-receiver-address', 100);

        // 同样，这里我们简单地检查返回的结果是否是一个数组
        $this->assertIsArray($result);
    }

    /**
     * 测试 sendTRC20 方法
     * @dataProvider amountProvider
     */
    public function testSendTRC20($amount, $expected)
    {
        // 测试 sendTRC20 方法，这里是一个例子，你需要根据实际情况调整
        $contractAddress = 'TRC20-contract-address-here';
        $toAddress = 'receiver-address-here';

        if ($expected === 'exception') {
            $this->expectException(Exception::class);
        }

        $result = $this->tronTransaction->sendTRC20($contractAddress, $toAddress, $amount);

        if ($expected !== 'exception') {
            // 在这里检查你期望的其他结果，例如交易是否成功，返回的结果是否符合预期等
            $this->assertIsArray($result);
        }
    }

    /**
     * 金额提供器
     * 测试 sendTRC20 方法时使用的金额数据源
     * 
     * @return array
     */
    public function amountProvider()
    {
        return [
            'zero amount' => [0, 'success'],
            'negative amount' => [-1, 'exception'],
            'small amount' => [0.01, 'success'],
            'large amount' => [1000000, 'success'],
        ];
    }
}
