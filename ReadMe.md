# Tron Transaction

```bash
.
├── READMD.md             # Instruction
├── TronTransaction.php   # Main class
├── TronTransactionTest.php     # Test case
└── useTronTransaction.php      # Example
```

## Install dependency

```bash
composer require iexbase/tron-api
```


## Usage

```php
# Send TRX to address
$result = $tronTransaction->sendTRX('receiver-address-for-TRX', 100);

# Send TRC20 to address
$result = $tronTransaction->sendTRC20('your-send-address-for-TRC20', 'contract-address-for-TRC20', 100);
```

## Test Case

1. Install

```bash
composer require --dev phpunit/phpunit ^9
```

2. Change variable in `TronTransactionTest.php`
3. Run all test case

```bash
./vendor/bin/phpunit TronTransactionTest.php

# Global install phpunit
phpunit TronTransactionTest.php
```

**Note:**
1. Replace 'your-test-private-key-here', 'TRC20-contract-address-here' and 'receiver-address-here' to your own.

