# 【PHP】TRON の "Transfer from" の実装

PHP で TRON の "Transfer from" 機能を実装するには、TRON ブロックチェーンとのインタラクションが必要です。一般的なアプローチは、TRON の公式 PHP API または他のサードパーティのライブラリを使用する方法です。[参照](https://github.com/fwx5618177/PHP-Torn-Transfer-From)

本記事は、ウェブページ上で TRON ブロックチェーンとのインタラクションを可能にする TRON の公式ウォレット、TRONLink を使用した例を提供しています。

**TRONLink とは**:

TRONLink は、ウェブページ上で TRON ブロックチェーンとのインタラクションを可能にする TRON の公式ウォレットです。TRONLink には、Chrome 拡張バージョンとウェブバージョンの 2 つのバージョンがあります。本記事では、ウェブバージョンを使用しています。

## 2. 基本セットアップ

1. [Composer](https://getcomposer.org/)をインストールする
2. tronlink/php-tronlink の依存関係をインストールする

```bash
composer require iexbase/tron-api
```

## 3. 実装手順

### 3.1. Transfer From 機能の実装 - 簡単な例

送信者のプライベートキーを設定し、受信者のアドレスと転送金額を指定して、転送操作を実行します。最後に転送結果を出力します。

```php
<?php

require 'vendor/autoload.php';

use IEXBase\TronAPI\Tron;

// Tronオブジェクトを初期化
$tron = new Tron();

// 送信者のプライベートキーを設定
$tron->setPrivateKey('your-private-key-here');

// 受信者のアドレスと転送金額を指定
$toAddress = 'TRON-receiver-address';
$amount = 100;  // 単位はTRXの最小単位（sun）、1 TRX = 1,000,000 sun

// 転送操作を実行
try {
    $transaction = $tron->getTransactionBuilder()->sendTrx($toAddress, $amount, $tron->address);
    $signedTransaction = $tron->signTransaction($transaction);
    $result = $tron->sendRawTransaction($signedTransaction);

    // 結果を印刷
    print_r($result);
} catch (Exception $e) {
    echo "エラー: " . $e->getMessage();
}
```

### 3.2 TRON のスマートコントラクトアドレスを介して TRX または TRC10 トークンを送信する

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
            return "エラー: " . $e->getMessage();
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
            return "エラー: " . $e->getMessage();
        }
    }
}

// 新しい TronTransaction インスタンスを作成する
$privateKey = 'your-private-key-here'; // あなたのプライベートキーに置き換えてください
$tronTransaction = new TronTransaction($privateKey);
$sendAddress = 'send-address';

// 指定されたアドレスに TRX を送信する
// 受信者のアドレスと送信量を設定する
$trxReceiverAddress = 'receiver-address-for-TRX'; // 'receiver-address-for-TRX' を実際の TRX 受信アドレスに置き換えてください
$result = $tronTransaction->sendTRX($trxReceiverAddress, 100); // 金額を指定する
print_r($result);

// 指定されたアドレスに TRC10 トークンを送信する
// トークンのコントラクトアドレス、受信者のアドレス、送信量を設定する
$tokenReceiverAddress = 'receiver-address-for-Token'; // 'receiver-address-for-Token' を実際のトークン受信アドレスに置き換えてください
$tokenResult = $tronTransaction->sendToken($sendAddress, $tokenReceiverAddress, 100); // 金額を指定する
print_r($tokenResult);
```

- `receiver-address-for-TRX` は TRX を受け取る TRON アドレスで、TRON ブロックチェーンネットワークのネイティブトークンです。
- `receiver-address-for-Token` は TRC10 トークンを受け取る TRON アドレスで、TRC10 は TRON ブロックチェーン上のトークン標準で、Ethereum の ERC20 に似ています。

### 3.3. TRON のスマートコントラクトアドレスを通じて TRC20 トークンを送信する

```php
/**
 * TRC20トークンを送信する
 *
 * @param string $contractAddress TRC20トークンのコントラクトアドレス
 * @param string $toAddress トークンの受信者のTRONアドレス
 * @param int $amount 送信するトークンの数量（トークンの小数桁数に応じて計算され、例：トークンが6桁の小数点を持つ場合、1トークンは1000000として表記する必要があります）
 * @return array 取引の結果を返す
 */
public function sendTRC20($contractAddress, $toAddress, $amount)
{
    // 金額を検証し、負でないことを確認します。金額が0であることは許可されています。
    if ($amount < 0) {
        throw new Exception("金額は負であってはなりません。");
    }

    // スマートコントラクトをトリガーする取引を作成
    // ここで、TRC20コントラクトの `transfer` メソッドを呼び出してトークンを送信します
    // パラメータは、受信者のアドレスと送信数量を含みます
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

    // 取引に署名する
    $signedTransaction = $this->tron->signTransaction($transaction['transaction']);

    // 署名済みの取引を送信する
    $response = $this->tron->sendRawTransaction($signedTransaction);

    return $response;
}

// 新しいTronTransactionインスタンスを作成する
$privateKey = 'your-private-key-here'; // あなたのプライベートキーでこれを置き換えます
$tronTransaction = new TronTransaction($privateKey);

// TRC20コントラクトアドレス、受信者のアドレス、および送信するトークンの数量
$contractAddress = 'TRC20-contract-address-here'; // 実際のTRC20コントラクトアドレスでこれを置き換えます
$toAddress = 'receiver-address-here'; // 受信者のTRONアドレスでこれを置き換えます
$amount = 100; // 送信するトークンの数量（注：数量はトークンの小数桁数に応じて調整する必要があります）

// TRC20トークンを送信する
$response = $tronTransaction->sendTRC20($contractAddress, $toAddress, $amount);

// レスポンスを印刷する
print_r($response);
```

このセクションでは、TRON のスマートコントラクトアドレスを通じて TRC20 トークンを送信する方法について説明します。`sendTRC20`というメソッドが定義され、そのメソッドでは TRC20 トークンのコントラクトアドレス、トークンの受信者の TRON アドレス、送信するトークンの数量というパラメータが必要となります。そして、金額が負でないことを検証します。0 の金額も許可されます。

スマートコントラクトをトリガーする取引を作成し、TRC20 コントラクトの `transfer` メソッドを呼び出してトークンを送信します。取引に署名した後、署名済みの取引を送信し、レスポンスを返します。

プライベートキーを使用して新しい`TronTransaction`インスタンスを作成し、TRC20 コントラクトアドレス、受信者のアドレス、送信するトークンの数量を指定します。`sendTRC20`メソッドを呼び出して取引を実行し、レスポンスを印刷します。

### 3.4. "代理支払い"（ガス料金スポンサーシップ）または "トランザクション委託"

代理支払い（スポンサードトランザクション）: これは、アカウント A がアカウント C の取引のエネルギーまたはガス料金を支払うことを可能にします。これにより、アカウント C は TRX や他のネイティブトークンを持っていなくても取引を実行できます。
シナリオ: ユーザー体験を単純化するシーンでよく見られ、dApps のような場面で、ユーザーが取引費用を支払うのに十分な暗号通貨を持っていない、または開発者がユーザーを引き付けて保持し、取引費用を支払って負担を軽減したい場合などです。

このモードは、異なるブロックチェーンで異なる実装と名前を持っています。例えば、Ethereum には、ユーザーが取引時にガス料金を支払わなくても他の人やエンティティに支払わせる「ガスステーションネットワーク」（GSN）というシステムがあります。

```php
/**
 * TRC20 代理支払い: アカウントAのエネルギーを使用して、アカウントCがアカウントBに送金する
 *
 * @param string $accountA 秘密鍵 - 取引の署名とエネルギー料金の支払いに使用する
 * @param string $accountC 契約アドレス - トークンがこのアドレスから送られる
 * @param string $accountB 受信アドレス - トークンがこのアドレスに送られる
 * @param int $amount 送金額
 * @return array 取引結果を返す
 */
public function sendByDelegation($accountA, $accountC, $accountB, $amount)
{
    try {
        // アカウントAの秘密鍵を設定
        $this->tron->setPrivateKey($accountA);

        // アカウントCの契約送金をトリガする取引を作成
        // 注: ここでは適切な契約方法とパラメータが必要です
        $transaction = $this->tron->getTransactionBuilder()->triggerSmartContract(
            $accountC,
            'transfer(address,address,uint256)',  // ここでは具体的な契約方法とパラメータのフォーマットが必要です
            '0',
            [
                ['type' => 'address', 'value' => $accountC],
                ['type' => 'address', 'value' => $accountB],
                ['type' => 'uint256', 'value' => $amount]
            ],
            $this->tron->address
        );

        // 取引に署名
        $signedTransaction = $this->tron->signTransaction($transaction['transaction']);

        // 署名済みの取引を送信
        $result = $this->tron->sendRawTransaction($signedTransaction);

        return $result;
    } catch (Exception $e) {
        return "Error: " . $e->getMessage();
    }
}
```

このコードスニペットでは、アカウント A のエネルギーを使用して、アカウント C がアカウント B にトークンを送る TRC20 代理支払いを実行する方法を示しています。これは、TRX や他のネイティブトークンを持っていないユーザーや、dApp プロバイダーがユーザーの取引料を支払って支援したい場合に役立ちます。これは、ユーザーがガス料金を支払

わずに他のパーティに支払わせる Ethereum ブロックチェーンのガスステーションネットワークに似ています。

### 3.5. TRC20 の ABI

詳細は、以下の[ABI]を参照してください。

現実的なシチュエーションでは、スマートコントラクトをトリガーする際に、コントラクトの ABI（Application Binary Interface）情報が必要になり、コントラクトの呼び出しと応答を正確にエンコードおよびデコードすることができます。これは、TRON ネットワーク上の関連 API または他の方法を使用して完了することができます。

以下の内容を更新:

```php
    /**
     * TRC20 トークンを送信する
     *
     * @param string $contractAddress TRC20 トークンのコントラクトアドレス
     * @param string $toAddress トークンを受け取る TRON アドレス
     * @param int $amount 送信するトークンの数量（トークンの小数桁に応じて計算され、例えば、トークンが6桁の小数を持つ場合、1 トークンは 1000000 として記述する必要があります）
     * @return array トランザクションの結果を返す
     */
    public function sendTRC20($contractAddress, $toAddress, $amount)
    {
        // 金額を検証して、負でないことを確認します。金額が0であることは許可されます。
        if ($amount < 0) {
            throw new Exception("金額は負であってはなりません。");
        }

        // コントラクトの ABI を取得する
        $contractABI = $this->getContractABI($contractAddress);

        // スマートコントラクトをトリガーするトランザクションを作成します
        // ここでは TRC20 コントラクトの `transfer` メソッドを呼び出してトークンを送信します
        // パラメータには受信者のアドレスと送信する数量が含まれます
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
            $contractABI   // ABI を呼び出しに追加
        );

        // トランザクションに署名する
        $signedTransaction = $this->tron->signTransaction($transaction['transaction']);

        // 署名されたトランザクションを送信する
        $response = $this->tron->sendRawTransaction($signedTransaction);

        return $response;
    }

     /**
     * TRC20 代理支払い：アカウントAのエネルギーを使用して、アカウントCがアカウントBに送金する
     *
     * @param string $accountA 私的鍵 - トランザクションに署名してエネルギー費を支払うために使用されます
     * @param string $accountC コントラクトアドレス - このアドレスからトークンが送られます
     * @param string $accountB 受信アドレス - トークンがこのアドレスに送られます
     * @param int $amount 送金額
     * @return array トランザクションの結果を返す
     */
    public function sendByDelegation($accountA, $accountC, $accountB, $amount)
    {
        try {
            // アカウントAの私的鍵を設定する
            $this->tron->setPrivateKey($accountA);

            // コントラクトの ABI を取得する
            $contractABI = $this->getContractABI($accountC);

            // アカウントCのコントラクト送金をトリガーするトラン

ザクションを作成する
            // 注意：ここで適切なコントラクトメソッドとパラメータが必要です
            $transaction = $this->tron->getTransactionBuilder()->triggerSmartContract(
                $accountC,
                'transfer(address,address,uint256)',  // ここで具体的なコントラクトメソッドとパラメータフォーマットが必要です
                '0',
                [
                    ['type' => 'address', 'value' => $accountC],
                    ['type' => 'address', 'value' => $accountB],
                    ['type' => 'uint256', 'value' => $amount]
                ],
                $this->tron->address,
                $contractABI  // メソッド呼び出しに ABI 情報を追加
            );

            // トランザクションに署名する
            $signedTransaction = $this->tron->signTransaction($transaction['transaction']);

            // 署名されたトランザクションを送信する
            $result = $this->tron->sendRawTransaction($signedTransaction);

            return $result;
        } catch (Exception $e) {
            return "エラー: " . $e->getMessage();
        }
    }

    /**
     * コントラクトの ABI を取得する
     *
     * これは抽象的な例です。実際の実装では、TRON ネットワーク上の特定の API エンドポイントを呼び出す必要があるかもしれません
     * @param string $contractAddress コントラクトアドレス
     * @return array コントラクトの ABI を返します
     * @throws Exception
     * @throws \IEXBase\TronAPI\Exception\TronException
     */
    private function getContractABI($contractAddress)
    {
        // TRON ネットワーク API または他の方法を使用してコントラクトの ABI を取得する
        $apiEndpoint = "https://api.trongrid.io/v1/contracts/{$contractAddress}/abi";
        $responseJson = file_get_contents($apiEndpoint);
        $responseData = json_decode($responseJson, true);

        if (!isset($responseData['data'][0]['abi'])) {
            throw new Exception('指定されたコントラクトアドレスの ABI が見つかりません');
        }

        $contractABI = $responseData['data'][0]['abi'];

        return $contractABI;
    }
```

これは抽象的な例です。実際の実装は異なる場合があります。例えば、TRON ネットワーク上の特定の API を使用してコントラクトの ABI を取得することができます。

## ABI

ABI（Application Binary Interface、アプリケーションバイナリインターフェース）は、スマートコントラクトと外部アプリケーション間のインタラクションのためのインターフェースです。これは JSON オブジェクトであり、スマートコントラクトのメソッド、入力/出力パラメータのタイプ、パラメータの順序などの情報が含まれています。ABI を使用すると、外部アプリケーションはトランザクションデータを正確に構築し、スマートコントラクトの特定のメソッドを呼び出し、コントラクトの呼び出し結果を解析することができます。

### ABI の主な役割：

1. **メソッド署名のマッピング**：ABI にはスマートコントラクトのメソッド署名が含まれており、外部アプリケーションがコントラクトの特定のメソッドをどのように呼び出すかを特定するのに役立ちます。

2. **タイプとデータの変換**：ABI はパラメータと戻り値のタイプ情報を提供し、外部アプリケーションが正確なタイプとデータ形式の変換を行うのに役立ちます。

3. **イベントの定義**：Ethereum や他のイベントをサポートするブロックチェーンに対して、ABI はコントラクトイベントの定義も含んでおり、外部アプリケーションがコントラクトイベントを解析し処理するのを助けます。

### ABI の簡単な例：

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

上記の ABI の例では、`setValue`と`getValue`という二つのメソッドが定義されています。`setValue`は`uint256`型の入力パラメータ`_value`を受け取り、出力はありません。`getValue`は入力パラメータがなく、`uint256`型の値を返します。

### スマートコントラクトのインタラクションで ABI を使用する：

1. **コントラクトメソッドの呼び出し**：外部アプリケーションは ABI を使用して、スマートコントラクトメソッドを呼び出すためのトランザクションデータを構築できます。

2. **コントラクト呼び出し結果の解析**：コントラクトメソッドが呼び出された後、外部アプリケーションは ABI を使用してメソッド呼び出しの結果を解析し、可読な形式に変換できます。

3. **コントラクトイベントの処理**：イベントをサポートするブロックチェーンに対して、外部アプリケーションは ABI を使用して、スマートコントラクトによってトリガーされたイベントを解析および処理することができます。

したがって、TRON や Ethereum などの他のブロックチェーンにおいても、ABI は外部アプリケーションとスマートコントラクト間のインタラクションの重要な部分です。コントラクトを呼び出す際に、アプリケーションはトランザクションデータを正しく構築する方法、およびコントラクト呼び出しの結果を解析する方法を特定するために ABI が必要です。
