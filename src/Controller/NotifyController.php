<?php


namespace Ahmedkhd\SyliusPaymobPlugin\Controller;

use Ahmedkhd\SyliusPaymobPlugin\Services\PaymobService;
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

        if(!empty($_GET_PARAMS) && $_GET_PARAMS['success'] == 'true') {
            return $this->redirectToRoute('sylius_shop_order_thank_you');
        }

        $order = $this->paymobService->getPaymentById($_GET_PARAMS['merchant_order_id'])->getOrder();
        return $this->redirectToRoute('sylius_shop_order_show',['tokenValue' => $order->getTokenValue()]);
    }

    public function webhookAction(Request $request): Response
    {
        $paymobResponse = \GuzzleHttp\json_decode($request->getContent());
        $response = false;

        //success payment
        if(
            !empty($paymobResponse) &&
            isset($paymobResponse->obj->is_standalone_payment) &&
            isset($paymobResponse->obj->success) && $paymobResponse->obj->success &&
            isset($paymobResponse->type) && $paymobResponse->type == PaymobService::TRANSACTION_TYPE &&
            isset($paymobResponse->obj->order->paid_amount_cents) &&
            isset($paymobResponse->obj->order->merchant_order_id)
        ) {
            $payment = $this->paymobService->getPaymentById($paymobResponse->obj->order->merchant_order_id);

            $orderAmount = $paymobResponse->obj->order->paid_amount_cents;
            $amount = $payment->getAmount();

            if($orderAmount === $amount) {
                $payment->setDetails(['status'=> 'success', 'message' => "amount: {$amount}"]);
                $order = $this->paymobService->setPaymentState($payment,
                    PaymentInterface::STATE_COMPLETED,
                    OrderPaymentStates::STATE_PAID
                );
                $response = true;
            }
        } else if (isset($paymobResponse->obj->order->merchant_order_id)) {
            $paymentId = $paymobResponse->obj->order->merchant_order_id;
            $payment = $this->paymobService->getPaymentById($paymentId);
            $payment->setDetails(["status"=> "failed", "message"=> "payment_id: {$paymentId}"]);

            # create new payment so user can try to pay again
            $newPayment = clone $payment;
            $newPayment->setState(PaymentInterface::STATE_NEW);
            $payment->getOrder()->addPayment($newPayment);

            $order = $this->paymobService->setPaymentState($payment,
                PaymentInterface::STATE_FAILED,
                OrderPaymentStates::STATE_AWAITING_PAYMENT
            );
        }

        return new Response(\GuzzleHttp\json_encode(['success' => $response]), $response ? 200 : 400);
    }
}
