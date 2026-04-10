# AI Development Guidelines - WHMCS Modules (Link Nacional)

This document serves as the strict set of rules and guidelines for **GitHub Copilot** and other Artificial Intelligence assistants contributing to this WHMCS modules repository.

When generating, refactoring, or suggesting code, the AI **MUST** unconditionally adhere to the rules below.

## 1. PHP Standards (Version 8.1+)
The target environment runs PHP 8.1 or higher. The code must be modern, strict, and strongly typed.
* **Strict Typing:** Always use `declare(strict_types=1);` at the top of new files that support namespaces.
* **Return and Parameter Types:** Explicitly declare parameter types and return types in all functions/methods (e.g., `int`, `string`, `array`, `?bool`, `void`).
* **Modern Features:** Utilize PHP 8+ features like Nullsafe Operator (`?->`), Match Expressions (`match()`), Constructor Property Promotion, and Union Types (`int|string`).
* **Clean Syntax:** Prefer `??` (Null Coalescing Operator) and `??=` over long `isset()` blocks.
* **Error Handling:** Catch exceptions with `try...catch (\Throwable $e)` blocks in critical routines involving external APIs or database communication.

## 2. Security and WHMCS Mandatory Standards
* **Direct Access Block:** ALL `.php` files must begin with the following security lock immediately after the `<?php` tag:
  ```php
  if (!defined('WHMCS')) {
      die('This file cannot be accessed directly');
  } ```
Basic Function Naming: Global gateway module functions do not use namespaces to maintain compatibility with the WHMCS activation architecture. They must be prefixed with the module name to avoid global collisions (e.g., modulename_config(), modulename_link()).

Dependency Injection & File Includes: When including native WHMCS files in external routines (like callbacks), safely build paths using the magic constant __DIR__ to traverse back to the root. (e.g., require_once __DIR__ . '/../../../init.php';).

## 3. Database (WHMCS Capsule)
NEVER use obsolete functions like mysql_query(), raw PDO, or old WHMCS functions like select_query(), update_query().

Database interactions MUST use WHMCS\Database\Capsule (based on Laravel Eloquent).

Read Example:
```
PHP
use WHMCS\Database\Capsule;
$clientId = Capsule::table('tblinvoices')->where('id', $invoiceId)->value('userid');
```
Write Example:

PHP
```
Capsule::table('tblinvoices')->where('id', $invoiceId)->update(['status' => 'Unpaid']); ```

## 4. WHMCS APIs and Transactions
Internal Actions: Always use localAPI() to create invoices, add payments, accept orders, etc. Do not manually manipulate billing tables via Capsule if an API or native function exists for that action.

Payment Registration: To register that an invoice was paid and trigger service activation automations, ALWAYS prefer the native function addInvoicePayment($invoiceId, $transId, $amount, $fee, $gatewayName) over localAPI('AddTransaction').

Hooks: When creating Hooks, ensure the exact naming from the WHMCS API is used. If the Hook needs to be aborted (e.g., to prevent automatic cancellation), use the exact return keys required by the documentation (e.g., ['abortCancel' => true]).

## 5. Handling Requests (Hooks and Callbacks)
In callback files (callback/ or check_payment.php), expect to receive data via $_POST, $_GET, or raw JSON Payload (php://input).

Use the following pattern to obtain data safely and agnostically in callbacks:

PHP
```
$request = json_decode(file_get_contents('php://input'), true) ?? $_POST;```

## 6. Logs and Auditing
Always log vital communication events to assist in debugging.

Gateways: Use logTransaction($gatewayName, $requestAndResponseData, $transactionStatus) to record data going to or returning from third-party payment APIs.

System/Hooks: Use the function logActivity("Your descriptive message. ID: {$id}") to log internal customer audits, blocks, or hook behaviors in WHMCS.

## 7. Frontend and Javascript (Gateways)
If you need to render return HTML in the Gateway (modulename_link), wrap JavaScript blocks in an IIFE (Immediately Invoked Function Expression) to shield variables from Ajax reloading (common on the viewinvoice.php WHMCS screen).

Isolation Example:

HTML
```
<script>
(function() {
    const myVar = 123;
    // Protected logic here
})();
</script>```

If it is necessary to use { } brackets in JS scripts inside Smarty template files (.tpl), the entire script must be enveloped in {literal} ... {/literal} tags to prevent compiler Fatal Errors from Smarty.