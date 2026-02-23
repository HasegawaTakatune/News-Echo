#!/bin/sh
set -e

# Register SSH public key if provided
if [ -n "$SSH_PUBLIC_KEY" ]; then
    mkdir -p ~/.ssh
    echo "$SSH_PUBLIC_KEY" >> ~/.ssh/authorized_keys
    chmod 700 ~/.ssh
    chmod 600 ~/.ssh/authorized_keys
fi

# Start SSHD in background (ignore failure soアプリは続行)
if command -v /usr/sbin/sshd >/dev/null 2>&1; then
    /usr/sbin/sshd || echo "sshd failed to start, continuing without SSH"
fi

# Execute the main command
exec "$@"
