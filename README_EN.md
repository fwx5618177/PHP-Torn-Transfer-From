# 【PHP】Implementation of TRON "Transfer from"

Implementing TRON's "Transfer from" functionality in PHP requires interaction with the TRON blockchain. A common approach involves using TRON’s official PHP API or other third-party libraries.[Reference](https://github.com/fwx5618177/PHP-Torn-Transfer-From)

This article provides an example using TRONLink, the official wallet of TRON that enables interaction with the TRON blockchain on web pages.

**What is TRONLink**:

TRONLink is the official wallet of TRON, facilitating interactions with the TRON blockchain on web pages. There are two versions of TRONLink: a Chrome extension version and a web version. This article uses the web version.

## 2. Basic Setup

1. Install [Composer](https://getcomposer.org/)
2. Install tronlink/php-tronlink dependency

```bash
composer require iexbase/tron-api
```

## 3. Implementation Steps

### 3.1. Implementing Transfer From Functionality - A Simple Example

We set the sender's private key, specify the recipient's address and transfer amount, execute the transfer operation, and finally print the transfer result.

```php
<?php

require 'vendor/autoload.php';

use IEXBase\TronAPI\Tron;

// Initialize Tron object
$tron = new Tron();

// Set the sender's private key
$tron->setPrivateKey('your-private-key-here');

// Specify the recipient's address and transfer amount
$toAddress = 'TRON-receiver-address';
$amount = 100;  // The unit is the smallest unit of TRX (sun), 1 TRX = 1,000,000 sun

// Execute the transfer operation
try {
    $transaction = $tron->getTransactionBuilder()->sendTrx($toAddress, $amount, $tron->address);
    $signedTransaction = $tron->signTransaction($transaction);
    $result = $tron->sendRawTransaction($signedTransaction);

    // Print the result
    print_r($result);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

### 3.2 Sending TRX or TRC10 Tokens via a TRON Smart Contract Address

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

// Create a new TronTransaction instance
$privateKey = 'your-private-key-here'; // Replace with your private key
$tronTransaction = new TronTransaction($privateKey);
$sendAddress = 'send-address';

// Send TRX to a specific address
// Set the recipient's address and the sending amount
$trxReceiverAddress = 'receiver-address-for-TRX'; // Replace 'receiver-address-for-TRX' with the actual TRX receiving address
$result = $tronTransaction->sendTRX($trxReceiverAddress, 100); // Specify the amount
print_r($result);

// Send TRC10 tokens to a specific address
// Set the token's contract address, the recipient's address, and the sending amount
$tokenReceiverAddress = 'receiver-address-for-Token'; // Replace 'receiver-address-for-Token' with the actual token receiving address
$tokenResult = $tronTransaction->sendToken($sendAddress, $tokenReceiverAddress, 100); // Specify the amount
print_r($tokenResult);
```

- `receiver-address-for-TRX` is the TRON address for receiving TRX, which is the native cryptocurrency of the TRON blockchain network.
- `receiver-address-for-Token` is the TRON address for receiving TRC10 tokens. TRC10 is a token standard on the TRON blockchain, similar to Ethereum's ERC20.

### 3.3 Sending TRC20 Tokens Through TRON’s Smart Contract Address

```php
/**
 * Send TRC20 Tokens
 *
 * @param string $contractAddress The contract address of the TRC20 token
 * @param string $toAddress The TRON address to receive the tokens
 * @param int $amount The amount of tokens to send (calculated based on the token's decimal places, e.g., 1 token might need to be written as 1000000 if the token has 6 decimal places)
 * @return array Returns the result of the transaction
 */
public function sendTRC20($contractAddress, $toAddress, $amount)
{
    // Validate the amount to ensure it's not negative. Zero amount is allowed.
    if ($amount < 0) {
        throw new Exception("The amount should not be negative.");
    }

    // Create a transaction to trigger the smart contract
    // Here, the ‘transfer’ method of the TRC20 contract is called to send tokens
    // The parameters include the receiver’s address and the amount to send
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

    // Sign the transaction
    $signedTransaction = $this->tron->signTransaction($transaction['transaction']);

    // Send the signed transaction
    $response = $this->tron->sendRawTransaction($signedTransaction);

    return $response;
}

// Create a new TronTransaction instance
$privateKey = 'your-private-key-here'; // Replace with your private key
$tronTransaction = new TronTransaction($privateKey);

// TRC20 contract address, receiver address, and amount of tokens to send
$contractAddress = 'TRC20-contract-address-here'; // Replace with the actual TRC20 contract address
$toAddress = 'receiver-address-here'; // Replace with the receiver’s TRON address
$amount = 100; // The amount of tokens to send (note: this may need to be adjusted according to the token’s decimal places)

// Send TRC20 tokens
$response = $tronTransaction->sendTRC20($contractAddress, $toAddress, $amount);

// Print the response
print_r($response);
```

In this section, a method for sending TRC20 tokens using a smart contract on the TRON blockchain is described. The method `sendTRC20` is defined to carry out this operation. The contract address of the TRC20 token, the recipient's TRON address, and the amount of tokens to be sent are the parameters needed. The amount is validated to ensure it is not negative. Zero amount is also acceptable.

A transaction is created to trigger the smart contract, and the `transfer` method of the TRC20 contract is specifically invoked to send the tokens. After signing the transaction, it is then sent, and the response is returned.

A new instance of `TronTransaction` is created, and the private key is set. The TRC20 contract address, the receiver's address, and the amount of tokens to send are specified before calling the `sendTRC20` method to execute the transaction. The response is then printed out.

### 3.4. "Proxy Pay" (Gas Fee Sponsorship) or "Transaction Delegation"

Proxy Pay (Sponsored Transactions): This allows one account (Account A) to pay for the energy or gas fees of another account (Account C) for executing transactions. As a result, Account C can carry out transactions without having TRX or other native tokens.
Scenario: This is common in scenarios that aim to simplify user experience, such as in dApps, where users might not have enough cryptocurrency to pay for transaction fees, or developers want to attract and retain users by alleviating their burden by paying for their transaction fees.

This mode has different implementations and names on different blockchains. For instance, on Ethereum, there's a system called "Gas Station Network" (GSN) that allows users not to pay gas fees during transactions, as it's covered by others or entities.

```php
/**
 * TRC20 Proxy Pay: Uses Account A's energy to allow Account C to transfer to Account B
 *
 * @param string $accountA Private Key - Used for signing the transaction and paying energy costs
 * @param string $accountC Contract Address - Tokens will be transferred from this address
 * @param string $accountB Receiving Address - Tokens will be transferred to this address
 * @param int $amount Transfer Amount
 * @return array Returns transaction results
 */
public function sendByDelegation($accountA, $accountC, $accountB, $amount)
{
    try {
        // Set the private key of Account A
        $this->tron->setPrivateKey($accountA);

        // Create a transaction that triggers the transfer of Account C's contract
        // Note: Appropriate contract methods and parameters are needed here
        $transaction = $this->tron->getTransactionBuilder()->triggerSmartContract(
            $accountC,
            'transfer(address,address,uint256)',  // Specific contract methods and parameter formats are needed here
            '0',
            [
                ['type' => 'address', 'value' => $accountC],
                ['type' => 'address', 'value' => $accountB],
                ['type' => 'uint256', 'value' => $amount]
            ],
            $this->tron->address
        );

        // Sign the transaction
        $signedTransaction = $this->tron->signTransaction($transaction['transaction']);

        // Send the signed transaction
        $result = $this->tron->sendRawTransaction($signedTransaction);

        return $result;
    } catch (Exception $e) {
        return "Error: " . $e->getMessage();
    }
}
```

In this section, a PHP example is provided, demonstrating how to use one account's energy to pay for the transaction fees of another account on the TRON blockchain, facilitating users without enough cryptocurrency or those who the dApp providers want to sponsor by paying their transaction fees. This form of transaction is likened to the Gas Station Network on the Ethereum blockchain where users' gas fees can be sponsored by another party.

### 3.5. TRC20 ABI

For further details, refer to the following [ABI] section.

In practice, when triggering a smart contract, the contract's ABI (Application Binary Interface) information is required to correctly encode and decode contract calls and responses. This can be accomplished using related APIs on the TRON network or other means.

Updated content below:

```php
    /**
     * Send TRC20 Tokens
     *
     * @param string $contractAddress The contract address of the TRC20 token
     * @param string $toAddress The TRON address to receive the tokens
     * @param int $amount The amount of tokens to send (calculated according to the token's decimal places, e.g., 1 token may need to be written as 1000000 if the token has 6 decimal places)
     * @return array Returns the transaction result
     */
    public function sendTRC20($contractAddress, $toAddress, $amount)
    {
        // Validate the amount to ensure it's not negative. An amount of 0 is permissible.
        if ($amount < 0) {
            throw new Exception("The amount should not be negative.");
        }

        // Obtain the contract's ABI
        $contractABI = $this->getContractABI($contractAddress);

        // Create a transaction that triggers the smart contract
        // Here, the `transfer` method of the TRC20 contract is called to send tokens
        // Parameters include the recipient's address and the amount to send
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
            $contractABI  // Pass ABI in the call
        );

        // Sign the transaction
        $signedTransaction = $this->tron->signTransaction($transaction['transaction']);

        // Send the signed transaction
        $response = $this->tron->sendRawTransaction($signedTransaction);

        return $response;
    }

    /**
     * TRC20 Delegate Pay: Use Account A's energy to allow Account C to transfer to Account B
     *
     * @param string $accountA Private key - used for signing transactions and paying energy fees
     * @param string $accountC Contract address - tokens will be transferred from this address
     * @param string $accountB Receiving address - tokens will be transferred to this address
     * @param int $amount Transfer amount
     * @return array Returns the transaction result
     */
    public function sendByDelegation($accountA, $accountC, $accountB, $amount)
    {
        try {
            // Set Account A's private key
            $this->tron->setPrivateKey($accountA);

            // Obtain the contract's ABI
            $contractABI = $this->getContractABI($accountC);

            // Create a transaction that triggers Account C's contract transfer
            // Note: Proper contract method and parameters are needed here
            $transaction = $this->tron->getTransactionBuilder()->triggerSmartContract(
                $accountC,
                'transfer(address,address,uint256)',  // Specific contract method and parameter format needed here
                '0',
                [
                    ['type' => 'address', 'value' => $accountC],
                    ['type' => 'address', 'value' => $accountB],
                    ['type' => 'uint256', 'value' => $amount]
                ],
                $this->tron->address,
                $contractABI  // Add ABI information to method call
            );

            // Sign the transaction
            $signedTransaction = $this->tron->signTransaction($transaction['transaction']);

            // Send the signed transaction
            $result = $this->tron->sendRawTransaction($signedTransaction);

            return $result;
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Get Contract's ABI
     *
     * This is an abstract example; actual implementation may require calling a specific API endpoint on the TRON network
     * @param string $contractAddress Contract address
     * @return array Returns the contract's ABI
     * @throws Exception
     * @throws \IEXBase\TronAPI\Exception\TronException
     */
    private function getContractABI($contractAddress)
    {
        // Use TRON network API or other means to obtain the contract's ABI
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

This is just an abstract example, and the actual implementation might differ. For instance, a specific API on the TRON network could be used to retrieve the contract's ABI.

## ABI

The ABI (Application Binary Interface) serves as the interface for interaction between smart contracts and external applications. It is represented as a JSON object, containing detailed information about the smart contract's methods, the types of input/output parameters, their order, and so forth. With the ABI, external applications can accurately construct transaction data, invoke specific methods of the smart contract, and parse the results of such calls.

### Key Functions of ABI:

1. **Method Signature Mapping**: The ABI includes the method signatures of the smart contract, assisting external applications in determining how to call specific contract methods.

2. **Type and Data Conversion**: The ABI provides type information for parameters and return values, enabling external applications to carry out accurate type and data format conversions.

3. **Event Definitions**: For blockchains like Ethereum that support events, the ABI also encapsulates the definitions of contract events, aiding external applications in parsing and handling these events.

### A Simple Example of ABI:

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

In the above example of an ABI, two methods `setValue` and `getValue` are defined. `setValue` accepts a `uint256` type input parameter `_value` and has no output. `getValue`, on the other hand, takes no input parameters but returns a value of type `uint256`.

### Using ABI in Smart Contract Interactions:

1. **Invoking Contract Methods**: External applications can utilize the ABI to build transaction data for calling smart contract methods.

2. **Parsing Contract Call Results**: After a contract method is invoked, external applications can also employ the ABI to parse the results of the method call, converting them into a readable format.

3. **Handling Contract Events**: For blockchains that support events, external applications can leverage the ABI to parse and manage events triggered by smart contracts.

Hence, whether on TRON or other blockchains like Ethereum, the ABI is a critical component of the interaction between external applications and smart contracts. Applications require the ABI to determine how to correctly construct transaction data and to parse the results of contract calls.
