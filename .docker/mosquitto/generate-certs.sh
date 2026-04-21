#!/bin/bash

# Test Certificate Generation Script
# Generates self-signed certificates for local MQTT testing with TLS
# WARNING: These certificates are for development/testing ONLY

set -e

CERTS_DIR="$(dirname "$0")/certs"
mkdir -p "$CERTS_DIR"

echo "Generating test certificates in $CERTS_DIR..."

# Generate CA private key and certificate
echo "  - Generating CA certificate..."
openssl req -new -x509 -days 365 -nodes \
  -out "$CERTS_DIR/ca.crt" \
  -keyout "$CERTS_DIR/ca.key" \
  -subj "/C=US/ST=State/L=City/O=PlayerCentral/CN=LocalCA" \
  2>/dev/null

# Generate server private key
echo "  - Generating server key..."
openssl genrsa -out "$CERTS_DIR/server.key" 2048 2>/dev/null

# Generate server certificate signing request
echo "  - Generating server certificate..."
openssl req -new \
  -key "$CERTS_DIR/server.key" \
  -out "$CERTS_DIR/server.csr" \
  -subj "/C=US/ST=State/L=City/O=PlayerCentral/CN=localhost" \
  2>/dev/null

# Sign server certificate with CA
openssl x509 -req -in "$CERTS_DIR/server.csr" \
  -CA "$CERTS_DIR/ca.crt" \
  -CAkey "$CERTS_DIR/ca.key" \
  -CAcreateserial \
  -out "$CERTS_DIR/server.crt" \
  -days 365 \
  2>/dev/null

# Generate client private key
echo "  - Generating client key..."
openssl genrsa -out "$CERTS_DIR/client.key" 2048 2>/dev/null

# Generate client certificate signing request
echo "  - Generating client certificate..."
openssl req -new \
  -key "$CERTS_DIR/client.key" \
  -out "$CERTS_DIR/client.csr" \
  -subj "/C=US/ST=State/L=City/O=PlayerCentral/CN=client" \
  2>/dev/null

# Sign client certificate with CA
openssl x509 -req -in "$CERTS_DIR/client.csr" \
  -CA "$CERTS_DIR/ca.crt" \
  -CAkey "$CERTS_DIR/ca.key" \
  -CAcreateserial \
  -out "$CERTS_DIR/client.crt" \
  -days 365 \
  2>/dev/null

# Clean up CSR files
rm -f "$CERTS_DIR/server.csr" "$CERTS_DIR/client.csr" "$CERTS_DIR/ca.srl"

# Set proper permissions
chmod 600 "$CERTS_DIR"/*.key
chmod 644 "$CERTS_DIR"/*.crt

echo "✓ Test certificates generated successfully"
echo ""
echo "Files created:"
echo "  - $CERTS_DIR/ca.crt           (CA certificate)"
echo "  - $CERTS_DIR/ca.key           (CA private key)"
echo "  - $CERTS_DIR/server.crt       (Server certificate)"
echo "  - $CERTS_DIR/server.key       (Server private key)"
echo "  - $CERTS_DIR/client.crt       (Client certificate)"
echo "  - $CERTS_DIR/client.key       (Client private key)"
echo ""
echo "WARNING: These certificates are for testing only and should never be used in production."
