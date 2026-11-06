<?php
require_once __DIR__ . '/php/cli_only.php';
exigirCli();
include 'php/conexao.php';
include 'models/Indicadores.php';

$ind = new Indicadores($conn);

echo "MTTR: \n";
print_r($ind->getMTTR());

echo "\nMTBF: \n";
print_r($ind->getMTBF());

echo "\nDisponibilidade: \n";
print_r($ind->getDisponibilidade());

echo "\nRanking Criticidade: \n";
print_r($ind->getRankingCriticidade(5));
