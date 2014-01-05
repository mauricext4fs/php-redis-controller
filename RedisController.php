<?php

class RedisController {

    private  $objServer = null;

    public function __construct()
    {
    
    }

    public function getList($strKey, $numLimit = 0)
    {
        $arrReturn = array();
        $objServer = $this->getInstance();

        $strMsg = sprintf("LRANGE %s %d %d", $strKey, ($numLimit) ? $numLimit * -1 : -10000, ($numLimit) ? $numLimit : 10000);
        $strResponse = $this->sendFormattedCommand($strMsg);
        $numResult = intval(preg_replace("/^\*([0-9]*)/", "$1", $strResponse));
        for ($i=1; $i<$numResult; $i++) {
            $strResponse = fgets($objServer);
            $strResponse = fgets($objServer);
            $arrReturn[] = $strResponse;
        }
        return $arrReturn;
    }

    public function addValueToEndOfList($strKey, $strValue)
    {
        $objServer = $this->getInstance();
        
        $strMsg = sprintf("RPUSH %s \"%s\"", $strKey, $strValue);
        $this->sendFormattedCommand($strMsg);
        
        return;
    }

    public function sendFormattedCommand($strCmd)
    {
        $objServer = $this->getInstance();

        $strCmd = sprintf("%s\r\n", $strCmd);
        fputs($objServer, $strCmd);
        $strResponse = fgets($objServer);

        return $strResponse;
    }


    protected function getInstance()
    {
        if(empty($this->objServer)) {
            // Connecting
            $this->objServer = stream_socket_client("unix:///tmp/redis.sock", $errno, $errstr, 30);
            // Test connection
            $this->sendFormattedCommand("ping");
            if ($errno) {
                throw new Exception(sprintf("%s\n", $errno));
            }
            if ($errstr) {
               throw new Exception( printf("%s\n", $errstr));
            }
        }

        return $this->objServer;
    }
}
