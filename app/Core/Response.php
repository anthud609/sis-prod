<?php
// App/Core/Response.php
namespace App\Core;

class Response {
    public int $status = 200;
    public array $headers = [];
    public string $body = '';

    public function redirect(string $url): self {
        $this->status = 302;
        $this->headers['Location'] = $url;
        return $this;
    }

    public function send(): void {
        http_response_code($this->status);
        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }
        echo $this->body;
    }
}
