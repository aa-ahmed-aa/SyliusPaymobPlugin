<?php


namespace Ahmedkhd\SyliusPaymobPlugin\Services;


use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractService
{
    /** @var ContainerInterface */
    protected $container;

    /**
     * AbstractService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
}
