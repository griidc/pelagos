<?php

namespace App\MessageHandler;

use App\Message\HashFile;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class HashFileHandler implements MessageHandlerInterface
{
    public function __invoke(HashFile $HashFile)
    {

    }

}
