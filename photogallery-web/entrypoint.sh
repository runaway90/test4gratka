#!/bin/sh
set -e

JWT_KEY_DIR="/app/config/jwt"
JWT_PRIVATE_KEY="${JWT_KEY_DIR}/private.pem"
JWT_PUBLIC_KEY="${JWT_KEY_DIR}/public.pem"
JWT_PASSPHRASE_FILE="/app/.env.local.jwt"

# 1. Check if JWT keys exist, if not, generate them
if [ ! -f "$JWT_PRIVATE_KEY" ]; then
    echo "Generating JWT keys..."
    mkdir -p "$JWT_KEY_DIR"

    # Generate a random passphrase
    PASSPHRASE=$(head /dev/urandom | tr -dc A-Za-z0-9_ | head -c 32)

    # Generate private key
    openssl genpkey -out "$JWT_PRIVATE_KEY" -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -passout pass:"$PASSPHRASE"

    # Generate public key
    openssl pkey -in "$JWT_PRIVATE_KEY" -out "$JWT_PUBLIC_KEY" -pubout -passin pass:"$PASSPHRASE"

    # Store the passphrase in a local .env file for Symfony to pick up
    echo "JWT_PASSPHRASE=$PASSPHRASE" > "$JWT_PASSPHRASE_FILE"
    echo "JWT keys generated and passphrase stored in $JWT_PASSPHRASE_FILE"
else
    echo "JWT keys already exist."
fi

# 2. Load the JWT_PASSPHRASE into the environment
if [ -f "$JWT_PASSPHRASE_FILE" ]; then
    export $(grep -v '^#' "$JWT_PASSPHRASE_FILE" | xargs)
    echo "JWT_PASSPHRASE loaded from $JWT_PASSPHRASE_FILE"
else
    echo "Warning: $JWT_PASSPHRASE_FILE not found. JWT_PASSPHRASE might be missing."
fi

# 3. Run composer install
composer install

# 4. Start the PHP built-in web server
php -S 0.0.0.0:8000 -t public public/index.php

exec "$@"
