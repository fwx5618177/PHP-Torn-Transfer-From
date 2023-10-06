# 【PHP】Torn Transfer from 的实现

在 PHP 中实现 TRON 的 "Transfer from" 功能，需要与 TRON 区块链进行交互。一个常见的方法是使用 TRON 的官方 PHP API 或者其他第三方库。

本文是一个使用 TRONLink 的示例，TRONLink 是 TRON 的官方钱包，允许在网页上与 TRON 区块链交互。

**什么是 TRONLink**:

TRONLink 是 TRON 的官方钱包，允许在网页上与 TRON 区块链交互。TRONLink 有两个版本，分别是 Chrome 插件版和网页版。本文使用的是网页版。

## 2. 实现的基础

1. 安装 [Compoer](https://getcomposer.org/)
2. 安装 tronlink/php-tronlink 依赖

```bash
composer require iexbase/tron-api
```

## 3. 实现的步骤

### 3.1. 实现 Transfer From 功能 - 简单的示例

我们设置发送方的私钥，指定接收方的地址和转账金额，并执行了转账操作, 最后打印出转账结果。

```php
<?php

require 'vendor/autoload.php';

use IEXBase\TronAPI\Tron;

// 初始化 Tron 对象
$tron = new Tron();

// 设置发送方的私钥
$tron->setPrivateKey('your-private-key-here');

// 指定接收方的地址和转账金额
$toAddress = 'TRON-receiver-address';
$amount = 100;  // 单位是 TRX 的最小单位（sun），1 TRX = 1,000,000 sun

// 执行转账操作
try {
    $transaction = $tron->getTransactionBuilder()->sendTrx($toAddress, $amount, $tron->address);
    $signedTransaction = $tron->signTransaction($transaction);
    $result = $tron->sendRawTransaction($signedTransaction);

    // 打印结果
    print_r($result);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

### 3.2. 通过 TRON 的智能合约地址发送 TRX 或 TRC10 代币

```php
<?php

require 'vendor/autoload.php';

use IEXBase\TronAPI\Tron;

class TronTransaction
{
    private $tron;
    private $privateKey;

    public function __construct($privateKey)
    {
        $this->tron = new Tron();
        $this->privateKey = $privateKey;
        $this->tron->setPrivateKey($privateKey);
    }

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

    public function sendToken($contractAddress, $toAddress, $amount)
    {
        try {
            $transaction = $this->tron->getTransactionBuilder()->sendToken($toAddress, $amount, $contractAddress);
            $signedTransaction = $this->tron->signTransaction($transaction);
            $result = $this->tron->sendRawTransaction($signedTransaction);
            return $result;
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }
}

// 创建一个新的 TronTransaction 实例
$privateKey = 'your-private-key-here'; // 替换为的私钥
$tronTransaction = new TronTransaction($privateKey);
$sendAddress = 'send-address';

// 发送 TRX 到指定地址
// 设置接收者地址和发送数量
$trxReceiverAddress = 'receiver-address-for-TRX'; // 替换 'receiver-address-for-TRX' 为实际接收 TRX 的地址
$result = $tronTransaction->sendTRX($trxReceiverAddress, 100); // 指定金额
print_r($result);

// 发送 TRC10 代币到指定地址
// 设置代币的合约地址、接收者地址和发送数量
$tokenReceiverAddress = 'receiver-address-for-Token'; // 替换 'receiver-address-for-Token' 为实际接收代币的地址
$tokenResult = $tronTransaction->sendToken($sendAddress, $tokenReceiverAddress, 100); // 指定金额
print_r($tokenResult);
```

- `receiver-address-for-TRX` 是接收 TRX 的 TRON 地址，是 TRON 区块链网络的原生代币。
- `receiver-address-for-Token` 是接收 TRC10 代币的 TRON 地址，TRC10 是 TRON 区块链上的一种代币标准，类似于 Ethereum 的 ERC20。

### 3.3. 通过 TRON 的智能合约地址发送 TRC20 代币

```php
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
        $this->tron->address
    );

    // 签名交易
    $signedTransaction = $this->tron->signTransaction($transaction['transaction']);

    // 发送已签名的交易
    $response = $this->tron->sendRawTransaction($signedTransaction);

    return $response;
}

// 创建一个新的 TronTransaction 实例
$privateKey = 'your-private-key-here'; // 用自己的私钥替换这里
$tronTransaction = new TronTransaction($privateKey);

// TRC20 合约地址、接收者地址和要发送的代币数量
$contractAddress = 'TRC20-contract-address-here'; // 用实际的 TRC20 合约地址替换这里
$toAddress = 'receiver-address-here'; // 用接收者的 TRON 地址替换这里
$amount = 100; // 要发送的代币数量（注意：数量可能需要根据代币的小数位数进行调整）

// 发送 TRC20 代币
$response = $tronTransaction->sendTRC20($contractAddress, $toAddress, $amount);

// 打印响应
print_r($response);
```

### 3.4. "代理支付"（Gas Fee Sponsorship）或 "交易委托"（Transaction Delegation）

代理支付 (Sponsored Transactions): 允许一个账户（账户 A）支付另一个账户（账户 C）执行交易的能量或 gas 费用。这使得账户 C 能够在没有 TRX 或其他原生代币的情况下执行交易。
场景: 常见于简化用户体验的场景，如 dApps，其中用户可能没有足够的 cryptocurrency 来支付交易费用，或者开发者希望吸引和保留用户，通过支付他们的交易费用来减轻他们的负担。

这种模式在不同的区块链上有不同的实现和名称。例如，在 Ethereum 上，有一种称为 "Gas Station Network"（GSN）的系统，允许用户交易时不支付 gas 费用，而是由其他人或实体支付。

```php
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
            $this->tron->address
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
```

### 3.5. TRC20 的 ABI

详情阅读后续的[ABI]。

在实际情况中，当需要触发一个智能合约时，需要合约的 ABI (Application Binary Interface) 信息来正确编码和解码合约的调用和响应。这可以通过使用 TRON 网络上的相关 API 或其他方式来完成。

更新下内容:

```php
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
```

这只是一个抽象示例，具体实现可能会有所不同。例如，可以使用 TRON 网络上的某个 API 来获取合约的 ABI。

## ABI

ABI (Application Binary Interface) 是用于智能合约和外部应用之间交互的接口。它是一个 JSON 对象，其中包含了智能合约的方法、输入输出参数的类型、参数顺序等信息。通过 ABI，外部应用可以正确地构造交易数据，调用智能合约的特定方法，并解析合约调用的结果。

### ABI 的主要作用：

1. **方法签名映射**：ABI 包含了智能合约的方法签名，帮助外部应用确定如何调用合约的特定方法。

2. **类型和数据转换**：ABI 提供了参数和返回值的类型信息，帮助外部应用进行正确的类型和数据格式转换。

3. **事件定义**：对于 Ethereum 和其他支持事件的区块链，ABI 还包含了合约事件的定义，帮助外部应用解析和处理合约事件。

### 一个 ABI 的简单例子：

```json
[
  {
    "constant": false,
    "inputs": [
      {
        "name": "_value",
        "type": "uint256"
      }
    ],
    "name": "setValue",
    "outputs": [],
    "payable": false,
    "stateMutability": "nonpayable",
    "type": "function"
  },
  {
    "constant": true,
    "inputs": [],
    "name": "getValue",
    "outputs": [
      {
        "name": "",
        "type": "uint256"
      }
    ],
    "payable": false,
    "stateMutability": "view",
    "type": "function"
  }
]
```

在上面的 ABI 示例中，定义了两个方法 `setValue` 和 `getValue`。`setValue` 接受一个 `uint256` 类型的输入参数 `_value`，没有输出。`getValue` 没有输入参数，但返回一个 `uint256` 类型的值。

### 在智能合约交互中使用 ABI：

1. **调用合约方法**：外部应用可以使用 ABI 来构造调用智能合约方法的交易数据。

2. **解析合约调用结果**：当合约方法被调用后，外部应用还可以使用 ABI 来解析方法调用的结果，将其转换为可读的格式。

3. **处理合约事件**：对于支持事件的区块链，外部应用还可以使用 ABI 来解析和处理由智能合约触发的事件。

因此，无论是在 TRON 还是在 Ethereum 等其他区块链上，ABI 都是外部应用和智能合约之间交互的关键部分。在进行合约调用时，应用需要 ABI 来确定如何正确地构造交易数据，并解析合约调用的结果。
