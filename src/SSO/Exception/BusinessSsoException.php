<?php

namespace SSO\Exception;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class BusinessSsoException extends SsoException
{

    /** @var  Collection */
    private $exceptions;

    /**
     * @return Collection
     */
    public function getExceptions()
    {
        if (null == $this->exceptions) {
            $this->exceptions = new ArrayCollection();
        }

        return $this->exceptions;
    }

    /**
     * @param mixed $exceptions
     */
    public function setExceptions($exceptions)
    {
        $this->exceptions = $exceptions;

        return $this;
    }

    public function addException(BusinessRuleException $e)
    {
        if (null == $this->exceptions) {
            $this->exceptions = new ArrayCollection();
        }

        $this->exceptions->add($e);
    }

    public function addMessageException($message)
    {

        if(!is_string($message)){
            throw new \Exception('Mensagem não corresponte ao tipo válido.');
        }

        if (null == $this->exceptions) {
            $this->exceptions = new ArrayCollection();
        }

        $this->exceptions->add(new BusinessRuleException($message));
    }


}