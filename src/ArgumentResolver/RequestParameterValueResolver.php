<?php

namespace App\ArgumentResolver;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class RequestParameterValueResolver implements ValueResolverInterface
{
    /**
     * @return iterable<int, bool|string>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $argumentName = $argument->getName();

        if (
            !$argument->isNullable()
            || !in_array($argument->getType(), ['bool', 'string'])
            || (!$request->query->has($argumentName) && !$request->request->has($argumentName))
        ) {
            return [];
        }

        $value = $request->query->has($argumentName)
            ? $request->query->get($argumentName)
            : $request->request->get($argumentName)
        ;

        if ('bool' === $argument->getType()) {
            return [boolval($value)];
        }

        if ('string' === $argument->getType()) {
            return [strval($value)];
        }

        throw new \UnexpectedValueException(sprintf('Unable to support argument with name %s and type %s', $argumentName, $argument->getType()));
    }
}
