<?php


namespace Ahmedkhd\SyliusPaymobPlugin\Controller;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderPaymentStates;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Payum\Core\Payum;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotifyController extends AbstractController
{
    /** @var EntityRepository */
    private $paymentRepository;

    /** @var Payum */
    private $payum;

    public function __construct(
        EntityRepository $paymentRepository,
        Payum $payum
    ) {
        $this->paymentRepository = $paymentRepository;
        $this->payum = $payum;
    }

    public function doAction(Request $request): Response
    {
        $_GET_PARAMS = $request->query->all();

        if(
            $request->isMethod('GET') &&
            !empty($_GET_PARAMS) &&
            $_GET_PARAMS['success'] == 'false'
        ){
            //fail this and assign new payment
            $payment = $this->getPaymentById($_GET_PARAMS['merchant_order_id']);

            $newPayment = clone $payment;
            $newPayment->setState(PaymentInterface::STATE_NEW);
            $payment->getOrder()->addPayment($newPayment);

            $order = $this->setPaymentState($payment,
                PaymentInterface::STATE_FAILED,
                OrderPaymentStates::STATE_AWAITING_PAYMENT
                );
            return $this->redirectToRoute('sylius_shop_order_show',['tokenValue' => $order->getTokenValue()]);
        }

        if(
            $request->isMethod('GET') &&
            !empty($_GET_PARAMS) &&
            $_GET_PARAMS['success'] == 'true'
        ) {
            $payment = $this->getPaymentById($_GET_PARAMS['merchant_order_id']);
            $order = $this->setPaymentState($payment,
                PaymentInterface::STATE_COMPLETED,
                OrderPaymentStates::STATE_PAID
            );
            return $this->redirectToRoute('sylius_shop_order_thank_you');
        }

        throw new NotFoundHttpException();
    }

    private function setPaymentState($payment, $paymentState, $orderPaymentState)
    {
        /** @var OrderInterface $order */
        $order = $payment->getOrder();

        $payment->setState($paymentState);
        $order->setPaymentState($orderPaymentState);
        $this->flushPaymentAndOrder($payment, $order);

        return $order;
    }

    public function flushPaymentAndOrder($payment, $order)
    {
        $em = $this->get('doctrine.orm.default_entity_manager');
        $em->persist($payment);
        $em->persist($order);
        $em->flush();
    }
    /**
     * @param $payment_id
     * @return PaymentInterface
     */
    private function getPaymentById($payment_id): PaymentInterface
    {
        /**@var $payment PaymentInterface|null */
        $payment = $this->paymentRepository->find($payment_id);
        if (null === $payment OR $payment->getState() !== PaymentInterface::STATE_NEW) {
            throw new NotFoundHttpException('Order not have available payment');
        }
        return $payment;
    }
}
