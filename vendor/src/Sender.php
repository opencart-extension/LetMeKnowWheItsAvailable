<?php
namespace ValdeirPsr\Letmeknow;

class Sender
{
    protected $registers = [];

    /**
     * 
     * @param NotifierInterface $adapter Define a forma de envio
     */
    public function __construct(
        protected NotifierInterface $adapter
    ) {

    }

    /**
     * Informa os registros que deverão ser notificados
     * 
     * @param array $registers
     */
    public function setRegisters(array $registers): self
    {
        $this->registers = $registers;

        return $this;
    }

    /**
     * Envia a notificação
     * 
     * @return boolean
     */
    public function send(): array
    {
        if (count($this->registers) === 0) return [];

        return $this->adapter->send($this->registers);
    }
}