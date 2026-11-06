<?php

function exigirCli() {
    if (PHP_SAPI !== 'cli') {
        http_response_code(403);
        header('Content-Type: text/plain; charset=utf-8');
        exit("Este script só pode ser executado pela linha de comando.\n");
    }
}
