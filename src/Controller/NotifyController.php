<?php

declare(strict_types=1);

namespace Ahmedkhd\SyliusPaymobPlugin\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ahmedkhd\SyliusPaymobPlugin\Service\PaymobServiceInterface;
use Sylius\Component\Order\OrderPaymentStates;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Sylius\Component\Payment\Model\PaymentRequestInterface;

class NotifyController
{
    /** @var PaymobServiceInterface */
    private $paymobService;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        PaymobServiceInterface $paymobService,
        RouterInterface $router
    ) {
        $this->paymobService = $paymobService;
        $this->router = $router;
    }

    /**
     * Handle Paymob's transaction response callback (GET request)
     * This is called when user returns from Paymob iframe
     */
    // public function doAction(Request $request): Response
    // {
    //     $_GET_PARAMS = $request->query->all();

    //     if(!empty($_GET_PARAMS) && $_GET_PARAMS['success'] == 'true') {
    //         return new RedirectResponse($this->router->generate('sylius_shop_order_thank_you'));
    //     }

    //     $order = $this->paymobService->findOrderByOrderNumber($_GET_PARAMS['merchant_order_id']);
    //     return new RedirectResponse($this->router->generate('sylius_shop_order_show',['tokenValue' => $order->getTokenValue()]));
    // }
    
    // /**
    //  * Handle Paymob's transaction processed webhook (POST request)
    //  * This is called by Paymob server to notify about payment status
    //  */
    // public function webhookAction(Request $request): Response
    // {
    //     $paymobResponse = \GuzzleHttp\json_decode($request->getContent());
    //     $response = false;

    //     //success payment
    //     if(
    //         !empty($paymobResponse) &&
    //         isset($paymobResponse->obj->is_standalone_payment) &&
    //         isset($paymobResponse->obj->success) && $paymobResponse->obj->success &&
    //         isset($paymobResponse->type) && $paymobResponse->type == PaymobServiceInterface::TRANSACTION_TYPE &&
    //         isset($paymobResponse->obj->order->paid_amount_cents) &&
    //         isset($paymobResponse->obj->order->merchant_order_id)
    //     ) {
    //         $order = $this->paymobService->getOrderByOrderNumber($paymobResponse->obj->order->merchant_order_id);
    //         $paymentRequest = $order->getPayments()->last()->getPaymentRequests()->last();
            
    //         $orderAmount = $paymobResponse->obj->order->paid_amount_cents;
    //         $amount = $paymentRequest->getAmount();

    //         if($orderAmount === $amount) {
    //             $paymentRequest->setPayload(['status'=> 'success', 'message' => "amount: {$amount}"]);
    //             $paymentRequest->setResponseData($paymobResponse);
    //             $this->paymobService->setPaymentRequestState($paymentRequest,
    //                 PaymentRequestInterface::STATE_COMPLETED,
    //                 OrderPaymentStates::STATE_PAID
    //             );
    //             $response = true;
    //         }
    //     } else if (isset($paymobResponse->obj->order->merchant_order_id)) {
    //         $paymentId = $paymobResponse->obj->order->merchant_order_id;
    //         $paymentRequest = $this->paymobService->getPaymentRequestById($paymentId);
    //         $paymentRequest->setPayload(["status"=> "failed", "message"=> "payment_id: {$paymentId}"]);
    //         $paymentRequest->setResponseData($paymobResponse);

    //         # create new payment so user can try to pay again
    //         $newPaymentRequest = clone $paymentRequest;
    //         $newPaymentRequest->setState(PaymentRequestInterface::STATE_NEW);
    //         $paymentRequest->getOrder()->addPaymentRequest($newPaymentRequest);

    //         $this->paymobService->setPaymentRequestState($paymentRequest,
    //             PaymentRequestInterface::STATE_FAILED,
    //             OrderPaymentStates::STATE_AWAITING_PAYMENT
    //         );
    //     }

    //     return new Response(\GuzzleHttp\json_encode(['success' => $response]), $response ? 200 : 400);
    // }
}
