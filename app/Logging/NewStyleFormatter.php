<?php
namespace App\Logging;

use Monolog\Formatter\LineFormatter;

class NewStyleFormatter {

    public function __invoke($logger)
    {
        $uniqid = uniqid();
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new LineFormatter(
                "\n\n [%datetime%] $uniqid :%channel%.%level_name%: %message% %context% %extra%",
                "Y-m-d H:i:s",
                true,
                true
            ));
        }
    }
}


