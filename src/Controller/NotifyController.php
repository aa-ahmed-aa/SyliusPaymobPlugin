<?php


namespace Ahmedkhd\SyliusPaymobPlugin\Controller;

use Ahmedkhd\SyliusPaymobPlugin\Services\PaymobServiceInterface;
use Sylius\Component\Core\OrderPaymentStates;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Payum\Core\Payum;
use Sylius\Component\Core\Model\PaymentInterface;

class NotifyController extends AbstractController
{
    /** @var Payum */
    private $payum;

    /** @var PaymobServiceInterface */
    private $paymobService;

    public function __construct(
        Payum $payum,
        PaymobServiceInterface $paymobService
    ) {
        $this->payum = $payum;
        $this->paymobService = $paymobService;
    }

    public function doAction(Request $request): Response
    {
        $_GET_PARAMS = $request->query->all();

        if(!empty($_GET_PARAMS) &&
            $_GET_PARAMS['success'] == 'true'
        ) {
            $payment = $this->paymobService->getPaymentById($_GET_PARAMS['merchant_order_id']);
            $order = $this->paymobService->setPaymentState($payment,
                PaymentInterface::STATE_COMPLETED,
                OrderPaymentStates::STATE_PAID
            );
            return $this->redirectToRoute('sylius_shop_order_thank_you');
        }

        //fail this and assign new payment
        $payment = $this->paymobService->getPaymentById($_GET_PARAMS['merchant_order_id']);

        $newPayment = clone $payment;
        $newPayment->setState(PaymentInterface::STATE_NEW);
        $payment->getOrder()->addPayment($newPayment);

        $order = $this->paymobService->setPaymentState($payment,
            PaymentInterface::STATE_FAILED,
            OrderPaymentStates::STATE_AWAITING_PAYMENT
        );
        return $this->redirectToRoute('sylius_shop_order_show',['tokenValue' => $order->getTokenValue()]);
    }

    public function webhookAction(Request $request): Response
    {
        return new Response(json_decode($request->getContent()), 200);
    }
}
