package jsonrpc

import (
	_ "errors"
	"io"
	"log"
	"net/http"
	"net/rpc"
	"net/rpc/jsonrpc"
)

var (
	JSON_RPC_CONNECTED = "200 Connected to JSON RPC"
)

type JSONRPCServer struct {
	*rpc.Server
}

func NewJSONRPCServer() *JSONRPCServer {
	return &JSONRPCServer{rpc.NewServer()}
}

func (s *JSONRPCServer) ServeHTTP(w http.ResponseWriter, req *http.Request) {
	log.Println("got a request")
	conn, _, err := w.(http.Hijacker).Hijack()
	if err != nil {
		log.Print("rpc hijacking ", req.RemoteAddr, ": ", err.Error())
		return
	}

	io.WriteString(conn, "HTTP/1.1 "+JSON_RPC_CONNECTED+"\n")
	io.WriteString(conn, "Content-Type: application/json\n\n")

	codec := jsonrpc.NewServerCodec(conn)
	log.Println("ServeCodec")

	s.Server.ServeCodec(codec)
	log.Println("finished serving request")
}
