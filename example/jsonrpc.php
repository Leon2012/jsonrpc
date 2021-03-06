<?php
class JsonRPC
{
    private $host;
    private $port;
    private $path;
    private $conn;
    private $reqId;
    function __construct($host, $port, $path) {
        $this->host = $host;
        $this->port = $port;
        $this->path = $path;
        $this->conn = NULL;
        $this->reqId = 1;
    }
    private function Dial() {
        $this->conn = @fsockopen($this->host, $this->port, $errno, $errstr, 5);
        if (!$this->conn) {
            return "JsonRPC Dial Failed: $errstr ($errno)";
        }
        $err = fwrite($this->conn, "GET ".$this->path." HTTP/1.1\n\n");
        if ($err === false)
            return "JsonRPC Init Failed";
        stream_set_timeout($this->conn, 0, 3000);
        $info = stream_get_meta_data($this->conn);
        if ($info['timed_out']) {
            fclose($this->conn);
            return "JsonRPC Init Time Out";
        }
        // check first http head
        $line = fgets($this->conn);
        if ($line != "HTTP/1.1 200 Connected to JSON RPC\n") {
            fclose($this->conn);
            return "JsonRPC Unexpected Result: $line";
        }
        // ignore http head
        for (;;) {
            $line = fgets($this->conn);
            if ($line == "\n") {
                break;
            }
        }
        return NULL;
    }
    public function Call($method, $params) {
        if ($this->conn == NULL) {
            $dialResult = $this->Dial();
            if ($dialResult !== NULL)
                return $dialResult;
        }
        $err = fwrite($this->conn, json_encode(array(
            'method' => $method,
            'params' => array($params),
            'id'     => $this->reqId++,
        ))."\n");
        if ($err === false)
            return "JsonRPC Send Failed";
        stream_set_timeout($this->conn, 0, 3000);
        $info = stream_get_meta_data($this->conn);
        if ($info['timed_out']) {
            fclose($this->conn);
            return "JsonRPC Time Out";
        }
        $line = fgets($this->conn);
        if ($line === false) {
            return NULL;
        }
        return json_decode($line);
    }
}