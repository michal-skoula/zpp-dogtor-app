#!/usr/bin/env bash

HOST="dogtor.local"
TIMEOUT=5

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
BOLD='\033[1m'
NC='\033[0m'

ok()   { echo -e "${GREEN}ok${NC}"; }
fail() { echo -e "${RED}fail${NC} — $1"; }
warn() { echo -e "${YELLOW}warn${NC} — $1"; }

pad() { printf "%-36s" "$1"; }

PI_MACS="b8:27:eb|dc:a6:32|e4:5f:01"  # Raspberry Pi MAC OUI prefixes
CACHE_FILE="${HOME}/.dogtor_last_ip"

echo -e "${BOLD}=== dogtor status ===${NC}"
echo

# ── 1. IP discovery ───────────────────────────────────────────────────────────
echo -e "${BOLD}Discovery${NC}"

IPV4=""
DISCOVERY_METHOD=""

# figure out the active network interface and local subnet
IFACE=$(route -n get default 2>/dev/null | awk '/interface:/{print $2}')
LOCAL_IP=$(ipconfig getifaddr "$IFACE" 2>/dev/null)
SUBNET="${LOCAL_IP%.*}.0/24"

# Attempt 1: ARP cache — instant, no sudo, works if we've talked to the Pi recently
pad "  ARP cache"
ARP_IP=$(arp -a 2>/dev/null | grep -iE "$PI_MACS" | grep -oE '[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+' | grep -v '^169\.254\.' | head -1)
ARP_LL=$(arp -a 2>/dev/null | grep -iE "$PI_MACS" | grep -oE '[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+' | grep '^169\.254\.' | head -1)
if [[ -n "$ARP_IP" ]]; then
    IPV4="$ARP_IP"
    DISCOVERY_METHOD="ARP cache"
    echo -e "${GREEN}ok${NC}  →  $IPV4"
elif [[ -n "$ARP_LL" ]]; then
    LINK_LOCAL_IP="$ARP_LL"
    echo -e "${YELLOW}link-local only${NC}  →  $ARP_LL  (ethernet-only, trying WiFi methods first)"
else
    echo -e "${YELLOW}not in cache${NC}"
fi

# Attempt 2: mDNS via system resolver (skip link-local — only works over direct ethernet)
if [[ -z "$IPV4" ]]; then
    pad "  mDNS (.local) resolver"
    MDNS_IP=$(python3 -c "import socket; print(socket.gethostbyname('$HOST'))" 2>/dev/null)
    if [[ -n "$MDNS_IP" && "$MDNS_IP" != 169.254.* ]]; then
        IPV4="$MDNS_IP"
        DISCOVERY_METHOD="mDNS resolver"
        echo -e "${GREEN}ok${NC}  →  $IPV4"
    elif [[ "$MDNS_IP" == 169.254.* ]]; then
        LINK_LOCAL_IP="$MDNS_IP"
        echo -e "${YELLOW}link-local${NC}  →  $MDNS_IP  (ethernet-only, trying WiFi methods first)"
    else
        echo -e "${YELLOW}no response${NC}"
    fi
fi

# Attempt 3: dns-sd (direct Bonjour, works when resolver is flaky; can return multiple IPs)
if [[ -z "$IPV4" ]]; then
    pad "  mDNS via dns-sd"
    DNS_SD_OUT=$(timeout 4 bash -c "dns-sd -G v4 $HOST 2>/dev/null" 2>/dev/null)
    # prefer non-link-local; stash link-local as fallback
    MDNS_REAL=$(echo "$DNS_SD_OUT" | grep -oE '[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+' \
                | grep -v '^0\.' | grep -v '^169\.254\.' | head -1)
    MDNS_LL=$(echo "$DNS_SD_OUT" | grep -oE '[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+' \
              | grep '^169\.254\.' | head -1)
    if [[ -n "$MDNS_REAL" ]]; then
        IPV4="$MDNS_REAL"
        DISCOVERY_METHOD="dns-sd"
        echo -e "${GREEN}ok${NC}  →  $IPV4"
    elif [[ -n "$MDNS_LL" ]]; then
        LINK_LOCAL_IP="$MDNS_LL"
        echo -e "${YELLOW}link-local only${NC}  →  $MDNS_LL"
    else
        echo -e "${YELLOW}no response${NC}"
    fi
fi

# Attempt 4: last known IP (cached from a previous successful run)
if [[ -z "$IPV4" && -f "$CACHE_FILE" ]]; then
    CACHED_IP=$(cat "$CACHE_FILE")
    pad "  Last known IP ($CACHED_IP)"
    if ping -c1 -t1 "$CACHED_IP" &>/dev/null 2>&1; then
        IPV4="$CACHED_IP"
        DISCOVERY_METHOD="cached IP"
        echo -e "${GREEN}responding${NC}  →  $IPV4"
    else
        echo -e "${YELLOW}not responding${NC}"
    fi
fi

# Attempt 5: nmap ping sweep — needs sudo to get MAC addresses in output
if [[ -z "$IPV4" ]]; then
    pad "  nmap sweep ($SUBNET)"
    if command -v nmap &>/dev/null && [[ -n "$SUBNET" ]]; then
        NMAP_OUT=$(sudo nmap -sn --host-timeout 3s "$SUBNET" 2>/dev/null)
        # match by Pi MAC OUI (requires sudo) or by hostname containing "dogtor"
        IPV4=$(echo "$NMAP_OUT" | awk '
            /Nmap scan report for/ { ip=$NF; gsub(/[()]/, "", ip) }
            /'"$PI_MACS"'/ { print ip; exit }
        ' 2>/dev/null | head -1)
        if [[ -z "$IPV4" ]]; then
            # fallback: match by hostname in nmap output (works without sudo)
            IPV4=$(echo "$NMAP_OUT" | grep -i "dogtor" | grep -oE '\(([0-9.]+)\)' \
                   | tr -d '()' | head -1)
        fi
        if [[ -n "$IPV4" ]]; then
            DISCOVERY_METHOD="nmap"
            echo -e "${GREEN}ok${NC}  →  $IPV4"
        else
            echo -e "${RED}not found${NC}"
        fi
    else
        echo -e "${YELLOW}nmap not available${NC}"
    fi
fi

# Attempt 6: arp-scan with correct interface (most reliable but needs sudo)
if [[ -z "$IPV4" ]]; then
    pad "  arp-scan (sudo, iface: $IFACE)"
    if command -v arp-scan &>/dev/null && [[ -n "$IFACE" ]]; then
        ARP_OUT=$(sudo arp-scan -I "$IFACE" --localnet 2>/dev/null)
        IPV4=$(echo "$ARP_OUT" | grep -iE "$PI_MACS" | awk '{print $1}' | head -1)
        if [[ -n "$IPV4" ]]; then
            DISCOVERY_METHOD="arp-scan"
            echo -e "${GREEN}ok${NC}  →  $IPV4"
        else
            echo -e "${RED}not found${NC}"
        fi
    else
        echo -e "${YELLOW}arp-scan not installed${NC}  →  brew install arp-scan"
    fi
fi

# Last resort: use link-local address if we found one (only works over direct ethernet cable)
if [[ -z "$IPV4" && -n "$LINK_LOCAL_IP" ]]; then
    pad "  link-local fallback"
    echo -e "${YELLOW}using${NC}  →  $LINK_LOCAL_IP  (ethernet cable required)"
    IPV4="$LINK_LOCAL_IP"
    DISCOVERY_METHOD="link-local"
fi

if [[ -z "$IPV4" ]]; then
    echo
    echo -e "  ${RED}Cannot locate dogtor on the network.${NC}"
    echo "  — Ensure you're on the same subnet as the Pi"
    echo "  — Try connecting over Ethernet for a reliable fallback"
    exit 1
fi

# Cache the IP for next run (never cache link-local — it's unreachable over WiFi)
[[ "$IPV4" != 169.254.* ]] && echo "$IPV4" > "$CACHE_FILE"

echo "  found via: $DISCOVERY_METHOD"
echo

# ── 2. Ping ───────────────────────────────────────────────────────────────────
pad "Ping"
if ping -c1 -t2 "$IPV4" &>/dev/null 2>&1; then
    ok
else
    warn "resolved but not pingable (firewall or ICMP blocked)"
fi

# ── 3. FastAPI health ─────────────────────────────────────────────────────────
echo -e "${BOLD}FastAPI Health${NC}"
HEALTH=$(curl -sf --max-time 3 "http://$IPV4:8000/api/health" 2>/dev/null)
if [[ -n "$HEALTH" ]]; then
    echo -e "  ${GREEN}ok${NC}  →  $HEALTH"
else
    echo -e "  ${RED}fail${NC} — no response from $IPV4:8000/api/health"
fi

echo

# ── 4. Robot (GO2 Jetson Orin) ────────────────────────────────────────────────
ROBOT_IP="192.168.123.18"
echo -e "${BOLD}Robot (GO2)${NC}"
pad "  Ping $ROBOT_IP"
if ssh -o ConnectTimeout=5 -o BatchMode=yes -o StrictHostKeyChecking=no -o LogLevel=ERROR dogtor@"$IPV4" \
    "ping -c1 -W2 $ROBOT_IP" &>/dev/null 2>&1; then
    ok
    pad "  WebRTC port 9991"
    if ssh -o ConnectTimeout=5 -o BatchMode=yes -o StrictHostKeyChecking=no -o LogLevel=ERROR dogtor@"$IPV4" \
        "nc -zw2 $ROBOT_IP 9991" &>/dev/null 2>&1; then
        ok
    else
        pad "  WebRTC port 8081"
        if ssh -o ConnectTimeout=5 -o BatchMode=yes -o StrictHostKeyChecking=no -o LogLevel=ERROR dogtor@"$IPV4" \
            "nc -zw2 $ROBOT_IP 8081" &>/dev/null 2>&1; then
            ok
        else
            warn "reachable but WebRTC ports 9991/8081 closed (signaling service down?)"
        fi
    fi
else
    echo -e "  ${RED}fail${NC} — not reachable (eth0 cable plugged in?)"
fi

echo

# ── (commented out — require SSH) ─────────────────────────────────────────────
# # Network (SSID, wlan0/eth0 IPs, gateway)
# # PM2 process list
