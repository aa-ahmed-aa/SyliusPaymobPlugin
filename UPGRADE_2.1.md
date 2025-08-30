# SyliusPaymobPlugin Upgrade to Sylius 2.1

This document explains the major changes made to upgrade the SyliusPaymobPlugin from Sylius 1.8/1.9 to Sylius 2.1.

## Major Changes

### 1. Payment System Architecture

**Before (Sylius 1.x):** The plugin used the old Payum-based payment system with:
- `Payum\Core\GatewayFactory`
- `Payum\Core\Action\ActionInterface`
- Payum gateway configuration

**After (Sylius 2.1):** The plugin now uses the new Payment Request system with:
- `PaymentRequest` objects
- `Command` and `CommandHandler` pattern
- Symfony Messenger for async processing
- HTTP Response Providers for UI handling

### 2. New File Structure

```
src/
├── Command/
│   └── CapturePaymentCommand.php          # New: Payment command object
├── CommandHandler/
│   └── CapturePaymentCommandHandler.php   # New: Processes payment commands
├── Factory/
│   └── PaymobGatewayFactory.php          # New: Creates gateway instances
├── PaymentRequest/
│   └── CapturePaymentRequest.php         # New: Payment request object
├── Provider/
│   ├── CaptureCommandProvider.php        # New: Provides commands for requests
│   └── CaptureHttpResponseProvider.php   # New: Handles UI responses
├── Payum/                                # Old: Payum-based implementation
│   ├── Action/
│   ├── PaymobGatewayFactory.php
│   └── SyliusApi.php
└── Services/
    └── PaymobService.php                 # Updated: Core business logic
```

### 3. Key Components

#### PaymentRequest
- Represents a payment action request
- Contains payment data and gateway configuration
- Implements the new Sylius 2.1 payment request interface

#### Command/CommandHandler Pattern
- `CapturePaymentCommand`: Data transfer object for capture requests
- `CapturePaymentCommandHandler`: Processes the command using Symfony Messenger
- Enables async processing and better separation of concerns

#### Gateway Factory
- `PaymobGatewayFactory`: Creates and validates gateway configurations
- Replaces the old Payum gateway factory
- Integrates with Sylius 2.1 payment system

#### Response Providers
- `CaptureHttpResponseProvider`: Handles UI responses for payment actions
- Can redirect to external payment pages or render templates
- Supports both API and UI-based payment flows

### 4. Configuration Changes

#### Old Configuration (Sylius 1.x)
```yaml
# services.yml
ahmedkhd.sylius_paymob_plugin.paymob:
  class: Payum\Core\Bridge\Symfony\Builder\GatewayFactoryBuilder
  arguments: [Ahmedkhd\SyliusPaymobPlugin\Payum\PaymobGatewayFactory]
  tags:
    - { name: payum.gateway_factory_builder, factory: paymob }
```

#### New Configuration (Sylius 2.1)
```yaml
# services.yml
ahmedkhd.sylius_paymob_plugin.gateway_factory:
  class: Ahmedkhd\SyliusPaymobPlugin\Factory\PaymobGatewayFactory
  tags:
    - { name: sylius.payment.gateway_factory, type: paymob }

ahmedkhd.sylius_paymob_plugin.provider.payment_request.command.capture:
  class: Ahmedkhd\SyliusPaymobPlugin\Provider\CaptureCommandProvider
  tags:
    - name: sylius.payment_request.provider.command
      gateway_factory: paymob
      action: capture
```

### 5. Dependencies

#### Removed
- `payum/payum` (Payum bundle)
- Old Payum action classes

#### Added
- `symfony/messenger` (for async command processing)
- New payment request system classes

### 6. Migration Steps

1. **Update composer.json**
   - Change Sylius version requirement to `^2.1`
   - Update PHP requirement to `^8.1`
   - Add `symfony/messenger` dependency

2. **Replace Payum Implementation**
   - Remove old Payum action classes
   - Implement new Payment Request system
   - Update service configuration

3. **Update Gateway Configuration**
   - Use new gateway factory pattern
   - Configure payment request providers
   - Set up command handlers

4. **Test Payment Flow**
   - Verify payment capture works
   - Test UI responses
   - Check async processing

### 7. Benefits of the New System

- **Better Performance**: Async processing with Symfony Messenger
- **Cleaner Architecture**: Separation of concerns with Command/Handler pattern
- **Modern Standards**: Uses latest Symfony and Sylius patterns
- **Easier Testing**: Better dependency injection and mocking
- **Future Proof**: Built on Sylius 2.1 architecture

### 8. Backward Compatibility

**Note**: This upgrade is **NOT** backward compatible with Sylius 1.x. The new plugin:
- Requires Sylius 2.1+
- Uses completely different payment processing architecture
- May require updates to custom payment logic

### 9. Next Steps

1. Test the plugin in a Sylius 2.1 environment
2. Implement proper error handling and logging
3. Add comprehensive test coverage
4. Document any custom payment logic requirements
5. Consider adding more payment actions (refund, cancel, etc.)

## Support

For issues or questions about the upgrade, please refer to:
- [Sylius 2.1 Documentation](https://docs.sylius.com/)
- [Payment Request System Guide](https://docs.sylius.com/the-customization-guide/customizing-payments/how-to-integrate-a-payment-gateway-as-a-plugin)
- [Sylius Migration Guides](https://github.com/Sylius/Sylius/blob/2.1/CHANGELOG-2.0.md)
