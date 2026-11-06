<?php

function carregarEnv($caminho) {
    if (!is_readable($caminho)) {
        return;
    }
    $linhas = file($caminho, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($linhas === false) {
        return;
    }
    foreach ($linhas as $linha) {
        $linha = trim($linha);
        if ($linha === '' || str_starts_with($linha, '#')) {
            continue;
        }
        $pos = strpos($linha, '=');
        if ($pos === false) {
            continue;
        }
        $chave = trim(substr($linha, 0, $pos));
        $valor = trim(substr($linha, $pos + 1));
        if ($valor !== '' && ($valor[0] === '"' || $valor[0] === "'")) {
            $valor = trim($valor, "\"'");
        }
        if ($chave === '') {
            continue;
        }
        putenv($chave . '=' . $valor);
        $_ENV[$chave] = $valor;
    }
}

function env_valor($chave, $padrao = '') {
    $valor = $_ENV[$chave] ?? getenv($chave);
    if ($valor === false || $valor === null || $valor === '') {
        return $padrao;
    }
    return $valor;
}
