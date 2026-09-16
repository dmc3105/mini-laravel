<?php

namespace App\Routing;

class Response{
    private function __construct(
        private string $content,
        private int $status = 200,
        private array $headers = [],
    ) {
    }

    public static function text(
        string $content,
        int $status = 200,
        array $headers = []
    )
    {
        return new Response($content, $status, array_merge([
            "Content-Type" => "text/plain; charset=UTF-8"
        ]));
    }

    public static function json(
        mixed $data,
        int $status = 200,
        array $headers = []
    ) : self
    {
        return new self(
            json_encode($data, JSON_THROW_ON_ERROR),
            $status,
            array_merge([
                "Content-Type" => 'application/json; charset=UTF-8',
            ])
        );
    }



    public function send(): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        echo $this->content;
    }
}
