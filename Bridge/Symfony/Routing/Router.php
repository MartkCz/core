<?php

/*
 * This file is part of the DunglasApiBundle package.
 *
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dunglas\ApiBundle\Bridge\Symfony\Routing;

use Dunglas\ApiBundle\Api\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

/**
 * Symfony router decorator.
 *
 * Kévin Dunglas <dunglas@gmail.com>
 */
final class Router implements RouterInterface, UrlGeneratorInterface
{
    const CONST_MAP = [
        UrlGeneratorInterface::ABS_URL => RouterInterface::ABSOLUTE_URL,
        UrlGeneratorInterface::ABS_PATH => RouterInterface::ABSOLUTE_PATH,
        UrlGeneratorInterface::REL_PATH => RouterInterface::RELATIVE_PATH,
        UrlGeneratorInterface::NET_PATH => RouterInterface::NETWORK_PATH,
    ];

    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function setContext(RequestContext $context)
    {
        $this->router->setContext($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {
        return $this->router->getContext();
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteCollection()
    {
        return $this->router->getRouteCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function match($pathInfo)
    {
        $baseContext = $this->router->getContext();
        $pathInfo = str_replace($baseContext->getBaseUrl(), '', $pathInfo);

        $request = Request::create($pathInfo);
        $context = (new RequestContext())->fromRequest($request);
        $context->setPathInfo($pathInfo);
        $context->setScheme($baseContext->getScheme());

        try {
            $this->router->setContext($context);

            return $this->router->match($request->getPathInfo());
        } finally {
            $this->router->setContext($baseContext);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function generate($name, $parameters = [], $referenceType = self::ABS_PATH)
    {
        return $this->router->generate($name, $parameters, self::CONST_MAP[$referenceType]);
    }
}