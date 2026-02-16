#!/bin/sh
set -e

# Register SSH public key if provided
if [ -n "$SSH_PUBLIC_KEY" ]; then
    mkdir -p ~/.ssh
    echo "$SSH_PUBLIC_KEY" >> ~/.ssh/authorized_keys
    chmod 700 ~/.ssh
    chmod 600 ~/.ssh/authorized_keys
fi

# Start SSHD in background
if command -v sshd >/dev/null 2>&1; then
    sshd
fi

# Run migrations
php artisan migrate --force 2>/dev/null || true

# Execute the main command
exec "$@"
