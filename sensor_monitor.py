"""
Sensor Monitor — Monitoramento de Controle USB via Pygame
=========================================================
Detecta conexão/desconexão de joysticks (controles USB) e conta inputs.
Envia os eventos para o backend PHP via HTTP POST.
"""

import pygame
import requests
import time
import argparse
import json
import os
from datetime import datetime

# Desabilita o driver HIDAPI do SDL2 para evitar o erro KeyError: 125 com Nintendo Switch Pro Controller
os.environ["SDL_JOYSTICK_HIDAPI"] = "0"
# Permite capturar eventos do controle mesmo quando a janela estiver em segundo plano (desfocada)
os.environ["SDL_JOYSTICK_ALLOW_BACKGROUND_EVENTS"] = "1"

def carregar_env(caminho):
    if not os.path.isfile(caminho):
        return
    with open(caminho, encoding="utf-8") as arquivo:
        for linha in arquivo:
            linha = linha.strip()
            if not linha or linha.startswith("#") or "=" not in linha:
                continue
            chave, valor = linha.split("=", 1)
            os.environ.setdefault(chave.strip(), valor.strip().strip('"').strip("'"))

def parse_args():
    parser = argparse.ArgumentParser(description="Monitor de controle USB")
    parser.add_argument("--tag", required=True, help="Tag cadastrada em tab_maq (ex: EXT001)")
    parser.add_argument("--url", default=os.environ.get("SENSOR_URL", "http://localhost/PI4/php/receber_sensor.php"), help="URL do endpoint PHP")
    parser.add_argument("--poll", type=float, default=0.05, help="Intervalo de polling")
    return parser.parse_args()

def enviar_evento(url, tag_maq, evento, total_inputs=0, tempo_conectado=0):
    payload = {
        "tag_maq": tag_maq,
        "evento": evento,
        "timestamp": datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
        "total_inputs": total_inputs,
        "tempo_conectado": tempo_conectado,
    }
    headers = {}
    token = os.environ.get("API_TOKEN", "")
    if token:
        headers["Authorization"] = "Bearer " + token
    else:
        print("API_TOKEN ausente. Defina o token no arquivo .env.")
    try:
        resp = requests.post(url, data=payload, headers=headers, timeout=5)
        if resp.status_code == 200:
            try:
                texto_limpo = resp.text.strip().lstrip('\ufeff')
                data = json.loads(texto_limpo)
                status = "OK" if data.get("success") else "FALHA"
                print(f"[{payload['timestamp']}] {evento} → {status}")
            except Exception:
                pass
    except requests.RequestException:
        pass

def main():
    carregar_env(os.path.join(os.path.dirname(os.path.abspath(__file__)), ".env"))
    args = parse_args()
    tag = args.tag
    url = args.url

    pygame.init()
    pygame.joystick.init()

    # Janela mínima para rodar no Windows
    screen = pygame.display.set_mode((400, 200))
    pygame.display.set_caption(f"Sensor Monitor — {tag}")
    font = pygame.font.SysFont("Consolas", 14)
    clock = pygame.time.Clock()

    joystick = None
    conectado = False
    total_inputs = 0
    tempo_conexao = 0.0
    ultimo_envio = 0.0
    ultimo_total_enviado = 0
    running = True

    # Checa se o controle já estava plugado antes de rodar o script
    if pygame.joystick.get_count() > 0:
        joystick = pygame.joystick.Joystick(0)
        joystick.init()
        conectado = True
        tempo_conexao = time.time()
        ultimo_envio = time.time()
        print(f"[CONECTADO] {joystick.get_name()}")
        enviar_evento(url, tag, "CONEXAO")

    try:
        while running:
            for event in pygame.event.get():
                if event.type == pygame.QUIT:
                    running = False
                    break
                
                # --- Hotplugging (Conectar/Desconectar) ---
                if event.type == pygame.JOYDEVICEADDED and not conectado:
                    joystick = pygame.joystick.Joystick(event.device_index)
                    joystick.init()
                    conectado = True
                    total_inputs = 0
                    tempo_conexao = time.time()
                    ultimo_envio = time.time()
                    ultimo_total_enviado = 0
                    print(f"[CONECTADO] {joystick.get_name()}")
                    enviar_evento(url, tag, "CONEXAO")
                
                elif event.type == pygame.JOYDEVICEREMOVED and conectado:
                    tempo_total = int(time.time() - tempo_conexao)
                    print(f"[DESCONECTADO] Inputs: {total_inputs} | Tempo: {tempo_total}s")
                    enviar_evento(url, tag, "DESCONEXAO", total_inputs, tempo_total)
                    conectado = False
                    joystick = None
                    total_inputs = 0

                # --- Escutando todos os inputs do controle ---
                elif event.type in (pygame.JOYBUTTONDOWN, pygame.JOYAXISMOTION, pygame.JOYHATMOTION):
                    if conectado:
                        total_inputs += 1

            if not running:
                break

            # Lógica de Envio para a Web (a cada 2 segundos se houve movimentos novos)
            if conectado and (time.time() - ultimo_envio >= 2.0) and (total_inputs > ultimo_total_enviado):
                tempo_parcial = int(time.time() - tempo_conexao)
                enviar_evento(url, tag, "INPUT", total_inputs, tempo_parcial)
                ultimo_envio = time.time()
                ultimo_total_enviado = total_inputs

            # Desenha os dados na telinha preta
            screen.fill((20, 20, 30))
            if conectado:
                t_seg = int(time.time() - tempo_conexao)
                h, m, s = t_seg // 3600, (t_seg % 3600) // 60, t_seg % 60
                linhas = [
                    (f"Maquina: {tag}", (100, 180, 255)),
                    (f"Status:  CONECTADO", (0, 200, 100)),
                    (f"Inputs:  {total_inputs}", (200, 200, 200)),
                    (f"Tempo:   {h:02d}:{m:02d}:{s:02d}", (200, 200, 200))
                ]
            else:
                linhas = [
                    (f"Maquina: {tag}", (100, 180, 255)),
                    (f"Status:  AGUARDANDO CONTROLE...", (255, 200, 50))
                ]

            y = 20
            for texto, cor in linhas:
                screen.blit(font.render(texto, True, cor), (20, y))
                y += 24

            pygame.display.flip()
            clock.tick(30) # Roda a 30 FPS leves

    except KeyboardInterrupt:
        print("\n[ENCERRADO] Monitor finalizado pelo usuário.")

    if conectado:
        tempo_total = int(time.time() - tempo_conexao)
        enviar_evento(url, tag, "DESCONEXAO", total_inputs, tempo_total)

    pygame.quit()

if __name__ == "__main__":
    main()
