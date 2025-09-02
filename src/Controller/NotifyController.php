<?php

declare(strict_types=1);

namespace Ahmedkhd\SyliusPaymobPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Doctrine\ORM\EntityManagerInterface;

class NotifyController extends AbstractController
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private PaymentRepositoryInterface $paymentRepository,
        private StateMachineInterface $stateMachine,
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Handle Paymob's transaction response callback (GET request)
     * This is called when user returns from Paymob iframe
     */
    #[Route('/payment/paymob/capture', name: 'ahmedkhd_sylius_paymob_plugin_capture', methods: ['GET'])]
    public function doAction(Request $request): Response
    {
        $success = $request->query->get('success');
        $orderId = $request->query->get('order');
        $transactionId = $request->query->get('id');
        
        error_log("Paymob capture callback - success: {$success}, order: {$orderId}, transaction: {$transactionId}");
        
        if (!$orderId) {
            return new Response('Order ID not provided', Response::HTTP_BAD_REQUEST);
        }
        
        try {
            // Find the order
            $order = $this->orderRepository->find($orderId);
            if (!$order) {
                error_log("Order not found: {$orderId}");
                return new Response('Order not found', Response::HTTP_NOT_FOUND);
            }
            
            // Get the payment
            $payment = $order->getPayments()->first();
            if (!$payment) {
                error_log("Payment not found for order: {$orderId}");
                return new Response('Payment not found', Response::HTTP_NOT_FOUND);
            }
            
            // Update payment details
            $details = $payment->getDetails();
            $details['transaction_id'] = $transactionId;
            $details['success'] = $success === 'true';
            $details['capture_callback_received'] = true;
            $payment->setDetails($details);
            
            // Update payment state based on success
            if ($success === 'true') {
                $payment->setState('completed');
                error_log("Payment completed for order: {$orderId}");
            } else {
                $payment->setState('failed');
                error_log("Payment failed for order: {$orderId}");
            }
            
            $this->entityManager->flush();
            
            // Redirect to success/failure page
            if ($success === 'true') {
                return new RedirectResponse('/checkout/complete');
            } else {
                return new RedirectResponse('/checkout/failed');
            }
            
        } catch (\Exception $e) {
            error_log("Error in capture callback: " . $e->getMessage());
            return new Response('Internal server error', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * Handle Paymob's transaction processed webhook (POST request)
     * This is called by Paymob server to notify about payment status
     */
    #[Route('/payment/paymob/webhook', name: 'ahmedkhd_sylius_paymob_plugin_webhook', methods: ['POST'])]
    public function webhookAction(Request $request): Response
    {
        $content = $request->getContent();
        $data = json_decode($content, true);
        
        error_log("Paymob webhook received: " . json_encode($data));
        
        if (!$data) {
            return new Response('Invalid JSON', Response::HTTP_BAD_REQUEST);
        }
        
        $orderId = $data['order']['id'] ?? null;
        $success = $data['success'] ?? false;
        $transactionId = $data['id'] ?? null;
        
        if (!$orderId) {
            return new Response('Order ID not provided', Response::HTTP_BAD_REQUEST);
        }
        
        try {
            // Find the order
            $order = $this->orderRepository->find($orderId);
            if (!$order) {
                error_log("Order not found in webhook: {$orderId}");
                return new Response('Order not found', Response::HTTP_NOT_FOUND);
            }
            
            // Get the payment
            $payment = $order->getPayments()->first();
            if (!$payment) {
                error_log("Payment not found for order in webhook: {$orderId}");
                return new Response('Payment not found', Response::HTTP_NOT_FOUND);
            }
            
            // Update payment details
            $details = $payment->getDetails();
            $details['webhook_received'] = true;
            $details['webhook_data'] = $data;
            $payment->setDetails($details);
            
            // Update payment state based on webhook data
            if ($success) {
                $payment->setState('completed');
                error_log("Payment completed via webhook for order: {$orderId}");
            } else {
                $payment->setState('failed');
                error_log("Payment failed via webhook for order: {$orderId}");
            }
            
            $this->entityManager->flush();
            
            return new Response('OK', Response::HTTP_OK);
            
        } catch (\Exception $e) {
            error_log("Error in webhook: " . $e->getMessage());
            return new Response('Internal server error', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
