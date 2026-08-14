#!/bin/bash
set -e

BLUE='\033[0;34m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

echo -e "${BLUE}${BOLD}"
echo "  █████╗ ██╗     ██████╗██╗  ██╗ █████╗ ████████╗"
echo " ██╔══██╗██║    ██╔════╝██║  ██║██╔══██╗╚══██╔══╝"
echo " ███████║██║    ██║     ███████║███████║   ██║   "
echo " ██╔══██║██║    ██║     ██╔══██║██╔══██║   ██║   "
echo " ██║  ██║██║    ╚██████╗██║  ██║██║  ██║   ██║   "
echo " ╚═╝  ╚═╝╚═╝     ╚═════╝╚═╝  ╚═╝╚═╝  ╚═╝   ╚═╝   "
echo -e "${NC}"
echo -e "${CYAN}  AI Chat UI — Docker Launcher${NC}"
echo -e "${CYAN}  Port 8015 · Laravel + MariaDB${NC}"
echo ""

# --- Check Docker ---
if ! command -v docker &> /dev/null; then
    echo -e "${RED}✗ Docker is not installed or not running.${NC}"
    exit 1
fi

if ! docker info &> /dev/null; then
    echo -e "${RED}✗ Docker daemon is not running. Please start Docker Desktop.${NC}"
    exit 1
fi

# --- Generate APP_KEY if missing in .env.docker ---
if grep -q "^APP_KEY=$" .env.docker || ! grep -q "^APP_KEY=" .env.docker; then
    echo -e "${YELLOW}🔑 Generating APP_KEY...${NC}"
    KEY=$(php artisan key:generate --show 2>/dev/null || openssl rand -base64 32)
    # Ensure base64: prefix
    if [[ "$KEY" != base64:* ]]; then
        KEY="base64:${KEY}=="
    fi
    sed -i '' "s|^APP_KEY=.*|APP_KEY=${KEY}|" .env.docker
    echo -e "${GREEN}✓ APP_KEY set${NC}"
fi

# --- Build & Start ---
echo -e "\n${YELLOW}🐳 Building and starting containers...${NC}"
docker compose down --remove-orphans 2>/dev/null || true
docker compose up -d --build

# --- Wait for app to be healthy ---
echo -e "\n${YELLOW}⏳ Waiting for services to be ready...${NC}"
MAX_WAIT=90
ELAPSED=0
until curl -sf http://localhost:8015 > /dev/null 2>&1; do
    if [ $ELAPSED -ge $MAX_WAIT ]; then
        echo -e "${RED}✗ Timed out waiting for app. Check logs: docker compose logs app${NC}"
        exit 1
    fi
    printf "."
    sleep 3
    ELAPSED=$((ELAPSED + 3))
done

echo -e "\n"
echo -e "${GREEN}${BOLD}✅ AI Chat UI is running!${NC}"
echo ""
echo -e "  ${CYAN}🌐 Open:${NC}      http://localhost:8015"
echo -e "  ${CYAN}⚙️  Settings:${NC}  http://localhost:8015/settings"
echo -e "  ${CYAN}🎭 Personas:${NC}  http://localhost:8015/personas"
echo -e "  ${CYAN}🗄️  DB Port:${NC}   localhost:3315"
echo ""
echo -e "  ${YELLOW}Commands:${NC}"
echo -e "    Stop:    ${BOLD}docker compose down${NC}"
echo -e "    Logs:    ${BOLD}docker compose logs -f app${NC}"
echo -e "    Shell:   ${BOLD}docker compose exec app bash${NC}"
echo -e "    Artisan: ${BOLD}docker compose exec app php artisan <cmd>${NC}"
echo ""
