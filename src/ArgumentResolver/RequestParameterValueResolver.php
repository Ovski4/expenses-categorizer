<?php

namespace App\ArgumentResolver;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use UnexpectedValueException;

class RequestParameterValueResolver implements ValueResolverInterface
{
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        if(!$argument->isNullable()) {
            return false;
        }

        if(!in_array($argument->getType(), ['bool', 'string'])) {
            return false;
        }

        $argumentName = $argument->getName();
        if(!$request->query->has($argumentName) && !$request->request->has($argumentName)) {
            return false;
        }

        return true;
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $argumentName = $argument->getName();

        if(
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

        if ($argument->getType() === 'bool') {
            return [boolval($value)];
        }

        if ($argument->getType() === 'string') {
            return [strval($value)];
        }

        throw new UnexpectedValueException(sprintf('Unable to support argument with name %s and type %s', $argumentName, $argument->getType() ));
    }
}
