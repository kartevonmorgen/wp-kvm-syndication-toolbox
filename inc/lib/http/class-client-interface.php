<?php

interface ClientInterface
{
  public function send(RequestInterface $request ): ResponseInterface;

  public function sendAsync(RequestInterface $request ): ResponseInterface;
}
