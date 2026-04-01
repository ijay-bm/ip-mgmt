const http = require("http");

http
  .createServer((req, res) => {
    res.writeHead(200, { "Content-Type": "application/json" });
    res.end(
      JSON.stringify({
        message: "Hello World from Auth Service",
        url: req.url,
        headers: req.headers,
      }),
    );
  })
  .listen(8000);

console.log("Auth service is running on port 8000");
