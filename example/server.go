package main

import (
	"github.com/Leon2012/jsonrpc"
	"log"
	"net/http"
)

type Args struct {
	A, B int
}

type Arith int

func (t *Arith) Multiply(args *Args, reply *int) error {
	log.Printf("======== a:%d, b:%d", args.A, args.B)

	*reply = args.A * args.B
	return nil
}

type Person struct {
	Name string `json:name`
	Age  int    `json:age`
}

func (p *Person) GetPerson(userId string, result *Person) error {

	result.Name = "leon"
	result.Age = 32
	return nil
}

func main() {
	log.Println("start server...")
	server := jsonrpc.NewJSONRPCServer()

	arith := new(Arith)
	server.Register(arith)

	person := new(Person)
	server.Register(person)

	http.Handle("/rpc", server)

	http.ListenAndServe(":8080", nil)
}
